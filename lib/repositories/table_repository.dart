import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/table.dart';
import '../core/network/dio_client.dart';

class TableRepository {
  final Dio _dio;

  TableRepository(this._dio);

  Future<List<RestaurantTable>> getTables() async {
    try {
      final response = await _dio.get('/tables');

      if (response.data is List) {
        return (response.data as List)
            .map((json) => RestaurantTable.fromJson(json))
            .toList();
      }

      if (response.data['data'] is List) {
        return (response.data['data'] as List)
            .map((json) => RestaurantTable.fromJson(json))
            .toList();
      }

      return [];
    } on DioException catch (e) {
      print('Error fetching tables: ${e.message}');
      throw Exception('Failed to load tables: ${e.message}');
    }
  }
}

final tableRepositoryProvider = Provider<TableRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return TableRepository(dio);
});
