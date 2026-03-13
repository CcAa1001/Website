import 'package:dio/dio.dart';
import '../services/auth_service.dart';
import '../models/user_model.dart';

class AuthRepository {
  final Dio _dio;
  final AuthService _authService;

  AuthRepository(this._dio, this._authService);

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _dio.post(
        '/login',
        data: {
          'email': email,
          'password': password,
        },
      );

      if (response.data['success'] == true) {
        final token = response.data['token'] as String;
        final user = User.fromJson(response.data['user']);

        // Save to local storage
        await _authService.saveAuth(token: token, user: user);

        return {
          'success': true,
          'user': user,
          'token': token,
        };
      } else {
        return {
          'success': false,
          'message': response.data['message'] ?? 'Login failed',
        };
      }
    } on DioException catch (e) {
      String message = 'Connection error';
      
      if (e.response != null) {
        message = e.response?.data['message'] ?? 'Login failed';
      }

      return {
        'success': false,
        'message': message,
      };
    }
  }

  Future<bool> logout() async {
    try {
      final token = await _authService.getToken();
      
      if (token != null) {
        // Call logout API
        await _dio.post(
          '/logout',
          options: Options(
            headers: {'Authorization': 'Bearer $token'},
          ),
        );
      }
    } catch (e) {
      // Continue even if API call fails
      print('Logout API error: $e');
    }

    // Always clear local data
    await _authService.clearAuth();
    return true;
  }

  Future<User?> getCurrentUser() async {
    try {
      final token = await _authService.getToken();
      
      if (token == null) return null;

      final response = await _dio.get(
        '/me',
        options: Options(
          headers: {'Authorization': 'Bearer $token'},
        ),
      );

      if (response.data['success'] == true) {
        final user = User.fromJson(response.data['user']);
        await _authService.updateUser(user);
        return user;
      }

      return null;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        // Token expired or invalid
        await _authService.clearAuth();
      }
      return null;
    }
  }
}
