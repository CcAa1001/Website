// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'table.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

RestaurantTable _$RestaurantTableFromJson(Map<String, dynamic> json) =>
    RestaurantTable(
      id: json['id'] as String,
      tableNumber: json['table_number'] as String,
      status: json['status'] as String,
      statusLabel: json['status_label'] as String?,
    );

Map<String, dynamic> _$RestaurantTableToJson(RestaurantTable instance) =>
    <String, dynamic>{
      'id': instance.id,
      'table_number': instance.tableNumber,
      'status': instance.status,
      'status_label': instance.statusLabel,
    };
