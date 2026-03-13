import 'package:dio/dio.dart';
import '../models/dashboard_models.dart';

/// Dashboard data bundle
class DashboardData {
  final DashboardStats stats;
  final Map<String, List<Order>> ordersByStatus;
  final List<TableSession> activeSessions;

  const DashboardData({
    required this.stats,
    required this.ordersByStatus,
    required this.activeSessions,
  });
}

/// Real API service — uses your existing Dio instance with auth headers.
class DashboardService {
  final Dio _dio;

  DashboardService(this._dio);

  /// Fetch all dashboard data in parallel
  Future<DashboardData> fetchDashboard() async {
    // baseUrl is already http://127.0.0.1:8000/api
    // so paths match your other routes: '/login', '/products', etc.
    final results = await Future.wait([
      _dio.get('/dashboard/stats'),
      _dio.get('/dashboard/orders'),
      _dio.get('/dashboard/sessions'),
    ]);

    final statsRes = results[0].data as Map<String, dynamic>;
    final ordersRes = results[1].data as Map<String, dynamic>;
    final sessionsRes = results[2].data as Map<String, dynamic>;

    final stats = DashboardStats.fromJson(statsRes);

    final ordersByStatus = <String, List<Order>>{};
    for (final status in ['pending', 'confirmed', 'preparing', 'ready']) {
      final list = (ordersRes[status] as List<dynamic>?) ?? [];
      ordersByStatus[status] = list
          .map((e) => Order.fromJson(e as Map<String, dynamic>))
          .toList();
    }

    final sessionsList = (sessionsRes['data'] as List<dynamic>?) ?? [];
    final sessions = sessionsList
        .map((e) => TableSession.fromJson(e as Map<String, dynamic>))
        .toList();

    return DashboardData(
      stats: stats,
      ordersByStatus: ordersByStatus,
      activeSessions: sessions,
    );
  }

  /// Update order status — matches PUT /api/orders/{id}/status
  Future<void> updateOrderStatus(String orderId, String newStatus) async {
    await _dio.put('/orders/$orderId/status', data: {
      'status': newStatus,
    });
  }
}