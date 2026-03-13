import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/pos_provider.dart';
import '../providers/data_providers.dart';
import '../core/theme/app_theme.dart';
import '../models/cart_item.dart';

class CartSection extends ConsumerWidget {
  final VoidCallback onTableSelectorTap;
  final VoidCallback onPaymentTap;

  const CartSection({
    super.key,
    required this.onTableSelectorTap,
    required this.onPaymentTap,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final posState = ref.watch(posProvider);
    final posNotifier = ref.read(posProvider.notifier);
    final tablesAsync = ref.watch(tablesProvider);

    return Card(
      margin: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Cart Header
          _buildCartHeader(posState, posNotifier),
          
          // Order Details (Dine-in only)
          if (posState.orderType == 'dine_in')
            _buildOrderDetails(posState, tablesAsync, onTableSelectorTap, posNotifier),
          
          // Customer Info
          _buildCustomerInfo(posState, posNotifier),
          
          // Cart Items
          Expanded(
            child: _buildCartItems(posState, posNotifier),
          ),
          
          // Cart Summary & Actions
          if (posState.cart.isNotEmpty) ...[
            _buildCartSummary(posState, posNotifier),
            _buildKitchenToggle(posState, posNotifier),
            _buildActionButtons(posState, tablesAsync, onPaymentTap, posNotifier),
          ],
        ],
      ),
    );
  }

  Widget _buildCartHeader(POSState state, POSNotifier notifier) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: AppTheme.border),
        ),
      ),
      child: Row(
        children: [
          const Text(
            'Order Saat Ini',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
            ),
          ),
          const Spacer(),
          if (state.cart.isNotEmpty)
            IconButton(
              onPressed: () => notifier.clearCart(),
              icon: const Icon(Icons.delete_sweep),
              color: AppTheme.danger,
              tooltip: 'Clear Cart',
            ),
        ],
      ),
    );
  }

  Widget _buildOrderDetails(
  POSState state,
  AsyncValue tablesAsync,
  VoidCallback onTableTap,
  POSNotifier notifier,
) {
  return Container(
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: state.selectedTableId == null
          ? AppTheme.warning.withOpacity(0.1)
          : Colors.transparent,
      border: Border(
        bottom: BorderSide(color: AppTheme.border),
      ),
    ),
    child: Column(
      children: [
        // Table Selection
        Row(
          children: [
            const Icon(Icons.table_restaurant, size: 20),
            const SizedBox(width: 8),
            const Text('Meja:', style: TextStyle(fontWeight: FontWeight.w500)),
            const Spacer(),
            
            // FIXED TABLE SELECTOR
            tablesAsync.when(
              data: (tables) {
                String tableNumber = 'Pilih Meja';
                bool hasTable = false;
                
                if (state.selectedTableId != null && tables.isNotEmpty) {
                  try {
                    final found = tables.firstWhere(
                      (t) => t.id == state.selectedTableId,
                    );
                    tableNumber = found.tableNumber;
                    hasTable = true;
                  } catch (e) {
                    // Table not found
                  }
                }
                
                return ElevatedButton(
                  onPressed: onTableTap,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: hasTable
                        ? AppTheme.primary.withOpacity(0.1)
                        : AppTheme.danger,
                    foregroundColor: hasTable
                        ? AppTheme.primary
                        : Colors.white,
                    elevation: 0,
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (!hasTable)
                        const Icon(Icons.warning, size: 16),
                      const SizedBox(width: 4),
                      Text(tableNumber),
                      const SizedBox(width: 4),
                      const Icon(Icons.edit, size: 16),
                    ],
                  ),
                );
              },
              loading: () => const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
              error: (_, __) => ElevatedButton(
                onPressed: onTableTap,
                child: const Text('Select Table'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        
        // Guest Count
        Row(
          children: [
            const Icon(Icons.people, size: 20),
            const SizedBox(width: 8),
            const Text('Tamu:', style: TextStyle(fontWeight: FontWeight.w500)),
            const Spacer(),
            SizedBox(
              width: 100,
              child: TextField(
                keyboardType: TextInputType.number,
                textAlign: TextAlign.center,
                decoration: const InputDecoration(
                  contentPadding: EdgeInsets.symmetric(vertical: 8),
                  border: OutlineInputBorder(),
                ),
                controller: TextEditingController(
                  text: state.guestCount.toString(),
                ),
                onChanged: (value) {
                  final count = int.tryParse(value);
                  if (count != null && count > 0) {
                    notifier.setGuestCount(count);
                  }
                },
              ),
            ),
          ],
        ),
      ],
    ),
  );
}

  Widget _buildCustomerInfo(POSState state, POSNotifier notifier) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: AppTheme.border),
        ),
      ),
      child: TextField(
        decoration: const InputDecoration(
          hintText: 'Nama pelanggan (opsional)',
          prefixIcon: Icon(Icons.person_outline),
          border: OutlineInputBorder(),
        ),
        controller: TextEditingController(text: state.customerName),
        onChanged: (value) => notifier.setCustomerName(value),
      ),
    );
  }

  Widget _buildCartItems(POSState state, POSNotifier notifier) {
    if (state.cart.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.shopping_cart, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            const Text(
              'Keranjang kosong',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 8),
            Text(
              'Pilih produk untuk memulai',
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: state.cart.length,
      itemBuilder: (context, index) {
        return _CartItemCard(
          item: state.cart[index],
          onIncrease: () => notifier.updateQuantity(state.cart[index].id, 1),
          onDecrease: () => notifier.updateQuantity(state.cart[index].id, -1),
          onRemove: () => notifier.removeFromCart(state.cart[index].id),
        );
      },
    );
  }

  Widget _buildCartSummary(POSState state, POSNotifier notifier) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(
          top: BorderSide(color: AppTheme.border),
        ),
      ),
      child: Column(
        children: [
          _SummaryRow(label: 'Subtotal', value: state.subtotal),
          if (state.applyServiceCharge && state.serviceCharge > 0)
            _SummaryRow(
              label: 'Service (${(state.serviceChargeRate * 100).toInt()}%)',
              value: state.serviceCharge,
            ),
          _SummaryRow(
            label: 'Pajak (${(state.taxRate * 100).toInt()}%)',
            value: state.taxAmount,
          ),
          if (state.discountAmount > 0)
            _SummaryRow(
              label: 'Diskon',
              value: -state.discountAmount,
              isDiscount: true,
            ),
          const Divider(height: 24),
          _SummaryRow(
            label: 'TOTAL',
            value: state.grandTotal,
            isTotal: true,
          ),
        ],
      ),
    );
  }

  // ── Kitchen Board Toggle ──
  Widget _buildKitchenToggle(POSState state, POSNotifier notifier) {
    final isOn = state.trackInKitchen;

    return InkWell(
      onTap: () => notifier.toggleTrackInKitchen(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          border: Border(
            top: BorderSide(color: AppTheme.border.withOpacity(0.5)),
          ),
          color: isOn
              ? AppTheme.primary.withOpacity(0.04)
              : Colors.transparent,
        ),
        child: Row(
          children: [
            // Icon
            AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                gradient: isOn
                    ? const LinearGradient(
                        colors: [AppTheme.primary, AppTheme.primaryDark],
                      )
                    : null,
                color: isOn ? null : AppTheme.border,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.restaurant,
                color: Colors.white,
                size: 18,
              ),
            ),
            const SizedBox(width: 10),

            // Labels
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Send to Kitchen Board',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.dark,
                    ),
                  ),
                  const SizedBox(height: 1),
                  Text(
                    isOn
                        ? 'Tampil di Kanban Dashboard'
                        : 'Tidak dikirim ke dapur',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: isOn ? AppTheme.primary : Colors.grey.shade500,
                    ),
                  ),
                ],
              ),
            ),

            // Toggle switch
            AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              width: 44,
              height: 24,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(24),
                color: isOn ? AppTheme.primary : Colors.grey.shade300,
              ),
              child: AnimatedAlign(
                duration: const Duration(milliseconds: 250),
                curve: Curves.easeInOut,
                alignment:
                    isOn ? Alignment.centerRight : Alignment.centerLeft,
                child: Container(
                  width: 20,
                  height: 20,
                  margin: const EdgeInsets.symmetric(horizontal: 2),
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black12,
                        blurRadius: 2,
                        offset: Offset(0, 1),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButtons(
    POSState state,
    AsyncValue tablesAsync,
    VoidCallback onPaymentTap,
    POSNotifier notifier,
  ) {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () {
                final tableNumber = tablesAsync.maybeWhen(
                  data: (tables) {
                    final table = tables.firstWhere(
                      (t) => t.id == state.selectedTableId,
                      orElse: () => tables.first,
                    );
                    return table.tableNumber;
                  },
                  orElse: () => null,
                );
                notifier.parkOrder(tableNumber);
              },
              icon: const Icon(Icons.bookmark),
              label: const Text('Park'),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: ElevatedButton.icon(
              onPressed: state.orderType == 'dine_in' && state.selectedTableId == null
                  ? null
                  : onPaymentTap,
              icon: const Icon(Icons.payment),
              label: const Text('Bayar'),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatCurrency(double amount) {
    return 'Rp ${amount.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }
}

class _CartItemCard extends StatelessWidget {
  final CartItem item;
  final VoidCallback onIncrease;
  final VoidCallback onDecrease;
  final VoidCallback onRemove;

  const _CartItemCard({
    required this.item,
    required this.onIncrease,
    required this.onDecrease,
    required this.onRemove,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            if (item.productImage != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: CachedNetworkImage(
                  imageUrl: item.productImage!,
                  width: 60,
                  height: 60,
                  fit: BoxFit.cover,
                  placeholder: (_, __) => Container(color: AppTheme.light),
                  errorWidget: (_, __, ___) => Container(
                    color: AppTheme.light,
                    child: const Icon(Icons.fastfood),
                  ),
                ),
              ),
            const SizedBox(width: 12),
            
            // Details
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.productName,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  
                  // Modifiers
                  if (item.modifiers.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Wrap(
                      spacing: 4,
                      children: item.modifiers.map((mod) {
                        return Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.light,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '+ ${mod.name}${mod.price > 0 ? " (${_formatCurrency(mod.price)})" : ""}',
                            style: const TextStyle(fontSize: 11),
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                  
                  // Special Instructions
                  if (item.specialInstructions != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.note, size: 12),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            item.specialInstructions!,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade700,
                              fontStyle: FontStyle.italic,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                  
                  const SizedBox(height: 8),
                  
                  // Price and Quantity
                  Row(
                    children: [
                      Text(
                        _formatCurrency(item.itemTotal),
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.primary,
                        ),
                      ),
                      const Spacer(),
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(color: AppTheme.border),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            IconButton(
                              onPressed: onDecrease,
                              icon: const Icon(Icons.remove, size: 18),
                              constraints: const BoxConstraints(
                                minWidth: 32,
                                minHeight: 32,
                              ),
                              padding: EdgeInsets.zero,
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              child: Text(
                                '${item.quantity}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                            IconButton(
                              onPressed: onIncrease,
                              icon: const Icon(Icons.add, size: 18),
                              constraints: const BoxConstraints(
                                minWidth: 32,
                                minHeight: 32,
                              ),
                              padding: EdgeInsets.zero,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            
            // Remove button
            IconButton(
              onPressed: onRemove,
              icon: const Icon(Icons.close),
              color: AppTheme.danger,
              constraints: const BoxConstraints(
                minWidth: 32,
                minHeight: 32,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatCurrency(double amount) {
    return 'Rp ${amount.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final double value;
  final bool isTotal;
  final bool isDiscount;

  const _SummaryRow({
    required this.label,
    required this.value,
    this.isTotal = false,
    this.isDiscount = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: isTotal ? 16 : 14,
              fontWeight: isTotal ? FontWeight.w700 : FontWeight.w500,
            ),
          ),
          const Spacer(),
          Text(
            _formatCurrency(value.abs()),
            style: TextStyle(
              fontSize: isTotal ? 18 : 14,
              fontWeight: isTotal ? FontWeight.w700 : FontWeight.w500,
              color: isDiscount ? AppTheme.success : null,
            ),
          ),
        ],
      ),
    );
  }

  String _formatCurrency(double amount) {
    return '${isDiscount ? "- " : ""}Rp ${amount.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }
}