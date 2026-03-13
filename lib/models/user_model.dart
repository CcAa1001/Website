import 'package:equatable/equatable.dart';

class User extends Equatable {
  final String id;
  final String name;
  final String email;
  final String? phone;
  final String? role;
  final String? tenantId;
  final String? outletId;
  final String? avatarUrl;
  final String? employeeCode;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.role,
    this.tenantId,
    this.outletId,
    this.avatarUrl,
    this.employeeCode,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as String,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      role: json['role'] as String?,
      tenantId: json['tenant_id'] as String?,
      outletId: json['outlet_id'] as String?,
      avatarUrl: json['avatar_url'] as String?,
      employeeCode: json['employee_code'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'role': role,
      'tenant_id': tenantId,
      'outlet_id': outletId,
      'avatar_url': avatarUrl,
      'employee_code': employeeCode,
    };
  }

  @override
  List<Object?> get props => [
        id,
        name,
        email,
        phone,
        role,
        tenantId,
        outletId,
        avatarUrl,
        employeeCode,
      ];
}
