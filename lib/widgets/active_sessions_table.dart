import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/dashboard_models.dart';
import '../core/theme/app_theme.dart';

class ActiveSessionsTable extends StatelessWidget {
  final List<TableSession> sessions;
  const ActiveSessionsTable({super.key, required this.sessions});

  @override
  Widget build(BuildContext context) {
    final currencyFmt =
        NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
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
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
              child: Row(
                children: [
                  const Icon(Icons.wifi, size: 20, color: AppTheme.primary),
                  const SizedBox(width: 10),
                  const Text(
                    'Active Table Sessions',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.dark,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppTheme.primary,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${sessions.length}',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const Divider(height: 1, thickness: 2, color: AppTheme.border),

            // ── Table ──
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minWidth: MediaQuery.of(context).size.width - 40,
                ),
                child: DataTable(
                  headingRowColor: MaterialStateProperty.all(AppTheme.light),
                  headingTextStyle: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey.shade600,
                    letterSpacing: 0.5,
                  ),
                  dataTextStyle: const TextStyle(
                    fontSize: 13,
                    color: AppTheme.dark,
                  ),
                  columnSpacing: 32,
                  horizontalMargin: 18,
                  columns: const [
                    DataColumn(label: Text('MEJA')),
                    DataColumn(label: Text('DURATION')),
                    DataColumn(label: Text('ORDERS'), numeric: true),
                    DataColumn(label: Text('TOTAL'), numeric: true),
                    DataColumn(label: Text('STATUS')),
                  ],
                  rows: sessions.map((s) {
                    return DataRow(
                      cells: [
                        // Table
                        DataCell(Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.table_restaurant,
                                size: 18, color: AppTheme.primary),
                            const SizedBox(width: 8),
                            Text(
                              'Meja ${s.tableNumber}',
                              style: const TextStyle(fontWeight: FontWeight.w600),
                            ),
                          ],
                        )),

                        // Duration
                        DataCell(Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              s.duration,
                              style:
                                  const TextStyle(fontWeight: FontWeight.w600),
                            ),
                            Text(
                              '${s.guestCount} tamu',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ],
                        )),

                        // Orders count
                        DataCell(Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppTheme.primary,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            '${s.orderCount}',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        )),

                        // Total
                        DataCell(Text(
                          currencyFmt.format(s.totalAmount),
                          style: const TextStyle(fontWeight: FontWeight.w700),
                        )),

                        // Status badge
                        DataCell(_StatusBadge(status: s.status)),
                      ],
                    );
                  }).toList(),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final isActive = status == 'active';
    final color = isActive ? AppTheme.success : AppTheme.warning;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }
}
