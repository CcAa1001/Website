import 'package:flutter/material.dart';

class AppTheme {
  // Colors matching the Livewire CSS
  static const Color primary = Color(0xFFFF6B6B);
  static const Color primaryDark = Color(0xFFEE5252);
  static const Color primaryLight = Color(0xFFFF8787);
  static const Color success = Color(0xFF51CF66);
  static const Color warning = Color(0xFFFFD43B);
  static const Color danger = Color(0xFFFF6B6B);
  static const Color info = Color(0xFF339AF0);
  static const Color dark = Color(0xFF212529);
  static const Color light = Color(0xFFF8F9FA);
  static const Color border = Color(0xFFDEE2E6);
  static const Color background = Color(0xFFF5F6FA);

  static ThemeData get lightTheme {
    return ThemeData(
      primaryColor: primary,
      scaffoldBackgroundColor: background,
      fontFamily: 'Inter',
      
      colorScheme: const ColorScheme.light(
        primary: primary,
        secondary: primaryLight,
        error: danger,
        surface: Colors.white,
      ),
      
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        elevation: 2,
        iconTheme: IconThemeData(color: dark),
        titleTextStyle: TextStyle(
          color: dark,
          fontSize: 20,
          fontWeight: FontWeight.w600,
          fontFamily: 'Inter',
        ),
      ),
      
      cardTheme: CardTheme(
        color: Colors.white,
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        shadowColor: Colors.black.withOpacity(0.05),
      ),
      
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: dark,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          side: const BorderSide(color: border),
        ),
      ),
      
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: light,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: primary, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      ),
      
      chipTheme: ChipThemeData(
        backgroundColor: light,
        selectedColor: primary,
        labelStyle: const TextStyle(fontSize: 14),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    );
  }
}
