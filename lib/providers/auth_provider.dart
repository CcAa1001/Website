import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import '../services/auth_service.dart';
import '../repositories/auth_repository.dart';
import '../models/user_model.dart';
import '../core/network/dio_client.dart';

// Auth Service Provider
final authServiceProvider = Provider<AuthService>((ref) {
  return AuthService();
});

// Auth Repository Provider
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final dio = ref.watch(dioProvider); // Use your existing dio provider
  final authService = ref.watch(authServiceProvider);
  return AuthRepository(dio, authService);
});

// Auth State
class AuthState {
  final User? user;
  final String? token;
  final bool isLoading;
  final String? error;

  const AuthState({
    this.user,
    this.token,
    this.isLoading = false,
    this.error,
  });

  bool get isAuthenticated => user != null && token != null;

  AuthState copyWith({
    User? user,
    String? token,
    bool? isLoading,
    String? error,
  }) {
    return AuthState(
      user: user ?? this.user,
      token: token ?? this.token,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

// Auth State Notifier
class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepository _authRepository;
  final AuthService _authService;

  AuthNotifier(this._authRepository, this._authService) : super(const AuthState()) {
    _checkAuthStatus();
  }

  // Check if user is already logged in
  Future<void> _checkAuthStatus() async {
    state = state.copyWith(isLoading: true);

    try {
      final token = await _authService.getToken();
      final user = await _authService.getUser();

      if (token != null && user != null) {
        // Verify token is still valid
        final currentUser = await _authRepository.getCurrentUser();
        if (currentUser != null) {
          state = AuthState(user: currentUser, token: token);
        } else {
          state = const AuthState();
        }
      } else {
        state = const AuthState();
      }
    } catch (e) {
      state = const AuthState();
    }
  }

  // Login
  Future<bool> login({
    required String email,
    required String password,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final result = await _authRepository.login(
        email: email,
        password: password,
      );

      if (result['success'] == true) {
        state = AuthState(
          user: result['user'],
          token: result['token'],
        );
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: result['message'],
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  // Logout
  Future<void> logout() async {
    await _authRepository.logout();
    state = const AuthState();
  }

  // Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

// Auth State Provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final authRepository = ref.watch(authRepositoryProvider);
  final authService = ref.watch(authServiceProvider);
  return AuthNotifier(authRepository, authService);
});

// Convenience providers
final currentUserProvider = Provider<User?>((ref) {
  return ref.watch(authProvider).user;
});

final isAuthenticatedProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isAuthenticated;
});
