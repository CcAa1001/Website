import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/dashboard_models.dart';
import '../services/dashboard_service.dart';
import '../services/pusher_service.dart';
import '../providers/auth_provider.dart';
import '../core/network/dio_client.dart'; // Your existing dio provider

// ── Service Provider (uses your existing dioProvider) ──
final dashboardServiceProvider = Provider<DashboardService>((ref) {
  final dio = ref.watch(dioProvider);
  return DashboardService(dio);
});

// ── Pusher Provider ──
final pusherServiceProvider = Provider<PusherService>((ref) {
  return PusherService();
});

// ── Dashboard State ──
class DashboardState {
  final DashboardStats stats;
  final Map<String, List<Order>> ordersByStatus;
  final List<TableSession> activeSessions;
  final bool isLoading;
  final bool isRefreshing;
  final String? error;
  final bool pusherConnected;

  const DashboardState({
    required this.stats,
    required this.ordersByStatus,
    required this.activeSessions,
    this.isLoading = false,
    this.isRefreshing = false,
    this.error,
    this.pusherConnected = false,
  });

  factory DashboardState.initial() => DashboardState(
        stats: DashboardStats.empty(),
        ordersByStatus: {
          'pending': [],
          'confirmed': [],
          'preparing': [],
          'ready': [],
        },
        activeSessions: [],
        isLoading: true,
      );

  DashboardState copyWith({
    DashboardStats? stats,
    Map<String, List<Order>>? ordersByStatus,
    List<TableSession>? activeSessions,
    bool? isLoading,
    bool? isRefreshing,
    String? error,
    bool? pusherConnected,
  }) {
    return DashboardState(
      stats: stats ?? this.stats,
      ordersByStatus: ordersByStatus ?? this.ordersByStatus,
      activeSessions: activeSessions ?? this.activeSessions,
      isLoading: isLoading ?? this.isLoading,
      isRefreshing: isRefreshing ?? this.isRefreshing,
      error: error,
      pusherConnected: pusherConnected ?? this.pusherConnected,
    );
  }
}

// ── Notifier ──
class DashboardNotifier extends StateNotifier<DashboardState> {
  final DashboardService _service;
  final PusherService _pusher;
  final Ref _ref;
  Timer? _refreshDebounce;

  DashboardNotifier(this._service, this._pusher, this._ref)
      : super(DashboardState.initial()) {
    _init();
  }

  Future<void> _init() async {
    await loadDashboard();
    await _connectPusher();
  }

  /// Connect to Pusher for real-time updates
  Future<void> _connectPusher() async {
    final authState = _ref.read(authProvider);
    if (!authState.isAuthenticated || authState.token == null) return;

    final user = authState.user;
    if (user == null) return;

    // Wire up event handlers
    _pusher.onAnyEvent = _onPusherEvent;

    _pusher.onNewOrder = (order) {
      debugPrint('[Dashboard] New order received: ${order.orderNumber}');
    };

    _pusher.onStatusChanged = (orderId, oldStatus, newStatus) {
      debugPrint('[Dashboard] Order $orderId: $oldStatus → $newStatus');
    };

    _pusher.onSessionEvent = (action, data) {
      debugPrint('[Dashboard] Session $action: Table ${data['table_number']}');
    };

    try {
      await _pusher.connect(
        authToken: authState.token!,
        baseUrl: _getBaseUrl(),
        outletId: user.outletId ?? '',
        tenantId: user.tenantId ?? '',
      );
      state = state.copyWith(pusherConnected: true);
    } catch (e) {
      debugPrint('[Dashboard] Pusher connection failed: $e');
    }
  }

  /// Debounced refresh — when Pusher fires events rapidly,
  /// we only refresh once after a short pause.
  void _onPusherEvent() {
    _refreshDebounce?.cancel();
    _refreshDebounce = Timer(const Duration(milliseconds: 500), () {
      refresh();
    });
  }

  String _getBaseUrl() {
    // TODO: Match this to your dio_client baseUrl
    // For local dev: 'http://10.0.2.2:8000' (Android emulator)
    //               'http://localhost:8000' (web/iOS simulator)
    //               'http://YOUR_IP:8000' (physical device)
    return 'http://127.0.0.1:8000';
  }

  /// Full dashboard load
  Future<void> loadDashboard() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final data = await _service.fetchDashboard();
      state = state.copyWith(
        stats: data.stats,
        ordersByStatus: data.ordersByStatus,
        activeSessions: data.activeSessions,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  /// Refresh (pull-to-refresh or Pusher triggered)
  Future<void> refresh() async {
    state = state.copyWith(isRefreshing: true);
    try {
      final data = await _service.fetchDashboard();
      state = state.copyWith(
        stats: data.stats,
        ordersByStatus: data.ordersByStatus,
        activeSessions: data.activeSessions,
        isRefreshing: false,
      );
    } catch (e) {
      state = state.copyWith(isRefreshing: false, error: e.toString());
    }
  }

  /// Update order status via API + refresh
  Future<bool> updateOrderStatus(String orderId, String newStatus) async {
    try {
      await _service.updateOrderStatus(orderId, newStatus);
      await refresh();
      return true;
    } catch (e) {
      state = state.copyWith(error: 'Failed to update status: $e');
      return false;
    }
  }

  @override
  void dispose() {
    _refreshDebounce?.cancel();
    _pusher.disconnect();
    super.dispose();
  }
}

// ── Provider ──
final dashboardProvider =
    StateNotifierProvider<DashboardNotifier, DashboardState>((ref) {
  final service = ref.watch(dashboardServiceProvider);
  final pusher = ref.watch(pusherServiceProvider);
  return DashboardNotifier(service, pusher, ref);
});