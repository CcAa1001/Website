import 'package:json_annotation/json_annotation.dart';
import 'package:equatable/equatable.dart';

part 'product.g.dart';

@JsonSerializable()
class Product extends Equatable {
  final String id;
  final String name;
  final String? sku;
  @JsonKey(name: 'base_price')
  final double basePrice;
  @JsonKey(name: 'medium_image')
  final String? mediumImage;
  @JsonKey(name: 'category_id')
  final String? categoryId;
  @JsonKey(name: 'modifier_groups')
  final List<ModifierGroup>? modifierGroups;

  const Product({
    required this.id,
    required this.name,
    this.sku,
    required this.basePrice,
    this.mediumImage,
    this.categoryId,
    this.modifierGroups,
  });

  factory Product.fromJson(Map<String, dynamic> json) => 
      _$ProductFromJson(json);
  
  Map<String, dynamic> toJson() => _$ProductToJson(this);

  @override
  List<Object?> get props => [id, name, sku, basePrice, mediumImage, categoryId];
}

@JsonSerializable()
class ModifierGroup extends Equatable {
  final String id;
  final String name;
  @JsonKey(name: 'is_required')
  final bool isRequired;
  @JsonKey(name: 'selection_type')
  final String selectionType; // 'single' or 'multiple'
  @JsonKey(name: 'min_selections')
  final int minSelections;
  @JsonKey(name: 'max_selections')
  final int? maxSelections;
  final List<Modifier> modifiers;

  const ModifierGroup({
    required this.id,
    required this.name,
    required this.isRequired,
    required this.selectionType,
    this.minSelections = 0,
    this.maxSelections,
    required this.modifiers,
  });

  factory ModifierGroup.fromJson(Map<String, dynamic> json) => 
      _$ModifierGroupFromJson(json);
  
  Map<String, dynamic> toJson() => _$ModifierGroupToJson(this);

  @override
  List<Object?> get props => [id, name, isRequired, selectionType];
}

@JsonSerializable()
class Modifier extends Equatable {
  final String id;
  final String name;
  final double price;

  const Modifier({
    required this.id,
    required this.name,
    required this.price,
  });

  factory Modifier.fromJson(Map<String, dynamic> json) => 
      _$ModifierFromJson(json);
  
  Map<String, dynamic> toJson() => _$ModifierToJson(this);

  @override
  List<Object?> get props => [id, name, price];
}
