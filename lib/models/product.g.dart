// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Product _$ProductFromJson(Map<String, dynamic> json) => Product(
      id: json['id'] as String,
      name: json['name'] as String,
      sku: json['sku'] as String?,
      basePrice: (json['base_price'] as num).toDouble(),
      mediumImage: json['medium_image'] as String?,
      categoryId: json['category_id'] as String?,
      modifierGroups: (json['modifier_groups'] as List<dynamic>?)
          ?.map((e) => ModifierGroup.fromJson(e as Map<String, dynamic>))
          .toList(),
    );

Map<String, dynamic> _$ProductToJson(Product instance) => <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'sku': instance.sku,
      'base_price': instance.basePrice,
      'medium_image': instance.mediumImage,
      'category_id': instance.categoryId,
      'modifier_groups': instance.modifierGroups,
    };

ModifierGroup _$ModifierGroupFromJson(Map<String, dynamic> json) =>
    ModifierGroup(
      id: json['id'] as String,
      name: json['name'] as String,
      isRequired: json['is_required'] as bool,
      selectionType: json['selection_type'] as String,
      minSelections: (json['min_selections'] as num?)?.toInt() ?? 0,
      maxSelections: (json['max_selections'] as num?)?.toInt(),
      modifiers: (json['modifiers'] as List<dynamic>)
          .map((e) => Modifier.fromJson(e as Map<String, dynamic>))
          .toList(),
    );

Map<String, dynamic> _$ModifierGroupToJson(ModifierGroup instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'is_required': instance.isRequired,
      'selection_type': instance.selectionType,
      'min_selections': instance.minSelections,
      'max_selections': instance.maxSelections,
      'modifiers': instance.modifiers,
    };

Modifier _$ModifierFromJson(Map<String, dynamic> json) => Modifier(
      id: json['id'] as String,
      name: json['name'] as String,
      price: (json['price'] as num).toDouble(),
    );

Map<String, dynamic> _$ModifierToJson(Modifier instance) => <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'price': instance.price,
    };
