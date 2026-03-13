import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:hive_flutter/hive_flutter.dart';

import 'screens/login_screen.dart';
import 'providers/auth_provider.dart';
import 'screens/pos_screen.dart';
import 'core/theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Hive
  await Hive.initFlutter();
  
  // Set preferred orientations for tablet
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.landscapeLeft,
    DeviceOrientation.landscapeRight,
  ]);
  
  runApp(
    const ProviderScope(
      child: MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ScreenUtilInit(
      designSize: const Size(1280, 800),
      minTextAdapt: true,
      splitScreenMode: true,
      builder: (context, child) {
        return MaterialApp(
          title: 'Restaurant POS',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          home: const AuthWrapper(),
          routes: {
            '/login': (context) => const LoginScreen(),
            '/pos': (context) => const POSScreen(),
          },
        );
      },
    );
  }
}

// Auth Wrapper - Checks authentication status
class AuthWrapper extends ConsumerWidget {
  const AuthWrapper({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);

    // Show loading while checking auth status
    if (authState.isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    // If authenticated, show POS screen
    if (authState.isAuthenticated) {
      return const POSScreen();
    }

    // Otherwise, show login screen
    return const LoginScreen();
  }
}
