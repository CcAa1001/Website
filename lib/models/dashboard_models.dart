/// Models matching the Laravel backend dashboard data

class DashboardStats {
  final double todaysEarnings;
  final int totalOrders;
  final int activeOrdersCount;
  final TableStats tableStats;

  const DashboardStats({
    required this.todaysEarnings,
    required this.totalOrders,
    required this.activeOrdersCount,
    required this.tableStats,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) {
    return DashboardStats(
      todaysEarnings: (json['todays_earnings'] ?? 0).toDouble(),
      totalOrders: json['total_orders'] ?? 0,
      activeOrdersCount: json['active_orders_count'] ?? 0,
      tableStats: TableStats.fromJson(json['table_stats'] ?? {}),
    );
  }

  factory DashboardStats.empty() => const DashboardStats(
        todaysEarnings: 0,
        totalOrders: 0,
        activeOrdersCount: 0,
        tableStats: TableStats(occupied: 0, total: 0),
      );
}

class TableStats {
  final int occupied;
  final int total;

  const TableStats({required this.occupied, required this.total});

  double get occupancyRate => total > 0 ? (occupied / total) * 100 : 0;

  factory TableStats.fromJson(Map<String, dynamic> json) {
    return TableStats(
      occupied: json['occupied'] ?? 0,
      total: json['total'] ?? 0,
    );
  }
}

class Order {
  final String id;
  final String orderNumber;
  final String status;
  final String tableNumber;
  final int guestCount;
  final double totalAmount;
  final DateTime createdAt;
  final List<OrderItem> items;
  final String? notes;

  const Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.tableNumber,
    required this.guestCount,
    required this.totalAmount,
    required this.createdAt,
    required this.items,
    this.notes,
  });

  OrderUrgency get urgency {
    final elapsed = DateTime.now().difference(createdAt).inMinutes;
    if (elapsed > 15) return OrderUrgency.urgent;
    if (elapsed > 5) return OrderUrgency.warning;
    return OrderUrgency.fresh;
  }

  String get elapsedTime {
    final diff = DateTime.now().difference(createdAt);
    if (diff.inHours > 0) return '${diff.inHours}h ${diff.inMinutes % 60}m';
    return '${diff.inMinutes}m';
  }

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'].toString(),
      orderNumber: json['order_number'] ?? '',
      status: json['status'] ?? 'pending',
      tableNumber: json['table']?['table_number']?.toString() ?? '-',
      guestCount: json['guest_count'] ?? 0,
      totalAmount: (json['grand_total'] ?? json['total_amount'] ?? 0).toDouble(),
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
      items: (json['items'] as List<dynamic>?)
              ?.map((e) => OrderItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      notes: json['notes'],
    );
  }
}

enum OrderUrgency { fresh, warning, urgent }

class OrderItem {
  final String name;
  final int quantity;
  final List<String> modifiers;
  final String? notes;

  const OrderItem({
    required this.name,
    required this.quantity,
    this.modifiers = const [],
    this.notes,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    // Parse modifiers — Laravel stores as JSON string or as array
    List<String> parsedModifiers = [];
    final rawModifiers = json['modifiers'];
    if (rawModifiers is List) {
      for (final m in rawModifiers) {
        if (m is Map) {
          parsedModifiers.add(
              m['modifier_name']?.toString() ?? m['name']?.toString() ?? '');
        } else if (m is String) {
          parsedModifiers.add(m);
        }
      }
    }

    return OrderItem(
      name: json['product_name'] ?? json['product']?['name'] ?? json['name'] ?? '',
      quantity: json['quantity'] ?? 1,
      modifiers: parsedModifiers,
      notes: json['notes'] ?? json['special_instructions'],
    );
  }
}

class TableSession {
  final String id;
  final String tableNumber;
  final DateTime startedAt;
  final int guestCount;
  final int orderCount;
  final double totalAmount;
  final String status;

  const TableSession({
    required this.id,
    required this.tableNumber,
    required this.startedAt,
    required this.guestCount,
    required this.orderCount,
    required this.totalAmount,
    required this.status,
  });

  String get duration {
    final diff = DateTime.now().difference(startedAt);
    if (diff.inHours > 0) return '${diff.inHours}h ${diff.inMinutes % 60}m';
    return '${diff.inMinutes}m ago';
  }

  factory TableSession.fromJson(Map<String, dynamic> json) {
    return TableSession(
      id: json['id'].toString(),
      tableNumber: json['table']?['table_number']?.toString() ?? '-',
      startedAt: DateTime.tryParse(json['started_at'] ?? '') ?? DateTime.now(),
      guestCount: json['guest_count'] ?? 0,
      orderCount: json['order_count'] ?? 0,
      totalAmount: (json['total_amount'] ?? 0).toDouble(),
      status: json['status'] ?? 'active',
    );
  }
}