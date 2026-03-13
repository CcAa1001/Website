import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/data_providers.dart';
import '../models/product.dart';
import '../core/theme/app_theme.dart';

class ProductsSection extends ConsumerWidget {
  final Function(String) onProductTap;

  const ProductsSection({
    super.key,
    required this.onProductTap,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Search and Controls
          _buildSearchBox(ref),
          const SizedBox(height: 16),
          
          // Category Tabs
          _buildCategoryTabs(ref),
          const SizedBox(height: 16),
          
          // Products Grid
          Expanded(
            child: _buildProductsGrid(ref),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBox(WidgetRef ref) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
          ),
        ],
      ),
      child: TextField(
        decoration: InputDecoration(
          hintText: 'Cari produk...',
          prefixIcon: const Icon(Icons.search),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(color: AppTheme.border),
          ),
          filled: true,
          fillColor: AppTheme.light,
        ),
        onChanged: (value) {
          ref.read(searchQueryProvider.notifier).state = value;
        },
      ),
    );
  }

  Widget _buildCategoryTabs(WidgetRef ref) {
    final categoriesAsync = ref.watch(categoriesProvider);
    final selectedCategory = ref.watch(selectedCategoryProvider);

    return categoriesAsync.when(
      data: (categories) => SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _CategoryTab(
              label: 'Semua',
              icon: Icons.apps,
              isSelected: selectedCategory == null,
              onTap: () => ref.read(selectedCategoryProvider.notifier).state = null,
            ),
            ...categories.map((category) => _CategoryTab(
              label: category.name,
              count: category.productsCount,
              isSelected: selectedCategory == category.id,
              onTap: () => ref.read(selectedCategoryProvider.notifier).state = category.id,
            )),
          ],
        ),
      ),
      loading: () => const CircularProgressIndicator(),
      error: (_, __) => const SizedBox(),
    );
  }

  Widget _buildProductsGrid(WidgetRef ref) {
    final productsAsync = ref.watch(filteredProductsProvider);

    return productsAsync.when(
      data: (products) => GridView.builder(
        gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
          maxCrossAxisExtent: 200,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          childAspectRatio: 0.75,
        ),
        itemCount: products.length,
        itemBuilder: (context, index) {
          return _ProductCard(
            product: products[index],
            onTap: () => onProductTap(products[index].id),
          );
        },
      ),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => Center(child: Text('Error: $error')),
    );
  }
}

class _CategoryTab extends StatelessWidget {
  final String label;
  final IconData? icon;
  final int? count;
  final bool isSelected;
  final VoidCallback onTap;

  const _CategoryTab({
    required this.label,
    this.icon,
    this.count,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: Material(
        color: isSelected ? AppTheme.primary : Colors.white,
        borderRadius: BorderRadius.circular(8),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(8),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 20, color: isSelected ? Colors.white : AppTheme.dark),
                  const SizedBox(width: 8),
                ],
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: isSelected ? Colors.white : AppTheme.dark,
                  ),
                ),
                if (count != null && count! > 0) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: isSelected ? Colors.white.withOpacity(0.3) : AppTheme.light,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '$count',
                      style: TextStyle(
                        fontSize: 12,
                        color: isSelected ? Colors.white : AppTheme.dark,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onTap;

  const _ProductCard({
    required this.product,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Product Image
            Expanded(
              child: Stack(
                children: [
                  if (product.mediumImage != null)
                    CachedNetworkImage(
                      imageUrl: product.mediumImage!,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      placeholder: (_, __) => Container(color: AppTheme.light),
                      errorWidget: (_, __, ___) => Container(
                        color: AppTheme.light,
                        child: const Icon(Icons.image_not_supported),
                      ),
                    )
                  else
                    Container(
                      color: AppTheme.light,
                      child: const Center(child: Icon(Icons.fastfood)),
                    ),
                  
                  // Customizable badge
                  if (product.modifierGroups?.isNotEmpty ?? false)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: AppTheme.primary,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Icon(Icons.tune, size: 16, color: Colors.white),
                      ),
                    ),
                ],
              ),
            ),
            
            // Product Info
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Rp ${_formatNumber(product.basePrice)}',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.primary,
                    ),
                  ),
                  if (product.sku != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      product.sku!,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatNumber(double number) {
    return number.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    );
  }
}
