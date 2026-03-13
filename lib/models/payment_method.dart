import 'package:json_annotation/json_annotation.dart';
import 'package:equatable/equatable.dart';

part 'payment_method.g.dart';

@JsonSerializable()
class PaymentMethod extends Equatable {
  final String id;
  final String name;
  @JsonKey(name: 'payment_type')
  final String paymentType; // 'cash', 'qr', 'card', 'other'

  const PaymentMethod({
    required this.id,
    required this.name,
    required this.paymentType,
  });

  factory PaymentMethod.fromJson(Map<String, dynamic> json) => 
      _$PaymentMethodFromJson(json);
  
  Map<String, dynamic> toJson() => _$PaymentMethodToJson(this);

  bool get isCash => paymentType == 'cash';
  bool get isQR => paymentType == 'qr';
  bool get isCard => paymentType == 'card';

  @override
  List<Object?> get props => [id, name, paymentType];
}
