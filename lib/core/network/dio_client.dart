import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../config/api_config.dart';
import '../../services/auth_service.dart';

final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(
    BaseOptions(
      baseUrl: ApiConfig.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: ApiConfig.headers,
    ),
  );

  // Add logging interceptor
  dio.interceptors.add(
    LogInterceptor(
      requestBody: true,
      responseBody: true,
      error: true,
      logPrint: (obj) => print(obj),
    ),
  );

  // Add auth token interceptor
  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        // Add auth token to all requests except login
        if (!options.path.contains('/login')) {
          final authService = AuthService();
          final token = await authService.getToken();
          
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
        }
        return handler.next(options);
      },
      onError: (error, handler) async {
        // Handle 401 Unauthorized (token expired)
        if (error.response?.statusCode == 401) {
          // Clear auth data
          final authService = AuthService();
          await authService.clearAuth();
          
          // You could also navigate to login screen here
          // or trigger a refresh token flow
        }
        
        print('API Error: ${error.message}');
        return handler.next(error);
      },
    ),
  );

  return dio;
});
