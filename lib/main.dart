import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:hive_flutter/hive_flutter.dart';

import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/pos_screen.dart';
import 'providers/auth_provider.dart';
import 'core/theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Hive.initFlutter();

  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.landscapeLeft,
    DeviceOrientation.landscapeRight,
    // Also allow portrait for phones
    DeviceOrientation.portraitUp,
  ]);

  runApp(const ProviderScope(child: MyApp()));
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
        );
      },
    );
  }
}

/// Auth wrapper — routes to login or main app shell
class AuthWrapper extends ConsumerWidget {
  const AuthWrapper({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);

    if (authState.isLoading) {
      return Scaffold(
        backgroundColor: AppTheme.background,
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primary, AppTheme.primaryDark],
                  ),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.restaurant, color: Colors.white, size: 28),
              ),
              const SizedBox(height: 20),
              const CircularProgressIndicator(color: AppTheme.primary),
              const SizedBox(height: 12),
              Text(
                'Loading...',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade500,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (authState.isAuthenticated) {
      return const MainShell();
    }

    return const LoginScreen();
  }
}

/// Main app shell with bottom navigation (Dashboard / POS)
class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _currentIndex = 0;

  /// Lazy-build screens so POS only loads when tapped
  Widget _buildScreen(int index) {
    switch (index) {
      case 0:
        return const DashboardScreen();
      case 1:
        return const POSScreen();
      default:
        return const DashboardScreen();
    }
  }

  void _switchTab(int i) {
    if (i != _currentIndex) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) setState(() => _currentIndex = i);
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.of(context).size.width > 800;

    return Scaffold(
      body: Row(
        children: [
          // Side rail for tablets
          if (isWide)
            NavigationRail(
              backgroundColor: Colors.white,
              selectedIndex: _currentIndex,
              onDestinationSelected: _switchTab,
              labelType: NavigationRailLabelType.all,
              indicatorColor: AppTheme.primary.withOpacity(0.12),
              selectedIconTheme: const IconThemeData(color: AppTheme.primary),
              selectedLabelTextStyle: const TextStyle(
                color: AppTheme.primary,
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
              unselectedLabelTextStyle: TextStyle(
                color: Colors.grey.shade500,
                fontSize: 12,
              ),
              leading: Padding(
                padding: const EdgeInsets.symmetric(vertical: 12),
                child: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [AppTheme.primary, AppTheme.primaryDark],
                    ),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.restaurant, color: Colors.white, size: 20),
                ),
              ),
              destinations: const [
                NavigationRailDestination(
                  icon: Icon(Icons.dashboard_outlined),
                  selectedIcon: Icon(Icons.dashboard),
                  label: Text('Dashboard'),
                ),
                NavigationRailDestination(
                  icon: Icon(Icons.point_of_sale_outlined),
                  selectedIcon: Icon(Icons.point_of_sale),
                  label: Text('POS'),
                ),
              ],
            ),

          if (isWide) const VerticalDivider(width: 1, thickness: 1),

          // Main content — lazy loaded, not IndexedStack
          Expanded(
            child: _buildScreen(_currentIndex),
          ),
        ],
      ),

      // Bottom nav for phones
      bottomNavigationBar: isWide
          ? null
          : NavigationBar(
              selectedIndex: _currentIndex,
              onDestinationSelected: _switchTab,
              indicatorColor: AppTheme.primary.withOpacity(0.12),
              height: 64,
              destinations: const [
                NavigationDestination(
                  icon: Icon(Icons.dashboard_outlined),
                  selectedIcon: Icon(Icons.dashboard, color: AppTheme.primary),
                  label: 'Dashboard',
                ),
                NavigationDestination(
                  icon: Icon(Icons.point_of_sale_outlined),
                  selectedIcon:
                      Icon(Icons.point_of_sale, color: AppTheme.primary),
                  label: 'POS',
                ),
              ],
            ),
    );
  }
}