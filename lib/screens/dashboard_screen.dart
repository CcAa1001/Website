import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../providers/dashboard_provider.dart';
import '../providers/auth_provider.dart';
import '../models/dashboard_models.dart';
import '../core/theme/app_theme.dart';
import '../widgets/stat_card.dart';
import '../widgets/kanban_board.dart';
import '../widgets/active_sessions_table.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(dashboardProvider);
    final user = ref.watch(currentUserProvider);

    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SafeArea(
        child: state.isLoading
            ? const _LoadingState()
            : state.error != null
                ? _ErrorState(
                    error: state.error!,
                    onRetry: () =>
                        ref.read(dashboardProvider.notifier).loadDashboard(),
                  )
                : RefreshIndicator(
                    color: AppTheme.primary,
                    onRefresh: () =>
                        ref.read(dashboardProvider.notifier).refresh(),
                    child: CustomScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      slivers: [
                        // ── App Bar ──
                        SliverToBoxAdapter(
                          child: _DashboardHeader(
                            userName: user?.name ?? 'Admin',
                            isRefreshing: state.isRefreshing,
                            pusherConnected: state.pusherConnected,
                            onRefresh: () => ref
                                .read(dashboardProvider.notifier)
                                .refresh(),
                            onLogout: () =>
                                ref.read(authProvider.notifier).logout(),
                          ),
                        ),

                        // ── Stat Cards ──
                        SliverToBoxAdapter(
                          child: _StatsSection(stats: state.stats),
                        ),

                        // ── Kanban Board ──
                        SliverToBoxAdapter(
                          child: KanbanBoard(
                            ordersByStatus: state.ordersByStatus,
                            onUpdateStatus: (orderId, newStatus) => ref
                                .read(dashboardProvider.notifier)
                                .updateOrderStatus(orderId, newStatus),
                          ),
                        ),

                        // ── Active Sessions ──
                        if (state.activeSessions.isNotEmpty)
                          SliverToBoxAdapter(
                            child: ActiveSessionsTable(
                              sessions: state.activeSessions,
                            ),
                          ),

                        // Bottom spacing
                        const SliverToBoxAdapter(
                          child: SizedBox(height: 24),
                        ),
                      ],
                    ),
                  ),
      ),
    );
  }
}

// ── Header ──
class _DashboardHeader extends StatelessWidget {
  final String userName;
  final bool isRefreshing;
  final bool pusherConnected;
  final VoidCallback onRefresh;
  final VoidCallback onLogout;

  const _DashboardHeader({
    required this.userName,
    required this.isRefreshing,
    required this.pusherConnected,
    required this.onRefresh,
    required this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
      child: Row(
        children: [
          // Logo + Title
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primary, AppTheme.primaryDark],
              ),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.restaurant, color: Colors.white, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      'Dashboard',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.dark,
                      ),
                    ),
                    const SizedBox(width: 8),
                    // Pusher live indicator
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: pusherConnected
                            ? AppTheme.success.withOpacity(0.1)
                            : Colors.grey.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 6,
                            height: 6,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: pusherConnected ? AppTheme.success : Colors.grey,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            pusherConnected ? 'LIVE' : 'OFFLINE',
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                              color: pusherConnected ? AppTheme.success : Colors.grey,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                Text(
                  'Welcome back, $userName',
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          ),

          // Refresh
          Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            elevation: 1,
            child: InkWell(
              borderRadius: BorderRadius.circular(10),
              onTap: isRefreshing ? null : onRefresh,
              child: Padding(
                padding: const EdgeInsets.all(10),
                child: isRefreshing
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: AppTheme.primary,
                        ),
                      )
                    : const Icon(Icons.refresh, size: 20, color: AppTheme.primary),
              ),
            ),
          ),
          const SizedBox(width: 8),

          // Logout
          Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            elevation: 1,
            child: InkWell(
              borderRadius: BorderRadius.circular(10),
              onTap: onLogout,
              child: const Padding(
                padding: EdgeInsets.all(10),
                child: Icon(Icons.logout, size: 20, color: Colors.grey),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Stats Section ──
class _StatsSection extends StatelessWidget {
  final DashboardStats stats;
  const _StatsSection({required this.stats});

  @override
  Widget build(BuildContext context) {
    final currencyFormat =
        NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);
    final width = MediaQuery.of(context).size.width;
    final crossAxisCount = width > 1100 ? 4 : (width > 600 ? 2 : 1);

    final cards = [
      StatCardData(
        label: 'Pendapatan Hari Ini',
        value: currencyFormat.format(stats.todaysEarnings),
        trend: '+12% vs kemarin',
        trendPositive: true,
        icon: Icons.payments,
        gradientColors: [const Color(0xFF667EEA), const Color(0xFF764BA2)],
      ),
      StatCardData(
        label: 'Total Order Hari Ini',
        value: stats.totalOrders.toString(),
        trend: '+8% vs kemarin',
        trendPositive: true,
        icon: Icons.receipt_long,
        gradientColors: [AppTheme.primary, AppTheme.primaryDark],
      ),
      StatCardData(
        label: 'Order Aktif',
        value: stats.activeOrdersCount.toString(),
        subtitle: 'Perlu perhatian',
        icon: Icons.restaurant,
        gradientColors: [const Color(0xFFF093FB), const Color(0xFFF5576C)],
      ),
      StatCardData(
        label: 'Meja Terisi',
        value: '${stats.tableStats.occupied}/${stats.tableStats.total}',
        subtitle: '${stats.tableStats.occupancyRate.round()}% occupancy',
        icon: Icons.table_restaurant,
        gradientColors: [const Color(0xFF4FACFE), const Color(0xFF00F2FE)],
      ),
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final availableWidth = constraints.maxWidth;
          final crossAxisCount = availableWidth > 1100 ? 4 : (availableWidth > 600 ? 2 : 1);
          final spacing = 14.0;
          final cardWidth = (availableWidth - (spacing * (crossAxisCount - 1))) / crossAxisCount;

          return Wrap(
            spacing: spacing,
            runSpacing: spacing,
            children: cards.map((data) => SizedBox(
              width: cardWidth,
              height: 110,
              child: StatCard(data: data),
            )).toList(),
          );
        },
      ),
    );
  }
}

// ── Loading ──
class _LoadingState extends StatelessWidget {
  const _LoadingState();

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          CircularProgressIndicator(color: AppTheme.primary),
          SizedBox(height: 16),
          Text('Loading dashboard...', style: TextStyle(color: Colors.grey)),
        ],
      ),
    );
  }
}

// ── Error ──
class _ErrorState extends StatelessWidget {
  final String error;
  final VoidCallback onRetry;
  const _ErrorState({required this.error, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.error_outline, size: 48, color: AppTheme.danger),
          const SizedBox(height: 12),
          Text(error, style: const TextStyle(color: Colors.grey), textAlign: TextAlign.center),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh),
            label: const Text('Retry'),
          ),
        ],
      ),
    );
  }
}