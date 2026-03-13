import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/pos_provider.dart';
import '../core/theme/app_theme.dart';

class ParkedOrdersModal extends ConsumerWidget {
  final VoidCallback onClose;

  const ParkedOrdersModal({super.key, required this.onClose});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final parkedOrders = ref.watch(posProvider).parkedOrders;

    return Material(
      color: Colors.black54,
      child: Center(
        child: Container(
          width: 600,
          constraints: const BoxConstraints(maxHeight: 700),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            children: [
              _buildHeader(context),
              Expanded(child: _buildBody(ref, parkedOrders)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: AppTheme.border)),
      ),
      child: Row(
        children: [
          const Text('Parked Orders', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w600)),
          const Spacer(),
          IconButton(onPressed: onClose, icon: const Icon(Icons.close)),
        ],
      ),
    );
  }

  Widget _buildBody(WidgetRef ref, List<ParkedOrder> orders) {
    if (orders.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.bookmark_border, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text('Tidak ada parked orders'),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(20),
      itemCount: orders.length,
      itemBuilder: (context, index) {
        final order = orders[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            order.state.customerName ?? 'Guest',
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 4),
                          if (order.tableNumber != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppTheme.primary.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text('Meja ${order.tableNumber}'),
                            )
                          else
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppTheme.info.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text('Takeaway'),
                            ),
                        ],
                      ),
                    ),
                    Text(
                      _timeAgo(order.parkedAt),
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Text('${order.state.cart.length} item(s)'),
                    const Spacer(),
                    Text(
                      'Rp ${_formatNumber(order.state.grandTotal)}',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppTheme.primary),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          ref.read(posProvider.notifier).loadParkedOrder(index);
                          onClose();
                        },
                        icon: const Icon(Icons.restore),
                        label: const Text('Muat'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      onPressed: () => ref.read(posProvider.notifier).deleteParkedOrder(index),
                      icon: const Icon(Icons.delete),
                      color: AppTheme.danger,
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  String _timeAgo(DateTime time) {
    final diff = DateTime.now().difference(time);
    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    return '${diff.inDays} hari lalu';
  }

  String _formatNumber(double number) {
    return number.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    );
  }
}
