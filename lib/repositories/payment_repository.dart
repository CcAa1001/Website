import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/payment_method.dart';
import '../core/network/dio_client.dart';

class PaymentRepository {
  final Dio _dio;

  PaymentRepository(this._dio);

  Future<List<PaymentMethod>> getPaymentMethods() async {
    try {
      final response = await _dio.get('/payment-methods');

      if (response.data is List) {
        return (response.data as List)
            .map((json) => PaymentMethod.fromJson(json))
            .toList();
      }

      if (response.data['data'] is List) {
        return (response.data['data'] as List)
            .map((json) => PaymentMethod.fromJson(json))
            .toList();
      }

      return [];
    } on DioException catch (e) {
      print('Error fetching payment methods: ${e.message}');
      throw Exception('Failed to load payment methods: ${e.message}');
    }
  }
}

final paymentRepositoryProvider = Provider<PaymentRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return PaymentRepository(dio);
});
