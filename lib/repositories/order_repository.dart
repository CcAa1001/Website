import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/network/dio_client.dart';

class OrderRepository {
  final Dio _dio;

  OrderRepository(this._dio);

  Future<Map<String, dynamic>> createOrder({
    required String orderType,
    required List<Map<String, dynamic>> items,
    String? tableId,
    int? guestCount,
    String? customerName,
    required double subtotal,
    required double taxAmount,
    required double serviceCharge,
    required double grandTotal,
    required bool trackInKitchen, 
    required List<Map<String, dynamic>> payments,
  }) async {
    try {
      final orderData = {
        'order_type': orderType,
        'table_id': tableId,
        'guest_count': guestCount,
        'customer_name': customerName,
        'items': items,
        'subtotal': subtotal,
        'tax_amount': taxAmount,
        'service_charge': serviceCharge,
        'track_in_kitchen': trackInKitchen,
        'grand_total': grandTotal,
        'payments': payments,
      };

      final response = await _dio.post(
        '/orders',
        data: orderData,
      );

      return response.data;
    } on DioException catch (e) {
      print('Error creating order: ${e.message}');
      throw Exception('Failed to create order: ${e.message}');
    }
  }
}

final orderRepositoryProvider = Provider<OrderRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return OrderRepository(dio);
});
