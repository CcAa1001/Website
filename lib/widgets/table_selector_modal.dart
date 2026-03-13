import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/data_providers.dart';
import '../providers/pos_provider.dart';
import '../core/theme/app_theme.dart';

class TableSelectorModal extends ConsumerWidget {
  final VoidCallback onClose;

  const TableSelectorModal({super.key, required this.onClose});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final tablesAsync = ref.watch(tablesProvider);

    return Material(
      color: Colors.black54,
      child: Center(
        child: Container(
          width: MediaQuery.of(context).size.width * 0.5,
          constraints: const BoxConstraints(maxWidth: 700, maxHeight: 600),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            children: [
              _buildHeader(context),
              Expanded(child: _buildBody(ref, tablesAsync)),
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
          const Text('Pilih Meja', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w600)),
          const Spacer(),
          IconButton(onPressed: onClose, icon: const Icon(Icons.close)),
        ],
      ),
    );
  }

  Widget _buildBody(WidgetRef ref, AsyncValue tablesAsync) {
    return tablesAsync.when(
      data: (tables) => GridView.builder(
        padding: const EdgeInsets.all(20),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 4,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          childAspectRatio: 1,
        ),
        itemCount: tables.length,
        itemBuilder: (context, index) {
          final table = tables[index];
          final isSelected = ref.watch(posProvider).selectedTableId == table.id;

          return Material(
            color: !table.isAvailable
                ? Colors.grey.shade300
                : isSelected
                    ? AppTheme.primary
                    : Colors.white,
            borderRadius: BorderRadius.circular(12),
            child: InkWell(
              onTap: table.isAvailable
                  ? () {
                      ref.read(posProvider.notifier).selectTable(table.id);
                      onClose();
                    }
                  : null,
              borderRadius: BorderRadius.circular(12),
              child: Container(
                decoration: BoxDecoration(
                  border: Border.all(color: AppTheme.border),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.table_restaurant,
                      size: 48,
                      color: isSelected ? Colors.white : AppTheme.dark,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      table.tableNumber,
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: isSelected ? Colors.white : AppTheme.dark,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      table.statusLabel ?? table.status,
                      style: TextStyle(
                        fontSize: 12,
                        color: isSelected ? Colors.white : Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => Center(child: Text('Error: $error')),
    );
  }
}
