import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/pos_provider.dart';
import '../providers/data_providers.dart';
import '../repositories/order_repository.dart';
import '../core/theme/app_theme.dart';

class PaymentModal extends ConsumerStatefulWidget {
  final VoidCallback onClose;

  const PaymentModal({super.key, required this.onClose});

  @override
  ConsumerState<PaymentModal> createState() => _PaymentModalState();
}

class _PaymentModalState extends ConsumerState<PaymentModal> {
  String? selectedPaymentMethodId;
  double cashReceived = 0;
  bool isProcessing = false;

  @override
  Widget build(BuildContext context) {
    final posState = ref.watch(posProvider);
    final paymentMethodsAsync = ref.watch(paymentMethodsProvider);

    return Material(
      color: Colors.black54,
      child: Center(
        child: Container(
          width: 500,
          constraints: const BoxConstraints(maxHeight: 700),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            children: [
              _buildHeader(),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      _buildSummary(posState),
                      const SizedBox(height: 20),
                      _buildPaymentMethods(paymentMethodsAsync),
                      const SizedBox(height: 20),
                      if (selectedPaymentMethodId != null)
                        _buildCashPayment(posState, paymentMethodsAsync),
                    ],
                  ),
                ),
              ),
              _buildFooter(posState),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: AppTheme.border)),
      ),
      child: Row(
        children: [
          const Text('Pembayaran', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w600)),
          const Spacer(),
          IconButton(onPressed: widget.onClose, icon: const Icon(Icons.close)),
        ],
      ),
    );
  }

  Widget _buildSummary(POSState state) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.light,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Ringkasan Order', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          const SizedBox(height: 12),
          _summaryRow('Subtotal', state.subtotal),
          if (state.serviceCharge > 0) _summaryRow('Service', state.serviceCharge),
          _summaryRow('Pajak', state.taxAmount),
          const Divider(height: 20),
          _summaryRow('TOTAL', state.grandTotal, isTotal: true),

          // ── Kitchen Board indicator ──
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: state.trackInKitchen
                  ? AppTheme.primary.withOpacity(0.08)
                  : Colors.grey.shade100,
              borderRadius: BorderRadius.circular(6),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.restaurant,
                  size: 14,
                  color: state.trackInKitchen ? AppTheme.primary : Colors.grey,
                ),
                const SizedBox(width: 6),
                Text(
                  state.trackInKitchen
                      ? 'Dikirim ke Kitchen Board'
                      : 'Tidak dikirim ke Kitchen Board',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: state.trackInKitchen ? AppTheme.primary : Colors.grey,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _summaryRow(String label, double value, {bool isTotal = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text(label, style: TextStyle(fontWeight: isTotal ? FontWeight.w700 : FontWeight.normal)),
          const Spacer(),
          Text(
            'Rp ${_formatNumber(value)}',
            style: TextStyle(
              fontSize: isTotal ? 18 : 14,
              fontWeight: isTotal ? FontWeight.w700 : FontWeight.normal,
              color: isTotal ? AppTheme.primary : null,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethods(AsyncValue paymentMethodsAsync) {
    return paymentMethodsAsync.when(
      data: (methods) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Metode Pembayaran', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          const SizedBox(height: 12),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: methods.map((method) {
              final isSelected = selectedPaymentMethodId == method.id;
              return ChoiceChip(
                label: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      method.isCash
                          ? Icons.payments
                          : method.isQR
                              ? Icons.qr_code_scanner
                              : Icons.credit_card,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Text(method.name),
                  ],
                ),
                selected: isSelected,
                onSelected: (selected) => setState(() {
                  selectedPaymentMethodId = selected ? method.id : null;
                }),
              );
            }).toList(),
          ),
        ],
      ),
      loading: () => const CircularProgressIndicator(),
      error: (_, __) => const Text('Error loading payment methods'),
    );
  }

  Widget _buildCashPayment(POSState state, AsyncValue paymentMethodsAsync) {
    return paymentMethodsAsync.maybeWhen(
      data: (methods) {
        final method = methods.firstWhere((m) => m.id == selectedPaymentMethodId);
        if (!method.isCash) return const SizedBox();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Uang Diterima', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            TextField(
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                prefixText: 'Rp ',
                border: OutlineInputBorder(),
              ),
              onChanged: (value) => setState(() {
                cashReceived = double.tryParse(value) ?? 0;
              }),
            ),
            if (cashReceived >= state.grandTotal) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.success.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Text('Kembalian:', style: TextStyle(fontWeight: FontWeight.w600)),
                    const Spacer(),
                    Text(
                      'Rp ${_formatNumber(cashReceived - state.grandTotal)}',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppTheme.success),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [50000, 100000, 150000, 200000, 500000]
                  .map((amount) => OutlinedButton(
                        onPressed: () => setState(() => cashReceived = amount.toDouble()),
                        child: Text('Rp ${_formatNumber(amount.toDouble())}'),
                      ))
                  .toList(),
            ),
          ],
        );
      },
      orElse: () => const SizedBox(),
    );
  }

  Widget _buildFooter(POSState state) {
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
              onPressed: (selectedPaymentMethodId == null || isProcessing) ? null : () => _completeOrder(state),
              icon: isProcessing
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.check_circle),
              label: Text(isProcessing ? 'Memproses...' : 'Selesaikan Pembayaran'),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _completeOrder(POSState state) async {
    setState(() => isProcessing = true);

    try {
      final orderItems = state.cart.map((item) => {
            'product_id': item.productId,
            'quantity': item.quantity,
            'base_price': item.basePrice,
            'modifiers': item.modifiers.map((m) => {'id': m.id, 'name': m.name, 'price': m.price}).toList(),
            'special_instructions': item.specialInstructions,
          }).toList();

      await ref.read(orderRepositoryProvider).createOrder(
            orderType: state.orderType,
            items: orderItems,
            tableId: state.selectedTableId,
            guestCount: state.guestCount,
            customerName: state.customerName,
            subtotal: state.subtotal,
            taxAmount: state.taxAmount,
            serviceCharge: state.serviceCharge,
            grandTotal: state.grandTotal,
            trackInKitchen: state.trackInKitchen, // ← NEW
            payments: [
              {
                'method_id': selectedPaymentMethodId,
                'amount': state.grandTotal,
                'cash_received': cashReceived,
              }
            ],
          );

      ref.read(posProvider.notifier).clearCart();
      widget.onClose();

      if (mounted) {
        final kitchenMsg = state.trackInKitchen ? ' (dikirim ke Kitchen Board)' : '';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Order berhasil dibuat!$kitchenMsg'),
            backgroundColor: AppTheme.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppTheme.danger),
        );
      }
    } finally {
      if (mounted) setState(() => isProcessing = false);
    }
  }

  String _formatNumber(double number) {
    return number.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    );
  }
}