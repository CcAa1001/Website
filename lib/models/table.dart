import 'package:json_annotation/json_annotation.dart';
import 'package:equatable/equatable.dart';

part 'table.g.dart';

@JsonSerializable()
class RestaurantTable extends Equatable {
  final String id;
  @JsonKey(name: 'table_number')
  final String tableNumber;
  final String status; // 'available', 'occupied', 'reserved'
  @JsonKey(name: 'status_label')
  final String? statusLabel;

  const RestaurantTable({
    required this.id,
    required this.tableNumber,
    required this.status,
    this.statusLabel,
  });

  factory RestaurantTable.fromJson(Map<String, dynamic> json) => 
      _$RestaurantTableFromJson(json);
  
  Map<String, dynamic> toJson() => _$RestaurantTableToJson(this);

  bool get isAvailable => status == 'available';

  @override
  List<Object?> get props => [id, tableNumber, status];
}
