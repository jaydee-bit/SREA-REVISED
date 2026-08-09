// File: typography.dart

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class SreaText {
  SreaText._();

  static double _scaleFontSize(BuildContext context, double base) {
    final width = MediaQuery.of(context).size.width;
    final scale = (width / 375).clamp(0.85, 1.3);
    return base * scale;
  }

  static TextStyle headlineLarge(BuildContext context) => GoogleFonts.inter(
        fontSize: _scaleFontSize(context, 34),
        fontWeight: FontWeight.w700,
        letterSpacing: -0.5,
        height: 1.2,
      );

  static TextStyle headlineSmall(BuildContext context) => GoogleFonts.inter(
        fontSize: _scaleFontSize(context, 26),
        fontWeight: FontWeight.w600,
        letterSpacing: -0.25,
        height: 1.3,
      );

  static TextStyle titleLarge(BuildContext context) => GoogleFonts.inter(
        fontSize: _scaleFontSize(context, 20),
        fontWeight: FontWeight.w500,
        letterSpacing: 0.0,
        height: 1.4,
      );

  static TextStyle bodyLarge(BuildContext context) => GoogleFonts.inter(
        fontSize: _scaleFontSize(context, 16),
        fontWeight: FontWeight.w400,
        letterSpacing: 0.15,
        height: 1.5,
      );

  static TextStyle bodySmall(BuildContext context) => GoogleFonts.inter(
        fontSize: _scaleFontSize(context, 14),
        fontWeight: FontWeight.w400,
        letterSpacing: 0.15,
        height: 1.5,
      );

  static TextStyle label(BuildContext context) => GoogleFonts.inter(
        fontSize: _scaleFontSize(context, 12),
        fontWeight: FontWeight.w500,
        letterSpacing: 0.5,
        height: 1.4,
      );
}