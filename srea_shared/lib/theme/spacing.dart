// File: spacing.dart

import 'package:flutter/material.dart';

class SreaSpacing {
  SreaSpacing._();

  static double _scale(BuildContext context, double base) {
    final width = MediaQuery.of(context).size.width;
    final scale = (width / 375).clamp(0.8, 1.5);
    return base * scale;
  }

  static double xs(BuildContext context) => _scale(context, 4);
  static double sm(BuildContext context) => _scale(context, 8);
  static double md(BuildContext context) => _scale(context, 16);
  static double lg(BuildContext context) => _scale(context, 24);
  static double xl(BuildContext context) => _scale(context, 32);
  static double xxl(BuildContext context) => _scale(context, 48);

  static double inputGap(BuildContext context) => _scale(context, 16);
  static double inputLabelGap(BuildContext context) => _scale(context, 6);
  static double sectionHeaderGap(BuildContext context) => _scale(context, 12);
  static double sectionGap(BuildContext context) => _scale(context, 32);
  static double cardGap(BuildContext context) => _scale(context, 12);
  static double avatarGap(BuildContext context) => _scale(context, 12);
  static double iconGap(BuildContext context) => _scale(context, 8);
  static double listItemGap(BuildContext context) => _scale(context, 8);

  static EdgeInsets screenPadding(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 20),
      vertical: _scale(context, 16),
    );
  }

  static EdgeInsets screenScrollPadding(BuildContext context) {
    return EdgeInsets.fromLTRB(
      _scale(context, 20),
      _scale(context, 16),
      _scale(context, 20),
      _scale(context, 32),
    );
  }

  static EdgeInsets appBarPadding(BuildContext context) {
    return EdgeInsets.symmetric(horizontal: _scale(context, 20));
  }

  static EdgeInsets cardPadding(BuildContext context) {
    return EdgeInsets.all(_scale(context, 16));
  }

  static EdgeInsets cardPaddingSmall(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 16),
      vertical: _scale(context, 12),
    );
  }

  static EdgeInsets buttonPadding(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 24),
      vertical: _scale(context, 14),
    );
  }

  static EdgeInsets buttonPaddingSmall(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 16),
      vertical: _scale(context, 10),
    );
  }

  static EdgeInsets buttonPaddingFull(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 20),
      vertical: _scale(context, 16),
    );
  }

  static EdgeInsets inputPadding(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 16),
      vertical: _scale(context, 14),
    );
  }

  static EdgeInsets sectionPadding(BuildContext context) {
    return EdgeInsets.symmetric(vertical: _scale(context, 24));
  }

  static EdgeInsets listItemPadding(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 20),
      vertical: _scale(context, 12),
    );
  }

  static EdgeInsets bottomNavPadding(BuildContext context) {
    return EdgeInsets.symmetric(
      horizontal: _scale(context, 8),
      vertical: _scale(context, 8),
    );
  }

  static EdgeInsets bottomSheetPadding(BuildContext context) {
    return EdgeInsets.fromLTRB(
      _scale(context, 20),
      _scale(context, 24),
      _scale(context, 20),
      _scale(context, 32),
    );
  }

  static EdgeInsets modalPadding(BuildContext context) {
    return EdgeInsets.all(_scale(context, 24));
  }
}