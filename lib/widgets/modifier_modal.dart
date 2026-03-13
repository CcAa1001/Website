import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product.dart';
import '../models/cart_item.dart';
import '../providers/pos_provider.dart';
import '../repositories/product_repository.dart';
import '../core/theme/app_theme.dart';

class ModifierModal extends ConsumerStatefulWidget {
  final String productId;
  final VoidCallback onClose;

  const ModifierModal({
    super.key,
    required this.productId,
    required this.onClose,
  });

  @override
  ConsumerState<ModifierModal> createState() => _ModifierModalState();
}

class _ModifierModalState extends ConsumerState<ModifierModal> {
  Map<String, bool> selectedModifiers = {};
  String specialInstructions = '';
  int quantity = 1;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.black54,
      child: Center(
        child: Container(
          width: MediaQuery.of(context).size.width * 0.4,
          constraints: const BoxConstraints(maxWidth: 600, maxHeight: 700),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: FutureBuilder<Product?>(
            future: ref.read(productRepositoryProvider).getProductById(widget.productId),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator());
              }

              if (!snapshot.hasData || snapshot.data == null) {
                return const Center(child: Text('Product not found'));
              }

              final product = snapshot.data!;
              return Column(
                children: [
                  _buildHeader(product),
                  Expanded(child: _buildBody(product)),
                  _buildFooter(product),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(Product product) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: AppTheme.border)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  product.name,
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 4),
                Text(
                  'Rp ${_formatNumber(product.basePrice)}',
                  style: const TextStyle(fontSize: 16, color: AppTheme.primary),
                ),
              ],
            ),
          ),
          IconButton(
            onPressed: widget.onClose,
            icon: const Icon(Icons.close),
          ),
        ],
      ),
    );
  }

  Widget _buildBody(Product product) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Product Image
          if (product.mediumImage != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.network(
                product.mediumImage!,
                height: 200,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
          const SizedBox(height: 20),

          // Modifier Groups
          if (product.modifierGroups != null)
            ...product.modifierGroups!.map((group) => _buildModifierGroup(group)),

          // Special Instructions
          const SizedBox(height: 16),
          const Row(
            children: [
              Icon(Icons.note, size: 20),
              SizedBox(width: 8),
              Text('Catatan Khusus (opsional)', style: TextStyle(fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: 8),
          TextField(
            maxLines: 3,
            decoration: const InputDecoration(
              hintText: 'Contoh: Tidak pedas, tanpa bawang...',
              border: OutlineInputBorder(),
            ),
            onChanged: (value) => specialInstructions = value,
          ),

          // Quantity
          const SizedBox(height: 20),
          Row(
            children: [
              const Text('Jumlah:', style: TextStyle(fontWeight: FontWeight.w600)),
              const Spacer(),
              Container(
                decoration: BoxDecoration(
                  border: Border.all(color: AppTheme.border),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    IconButton(
                      onPressed: () => setState(() {
                        if (quantity > 1) quantity--;
                      }),
                      icon: const Icon(Icons.remove),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text('$quantity', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
                    ),
                    IconButton(
                      onPressed: () => setState(() => quantity++),
                      icon: const Icon(Icons.add),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildModifierGroup(ModifierGroup group) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(group.name, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(width: 8),
            if (group.isRequired)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppTheme.danger,
                  borderRadius: BorderRadius.circular(4),
                ),
                child: const Text('Wajib', style: TextStyle(color: Colors.white, fontSize: 12)),
              ),
          ],
        ),
        const SizedBox(height: 12),
        ...group.modifiers.map((modifier) {
          final isSelected = selectedModifiers[modifier.id] ?? false;
          return CheckboxListTile(
            value: isSelected,
            onChanged: (value) => setState(() => selectedModifiers[modifier.id] = value ?? false),
            title: Text(modifier.name),
            subtitle: modifier.price > 0 ? Text('+ Rp ${_formatNumber(modifier.price)}') : null,
          );
        }),
        const SizedBox(height: 16),
      ],
    );
  }

  Widget _buildFooter(Product product) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        border: Border(top: BorderSide(color: AppTheme.border)),
      ),
      child: Row(
        children: [
          Expanded(
            child: OutlinedButton(
              onPressed: widget.onClose,
              child: const Text('Batal'),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: ElevatedButton.icon(
              onPressed: () => _addToCart(product),
              icon: const Icon(Icons.add_shopping_cart),
              label: const Text('Tambah ke Keranjang'),
            ),
          ),
        ],
      ),
    );
  }

  void _addToCart(Product product) {
    final modifiers = selectedModifiers.entries
        .where((entry) => entry.value)
        .map((entry) {
          final modifier = product.modifierGroups!
              .expand((g) => g.modifiers)
              .firstWhere((m) => m.id == entry.key);
          return SelectedModifier.fromModifier(modifier);
        })
        .toList();

    final cartItem = CartItem.fromProduct(
      product: product,
      quantity: quantity,
      modifiers: modifiers,
      specialInstructions: specialInstructions.isNotEmpty ? specialInstructions : null,
    );

    ref.read(posProvider.notifier).addToCart(cartItem);
    widget.onClose();
  }

  String _formatNumber(double number) {
    return number.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    );
  }
}
