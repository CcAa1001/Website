import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/category.dart';
import '../core/network/dio_client.dart';

class ProductRepository {
  final Dio _dio;

  ProductRepository(this._dio);

  Future<List<Product>> getProducts({
    String? search,
    String? categoryId,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }
      if (categoryId != null) {
        queryParams['category_id'] = categoryId;
      }

      final response = await _dio.get(
        '/products',
        queryParameters: queryParams,
      );

      if (response.data is List) {
        return (response.data as List)
            .map((json) => Product.fromJson(json))
            .toList();
      }
      
      // Handle Laravel pagination response
      if (response.data['data'] is List) {
        return (response.data['data'] as List)
            .map((json) => Product.fromJson(json))
            .toList();
      }

      return [];
    } on DioException catch (e) {
      print('Error fetching products: ${e.message}');
      throw Exception('Failed to load products: ${e.message}');
    }
  }

  Future<List<Category>> getCategories() async {
    try {
      final response = await _dio.get('/categories');

      if (response.data is List) {
        return (response.data as List)
            .map((json) => Category.fromJson(json))
            .toList();
      }

      if (response.data['data'] is List) {
        return (response.data['data'] as List)
            .map((json) => Category.fromJson(json))
            .toList();
      }

      return [];
    } on DioException catch (e) {
      print('Error fetching categories: ${e.message}');
      throw Exception('Failed to load categories: ${e.message}');
    }
  }

  Future<Product?> getProductById(String id) async {
    try {
      final response = await _dio.get('/products/$id');
      return Product.fromJson(response.data);
    } on DioException catch (e) {
      print('Error fetching product: ${e.message}');
      return null;
    }
  }
}

final productRepositoryProvider = Provider<ProductRepository>((ref) {
  final dio = ref.watch(dioProvider);
  return ProductRepository(dio);
});
