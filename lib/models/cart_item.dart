import 'package:equatable/equatable.dart';
import 'package:uuid/uuid.dart';
import 'product.dart';

class CartItem extends Equatable {
  final String id;
  final String productId;
  final String productName;
  final String? productImage;
  final double basePrice;
  final int quantity;
  final List<SelectedModifier> modifiers;
  final String? specialInstructions;

  const CartItem({
    required this.id,
    required this.productId,
    required this.productName,
    this.productImage,
    required this.basePrice,
    required this.quantity,
    this.modifiers = const [],
    this.specialInstructions,
  });

  double get modifiersTotal {
    return modifiers.fold(0.0, (sum, mod) => sum + mod.price);
  }

  double get itemTotal {
    return (basePrice + modifiersTotal) * quantity;
  }

  CartItem copyWith({
    String? id,
    String? productId,
    String? productName,
    String? productImage,
    double? basePrice,
    int? quantity,
    List<SelectedModifier>? modifiers,
    String? specialInstructions,
  }) {
    return CartItem(
      id: id ?? this.id,
      productId: productId ?? this.productId,
      productName: productName ?? this.productName,
      productImage: productImage ?? this.productImage,
      basePrice: basePrice ?? this.basePrice,
      quantity: quantity ?? this.quantity,
      modifiers: modifiers ?? this.modifiers,
      specialInstructions: specialInstructions ?? this.specialInstructions,
    );
  }

  factory CartItem.fromProduct({
    required Product product,
    int quantity = 1,
    List<SelectedModifier> modifiers = const [],
    String? specialInstructions,
  }) {
    return CartItem(
      id: const Uuid().v4(),
      productId: product.id,
      productName: product.name,
      productImage: product.mediumImage,
      basePrice: product.basePrice,
      quantity: quantity,
      modifiers: modifiers,
      specialInstructions: specialInstructions,
    );
  }

  @override
  List<Object?> get props => [
        id,
        productId,
        productName,
        basePrice,
        quantity,
        modifiers,
        specialInstructions,
      ];
}

class SelectedModifier extends Equatable {
  final String id;
  final String name;
  final double price;

  const SelectedModifier({
    required this.id,
    required this.name,
    required this.price,
  });

  factory SelectedModifier.fromModifier(Modifier modifier) {
    return SelectedModifier(
      id: modifier.id,
      name: modifier.name,
      price: modifier.price,
    );
  }

  @override
  List<Object?> get props => [id, name, price];
}
