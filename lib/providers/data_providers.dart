import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/category.dart';
import '../models/table.dart';
import '../models/payment_method.dart';
import '../repositories/product_repository.dart';
import '../repositories/table_repository.dart';
import '../repositories/payment_repository.dart';

// Products Provider
final productsProvider = FutureProvider.family<List<Product>, ProductFilter>((ref, filter) async {
  final repository = ref.watch(productRepositoryProvider);
  return repository.getProducts(
    search: filter.search,
    categoryId: filter.categoryId,
  );
});

class ProductFilter {
  final String? search;
  final String? categoryId;

  ProductFilter({this.search, this.categoryId});

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ProductFilter &&
          runtimeType == other.runtimeType &&
          search == other.search &&
          categoryId == other.categoryId;

  @override
  int get hashCode => search.hashCode ^ categoryId.hashCode;
}

// Search and Category Filter State
final searchQueryProvider = StateProvider<String>((ref) => '');
final selectedCategoryProvider = StateProvider<String?>((ref) => null);

// Combined Filter Provider
final currentFilterProvider = Provider<ProductFilter>((ref) {
  final search = ref.watch(searchQueryProvider);
  final categoryId = ref.watch(selectedCategoryProvider);
  return ProductFilter(search: search, categoryId: categoryId);
});

// Filtered Products Provider
final filteredProductsProvider = FutureProvider<List<Product>>((ref) {
  final filter = ref.watch(currentFilterProvider);
  return ref.watch(productsProvider(filter).future);
});

// Categories Provider
final categoriesProvider = FutureProvider<List<Category>>((ref) async {
  final repository = ref.watch(productRepositoryProvider);
  return repository.getCategories();
});

// Tables Provider
final tablesProvider = FutureProvider<List<RestaurantTable>>((ref) async {
  final repository = ref.watch(tableRepositoryProvider);
  return repository.getTables();
});

// Payment Methods Provider
final paymentMethodsProvider = FutureProvider<List<PaymentMethod>>((ref) async {
  final repository = ref.watch(paymentRepositoryProvider);
  return repository.getPaymentMethods();
});
