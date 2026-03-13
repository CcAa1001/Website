import 'package:json_annotation/json_annotation.dart';
import 'package:equatable/equatable.dart';

part 'category.g.dart';

@JsonSerializable()
class Category extends Equatable {
  final String id;
  final String name;
  @JsonKey(name: 'products_count')
  final int productsCount;

  const Category({
    required this.id,
    required this.name,
    this.productsCount = 0,
  });

  factory Category.fromJson(Map<String, dynamic> json) => 
      _$CategoryFromJson(json);
  
  Map<String, dynamic> toJson() => _$CategoryToJson(this);

  @override
  List<Object?> get props => [id, name, productsCount];
}
