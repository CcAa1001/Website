import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/cart_item.dart';
import '../models/product.dart';
import '../models/table.dart';
import '../models/payment_method.dart';

class POSState {
  final List<CartItem> cart;
  final String orderType; // 'dine_in' or 'takeaway'
  final String? selectedTableId;
  final int guestCount;
  final String? customerName;
  final double taxRate;
  final double serviceChargeRate;
  final bool applyServiceCharge;
  final double discountAmount;
  final List<ParkedOrder> parkedOrders;
  final bool trackInKitchen; // ← NEW: Send to Kitchen Board toggle

  POSState({
    this.cart = const [],
    this.orderType = 'dine_in',
    this.selectedTableId,
    this.guestCount = 1,
    this.customerName,
    this.taxRate = 0.11, // 11% PPN
    this.serviceChargeRate = 0.05, // 5% service
    this.applyServiceCharge = true,
    this.discountAmount = 0,
    this.parkedOrders = const [],
    this.trackInKitchen = true, // ← Default ON (dine-in orders go to kitchen)
  });

  double get subtotal {
    return cart.fold(0.0, (sum, item) => sum + item.itemTotal);
  }

  double get serviceCharge {
    return applyServiceCharge ? subtotal * serviceChargeRate : 0;
  }

  double get taxAmount {
    return (subtotal + serviceCharge) * taxRate;
  }

  double get grandTotal {
    return subtotal + serviceCharge + taxAmount - discountAmount;
  }

  POSState copyWith({
    List<CartItem>? cart,
    String? orderType,
    String? selectedTableId,
    int? guestCount,
    String? customerName,
    double? taxRate,
    double? serviceChargeRate,
    bool? applyServiceCharge,
    double? discountAmount,
    List<ParkedOrder>? parkedOrders,
    bool? trackInKitchen,
  }) {
    return POSState(
      cart: cart ?? this.cart,
      orderType: orderType ?? this.orderType,
      selectedTableId: selectedTableId ?? this.selectedTableId,
      guestCount: guestCount ?? this.guestCount,
      customerName: customerName ?? this.customerName,
      taxRate: taxRate ?? this.taxRate,
      serviceChargeRate: serviceChargeRate ?? this.serviceChargeRate,
      applyServiceCharge: applyServiceCharge ?? this.applyServiceCharge,
      discountAmount: discountAmount ?? this.discountAmount,
      parkedOrders: parkedOrders ?? this.parkedOrders,
      trackInKitchen: trackInKitchen ?? this.trackInKitchen,
    );
  }
}

class ParkedOrder {
  final POSState state;
  final DateTime parkedAt;
  final String? tableNumber;

  ParkedOrder({
    required this.state,
    required this.parkedAt,
    this.tableNumber,
  });
}

class POSNotifier extends StateNotifier<POSState> {
  POSNotifier() : super(POSState());

  void setOrderType(String type) {
    state = state.copyWith(orderType: type);
  }

  void selectTable(String tableId) {
    state = state.copyWith(selectedTableId: tableId);
  }

  void setGuestCount(int count) {
    state = state.copyWith(guestCount: count);
  }

  void setCustomerName(String name) {
    state = state.copyWith(customerName: name);
  }

  // ← NEW: Toggle kitchen tracking
  void toggleTrackInKitchen() {
    state = state.copyWith(trackInKitchen: !state.trackInKitchen);
  }

  void addToCart(CartItem item) {
    final updatedCart = [...state.cart, item];
    state = state.copyWith(cart: updatedCart);
  }

  void updateQuantity(String itemId, int change) {
    final updatedCart = state.cart.map((item) {
      if (item.id == itemId) {
        final newQuantity = item.quantity + change;
        if (newQuantity <= 0) {
          return null;
        }
        return item.copyWith(quantity: newQuantity);
      }
      return item;
    }).whereType<CartItem>().toList();

    state = state.copyWith(cart: updatedCart);
  }

  void removeFromCart(String itemId) {
    final updatedCart = state.cart.where((item) => item.id != itemId).toList();
    state = state.copyWith(cart: updatedCart);
  }

  void clearCart() {
    state = POSState(
      orderType: state.orderType,
      taxRate: state.taxRate,
      serviceChargeRate: state.serviceChargeRate,
      parkedOrders: state.parkedOrders, // ← Preserve parked orders on clear
      trackInKitchen: true, // ← Reset to default
    );
  }

  void parkOrder(String? tableNumber) {
    final parkedOrder = ParkedOrder(
      state: state,
      parkedAt: DateTime.now(),
      tableNumber: tableNumber,
    );
    
    final updatedParked = [...state.parkedOrders, parkedOrder];
    
    state = POSState(
      orderType: state.orderType,
      taxRate: state.taxRate,
      serviceChargeRate: state.serviceChargeRate,
      parkedOrders: updatedParked,
    );
  }

  void loadParkedOrder(int index) {
    if (index >= 0 && index < state.parkedOrders.length) {
      final parked = state.parkedOrders[index];
      final updatedParked = List<ParkedOrder>.from(state.parkedOrders)
        ..removeAt(index);
      
      // ← Restores trackInKitchen from parked state
      state = parked.state.copyWith(parkedOrders: updatedParked);
    }
  }

  void deleteParkedOrder(int index) {
    if (index >= 0 && index < state.parkedOrders.length) {
      final updatedParked = List<ParkedOrder>.from(state.parkedOrders)
        ..removeAt(index);
      state = state.copyWith(parkedOrders: updatedParked);
    }
  }

  void setDiscount(double amount) {
    state = state.copyWith(discountAmount: amount);
  }

  void toggleServiceCharge() {
    state = state.copyWith(applyServiceCharge: !state.applyServiceCharge);
  }
}

final posProvider = StateNotifierProvider<POSNotifier, POSState>((ref) {
  return POSNotifier();
});