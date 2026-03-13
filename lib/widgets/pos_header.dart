import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/pos_provider.dart';
import '../core/theme/app_theme.dart';

class POSHeader extends ConsumerWidget {
  final int parkedOrdersCount;
  final VoidCallback onParkedOrdersTap;

  const POSHeader({
    super.key,
    required this.parkedOrdersCount,
    required this.onParkedOrdersTap,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final posState = ref.watch(posProvider);
    final posNotifier = ref.read(posProvider.notifier);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Title
          Row(
            children: [
              Icon(
                Icons.point_of_sale,
                color: AppTheme.primary,
                size: 28,
              ),
              const SizedBox(width: 12),
              const Text(
                'Point of Sale',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
          
          const SizedBox(width: 32),
          
          // Order Type Selector
          Container(
            decoration: BoxDecoration(
              color: AppTheme.light,
              borderRadius: BorderRadius.circular(8),
            ),
            padding: const EdgeInsets.all(4),
            child: Row(
              children: [
                _OrderTypeButton(
                  icon: Icons.restaurant,
                  label: 'Dine In',
                  isActive: posState.orderType == 'dine_in',
                  onTap: () => posNotifier.setOrderType('dine_in'),
                ),
                const SizedBox(width: 8),
                _OrderTypeButton(
                  icon: Icons.shopping_bag,
                  label: 'Takeaway',
                  isActive: posState.orderType == 'takeaway',
                  onTap: () => posNotifier.setOrderType('takeaway'),
                ),
              ],
            ),
          ),
          
          const Spacer(),
          
          // Parked Orders Button
          if (parkedOrdersCount > 0)
            ElevatedButton.icon(
              onPressed: onParkedOrdersTap,
              icon: const Icon(Icons.bookmark),
              label: Text('Parked ($parkedOrdersCount)'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.warning,
                foregroundColor: AppTheme.dark,
              ),
            ),
        ],
      ),
    );
  }
}

class _OrderTypeButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _OrderTypeButton({
    required this.icon,
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: isActive ? AppTheme.primary : Colors.transparent,
      borderRadius: BorderRadius.circular(6),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(6),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(
            children: [
              Icon(
                icon,
                size: 20,
                color: isActive ? Colors.white : Colors.grey.shade600,
              ),
              const SizedBox(width: 8),
              Text(
                label,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                  color: isActive ? Colors.white : Colors.grey.shade600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
