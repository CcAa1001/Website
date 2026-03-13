import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/dashboard_models.dart';
import '../core/theme/app_theme.dart';

class KanbanBoard extends StatelessWidget {
  final Map<String, List<Order>> ordersByStatus;
  final Function(String orderId, String newStatus) onUpdateStatus;

  const KanbanBoard({
    super.key,
    required this.ordersByStatus,
    required this.onUpdateStatus,
  });

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final isWide = width > 900;

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          children: [
            // ── Header ──
            _KanbanHeader(),

            // ── Columns ──
            if (isWide)
              // Tablet landscape: 4 columns with fixed height (no IntrinsicHeight)
              SizedBox(
                height: 550,
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _buildColumn('pending', 'Baru (Bayar Nanti)', Icons.new_releases, AppTheme.warning, 'confirmed', 'Konfirmasi'),
                    _divider(),
                    _buildColumn('confirmed', 'Dikonfirmasi', Icons.check_circle, AppTheme.info, 'preparing', 'Proses'),
                    _divider(),
                    _buildColumn('preparing', 'Diproses', Icons.restaurant_menu, AppTheme.primary, 'ready', 'Siap'),
                    _divider(),
                    _buildColumn('ready', 'Siap Diantar', Icons.done_all, AppTheme.success, 'served', 'Served'),
                  ],
                ),
              )
            else
              // Phone/small tablet: horizontal scroll
              SizedBox(
                height: 500,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.all(0),
                  children: [
                    SizedBox(width: width * 0.75, child: _buildColumn('pending', 'Baru (Bayar Nanti)', Icons.new_releases, AppTheme.warning, 'confirmed', 'Konfirmasi')),
                    _divider(),
                    SizedBox(width: width * 0.75, child: _buildColumn('confirmed', 'Dikonfirmasi', Icons.check_circle, AppTheme.info, 'preparing', 'Proses')),
                    _divider(),
                    SizedBox(width: width * 0.75, child: _buildColumn('preparing', 'Diproses', Icons.restaurant_menu, AppTheme.primary, 'ready', 'Siap')),
                    _divider(),
                    SizedBox(width: width * 0.75, child: _buildColumn('ready', 'Siap Diantar', Icons.done_all, AppTheme.success, 'served', 'Served')),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _divider() => Container(width: 1, color: AppTheme.border);

  Widget _buildColumn(
    String statusKey,
    String title,
    IconData icon,
    Color color,
    String nextStatus,
    String nextLabel,
  ) {
    final orders = ordersByStatus[statusKey] ?? [];

    return Expanded(
      child: Column(
        children: [
          // Column header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              border: Border(
                bottom: BorderSide(color: AppTheme.border, width: 2),
              ),
            ),
            child: Row(
              children: [
                Icon(icon, size: 18, color: color),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: color,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppTheme.light,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${orders.length}',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.dark,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Cards list
          Expanded(
            child: orders.isEmpty
                ? _EmptyColumn(icon: icon, color: color)
                : ListView.separated(
                    padding: const EdgeInsets.all(10),
                    itemCount: orders.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (_, i) => _OrderCard(
                      order: orders[i],
                      nextStatus: nextStatus,
                      nextLabel: nextLabel,
                      accentColor: color,
                      onAction: () =>
                          onUpdateStatus(orders[i].id, nextStatus),
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}

// ── Kanban Header ──
class _KanbanHeader extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primary, AppTheme.primaryDark],
        ),
      ),
      child: Row(
        children: [
          const Icon(Icons.view_kanban, color: Colors.white, size: 24),
          const SizedBox(width: 10),
          const Text(
            'Order Tracking Board',
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w600,
              color: Colors.white,
            ),
          ),
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'Lihat Semua',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: AppTheme.primary,
                  ),
                ),
                SizedBox(width: 4),
                Icon(Icons.arrow_forward, size: 16, color: AppTheme.primary),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Order Card ──
class _OrderCard extends StatelessWidget {
  final Order order;
  final String nextStatus;
  final String nextLabel;
  final Color accentColor;
  final VoidCallback onAction;

  const _OrderCard({
    required this.order,
    required this.nextStatus,
    required this.nextLabel,
    required this.accentColor,
    required this.onAction,
  });

  Color get _urgencyColor {
    switch (order.urgency) {
      case OrderUrgency.urgent:
        return AppTheme.danger;
      case OrderUrgency.warning:
        return AppTheme.warning;
      case OrderUrgency.fresh:
        return AppTheme.success;
    }
  }

  String get _urgencyEmoji {
    switch (order.urgency) {
      case OrderUrgency.urgent:
        return '🔴';
      case OrderUrgency.warning:
        return '🟡';
      case OrderUrgency.fresh:
        return '🟢';
    }
  }

  @override
  Widget build(BuildContext context) {
    final currencyFmt =
        NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.border),
        boxShadow: [
          if (order.urgency == OrderUrgency.urgent)
            BoxShadow(
              color: AppTheme.danger.withOpacity(0.15),
              blurRadius: 8,
              spreadRadius: 1,
            ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Card Header ──
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Colors.grey.shade50,
                  Colors.grey.shade100,
                ],
              ),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    Text(_urgencyEmoji, style: const TextStyle(fontSize: 18)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Meja ${order.tableNumber}',
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.dark,
                            ),
                          ),
                          Row(
                            children: [
                              Icon(Icons.people, size: 13, color: Colors.grey.shade500),
                              const SizedBox(width: 3),
                              Text(
                                '${order.guestCount} tamu',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey.shade500,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      order.orderNumber,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.primary,
                      ),
                    ),
                    Row(
                      children: [
                        Icon(Icons.schedule, size: 13, color: Colors.grey.shade500),
                        const SizedBox(width: 3),
                        Text(
                          order.elapsedTime,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: _urgencyColor,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Left urgency border accent
          Container(
            height: 3,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [_urgencyColor, _urgencyColor.withOpacity(0.2)],
              ),
            ),
          ),

          // ── Items ──
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Column(
              children: [
                for (int i = 0; i < order.items.length && i < 3; i++)
                  _ItemRow(item: order.items[i]),
                if (order.items.length > 3)
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text(
                      '+${order.items.length - 3} item lainnya',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: AppTheme.info,
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // ── Footer ──
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              border: Border(
                top: BorderSide(color: AppTheme.border, width: 2),
              ),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Total',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    Text(
                      currencyFmt.format(order.totalAmount),
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.primary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    // Detail button
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: AppTheme.light,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.visibility, size: 18, color: AppTheme.dark),
                    ),
                    const SizedBox(width: 8),
                    // Action button
                    Expanded(
                      child: Material(
                        color: accentColor,
                        borderRadius: BorderRadius.circular(8),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(8),
                          onTap: onAction,
                          child: Padding(
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.arrow_forward, size: 16, color: Colors.white),
                                const SizedBox(width: 6),
                                Text(
                                  nextLabel,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Item Row ──
class _ItemRow extends StatelessWidget {
  final OrderItem item;
  const _ItemRow({required this.item});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.name,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.dark,
                  ),
                ),
                if (item.modifiers.isNotEmpty)
                  Wrap(
                    spacing: 4,
                    runSpacing: 2,
                    children: item.modifiers
                        .map((m) => Container(
                              margin: const EdgeInsets.only(top: 3),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 1),
                              decoration: BoxDecoration(
                                color: AppTheme.primary.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                m,
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w500,
                                  color: AppTheme.primary,
                                ),
                              ),
                            ))
                        .toList(),
                  ),
                if (item.notes != null)
                  Container(
                    margin: const EdgeInsets.only(top: 4),
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: AppTheme.warning.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(6),
                      border: Border(
                        left: BorderSide(color: AppTheme.warning, width: 3),
                      ),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.note, size: 12, color: AppTheme.warning),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            item.notes!,
                            style: TextStyle(
                              fontSize: 11,
                              fontStyle: FontStyle.italic,
                              color: Colors.amber.shade800,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            'x${item.quantity}',
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: AppTheme.primary,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Empty Column ──
class _EmptyColumn extends StatelessWidget {
  final IconData icon;
  final Color color;
  const _EmptyColumn({required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 40, color: color.withOpacity(0.2)),
          const SizedBox(height: 8),
          Text(
            'Tidak ada order',
            style: TextStyle(
              fontSize: 13,
              color: Colors.grey.shade400,
            ),
          ),
        ],
      ),
    );
  }
}