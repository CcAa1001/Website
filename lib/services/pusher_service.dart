import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:http/http.dart' as http;
import '../models/dashboard_models.dart';

/// Callback types
typedef OnNewOrder = void Function(Order order);
typedef OnStatusChanged = void Function(String orderId, String oldStatus, String newStatus);
typedef OnSessionEvent = void Function(String action, Map<String, dynamic> data);

/// Cross-platform Pusher service using raw WebSocket protocol.
/// Works on Flutter Web, Android, and iOS without native plugins.
///
/// Subscribes to:
///   - private-dashboard.{outletId}
///   - private-kitchen.{outletId}
///   - private-tenant.{tenantId}.outlet.{outletId}
class PusherService {
  static const String _appKey = 'a3a07a58f1eb91f1db51';
  static const String _cluster = 'ap1';

  WebSocketChannel? _channel;
  String? _socketId;
  bool _isConnected = false;
  Timer? _pingTimer;

  // Auth info (set on connect)
  String _authToken = '';
  String _baseUrl = '';

  // Callbacks
  OnNewOrder? onNewOrder;
  OnStatusChanged? onStatusChanged;
  OnSessionEvent? onSessionEvent;
  VoidCallback? onAnyEvent;

  /// Connect to Pusher via WebSocket and subscribe to channels
  Future<void> connect({
    required String authToken,
    required String baseUrl,
    required String outletId,
    required String tenantId,
  }) async {
    if (_isConnected) return;

    _authToken = authToken;
    _baseUrl = baseUrl;

    try {
      final wsUrl = 'wss://ws-$_cluster.pusher.com/app/$_appKey'
          '?client=flutter&version=1.0&protocol=7';

      _channel = WebSocketChannel.connect(Uri.parse(wsUrl));

      _channel!.stream.listen(
        (message) => _handleMessage(message, outletId, tenantId),
        onError: (error) {
          debugPrint('[Pusher] WebSocket error: $error');
          _isConnected = false;
        },
        onDone: () {
          debugPrint('[Pusher] WebSocket closed');
          _isConnected = false;
          _pingTimer?.cancel();
        },
      );

      debugPrint('[Pusher] Connecting via WebSocket...');
    } catch (e) {
      debugPrint('[Pusher] Connection failed: $e');
    }
  }

  void _handleMessage(dynamic raw, String outletId, String tenantId) {
    try {
      final msg = jsonDecode(raw as String) as Map<String, dynamic>;
      final event = msg['event'] as String? ?? '';
      final channelName = msg['channel'] as String?;

      switch (event) {
        // ── Connection established → get socket_id, subscribe to channels
        case 'pusher:connection_established':
          final data = jsonDecode(msg['data'] as String) as Map<String, dynamic>;
          _socketId = data['socket_id'] as String?;
          _isConnected = true;
          debugPrint('[Pusher] ✅ Connected (socket: $_socketId)');

          // Start ping/pong to keep alive
          _pingTimer?.cancel();
          _pingTimer = Timer.periodic(const Duration(seconds: 30), (_) {
            _send({'event': 'pusher:ping', 'data': {}});
          });

          // Subscribe to private channels
          _subscribePrivate('private-dashboard.$outletId');
          _subscribePrivate('private-kitchen.$outletId');
          _subscribePrivate('private-tenant.$tenantId.outlet.$outletId');
          break;

        // ── Subscription success
        case 'pusher_internal:subscription_succeeded':
          debugPrint('[Pusher] ✅ Subscribed: $channelName');
          break;

        // ── Subscription error
        case 'pusher:error':
          debugPrint('[Pusher] ⚠️ Error: ${msg['data']}');
          break;

        // ── Pong
        case 'pusher:pong':
          break;

        // ── App events (your Laravel broadcasts)
        default:
          _handleAppEvent(event, msg['data'], channelName);
      }
    } catch (e) {
      debugPrint('[Pusher] Parse error: $e');
    }
  }

  void _handleAppEvent(String event, dynamic rawData, String? channel) {
    Map<String, dynamic> data;
    if (rawData is String) {
      data = jsonDecode(rawData) as Map<String, dynamic>;
    } else if (rawData is Map<String, dynamic>) {
      data = rawData;
    } else {
      return;
    }

    debugPrint('[Pusher] Event: $event on $channel');

    switch (event) {
      case 'order.new':
        _handleNewOrder(data);
        break;
      case 'order.status.changed':
        _handleStatusChanged(data);
        break;
      case 'session.created':
      case 'session.updated':
      case 'session.closed':
        final action = event.replaceFirst('session.', '');
        _handleSessionEvent(action, data);
        break;
    }
  }

  /// Subscribe to a private channel (requires auth from Laravel)
  Future<void> _subscribePrivate(String channelName) async {
    if (_socketId == null) return;

    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/broadcasting/auth'),
        headers: {
          'Authorization': 'Bearer $_authToken',
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json',
        },
        body: 'socket_id=$_socketId&channel_name=$channelName',
      );

      if (response.statusCode == 200) {
        final authData = jsonDecode(response.body) as Map<String, dynamic>;

        _send({
          'event': 'pusher:subscribe',
          'data': {
            'auth': authData['auth'],
            'channel': channelName,
          },
        });

        debugPrint('[Pusher] Subscribing to $channelName...');
      } else {
        debugPrint('[Pusher] Auth failed ($channelName): ${response.statusCode} ${response.body}');
      }
    } catch (e) {
      debugPrint('[Pusher] Auth request error ($channelName): $e');
    }
  }

  void _send(Map<String, dynamic> data) {
    _channel?.sink.add(jsonEncode(data));
  }

  // ── Event handlers ──

  void _handleNewOrder(Map<String, dynamic> data) {
    debugPrint('[Pusher] 🆕 New order: ${data['order_number']}');

    try {
      final order = Order(
        id: data['order_id']?.toString() ?? '',
        orderNumber: data['order_number'] ?? '',
        status: 'pending',
        tableNumber: data['table_number']?.toString() ?? '-',
        guestCount: 0,
        totalAmount: (data['grand_total'] ?? 0).toDouble(),
        createdAt: DateTime.tryParse(data['created_at'] ?? '') ?? DateTime.now(),
        items: ((data['items'] as List<dynamic>?) ?? [])
            .map((e) => OrderItem(
                  name: e['product_name'] ?? '',
                  quantity: e['quantity'] ?? 1,
                ))
            .toList(),
      );
      onNewOrder?.call(order);
    } catch (e) {
      debugPrint('[Pusher] Error parsing new order: $e');
    }

    onAnyEvent?.call();
  }

  void _handleStatusChanged(Map<String, dynamic> data) {
    debugPrint('[Pusher] 🔄 ${data['order_number']}: ${data['old_status']} → ${data['new_status']}');

    onStatusChanged?.call(
      data['order_id']?.toString() ?? '',
      data['old_status'] ?? '',
      data['new_status'] ?? '',
    );

    onAnyEvent?.call();
  }

  void _handleSessionEvent(String action, Map<String, dynamic> data) {
    debugPrint('[Pusher] 🪑 Session $action: Table ${data['table_number']}');
    onSessionEvent?.call(action, data);
    onAnyEvent?.call();
  }

  /// Disconnect
  Future<void> disconnect() async {
    _pingTimer?.cancel();
    await _channel?.sink.close();
    _isConnected = false;
    _socketId = null;
    debugPrint('[Pusher] Disconnected');
  }

  bool get isConnected => _isConnected;
}