// File: colors.dart
// Path: srea_shared/lib/theme/colors.dart

import 'package:flutter/material.dart';

class SreaColors {
  SreaColors._();

  // ─── Primary Brand ─────────────────────────────────────────
  static const Color primary = Color(0xFF2B4EFF);
  static const Color primaryDark = Color(0xFF1A3ADB);
  static const Color primaryLight = Color(0xFFEEF1FF);

  // ─── Background ────────────────────────────────────────────
  static const Color background = Color(0xFFF8F9FF);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceVariant = Color(0xFFF0F2FB);

  // ─── Text ──────────────────────────────────────────────────
  static const Color textPrimary = Color(0xFF0D0F1C);
  static const Color textSecondary = Color(0xFF5C6080);
  static const Color textHint = Color(0xFFAAADB8);
  static const Color textOnPrimary = Color(0xFFFFFFFF);
  static const Color textOnDark = Color(0xFFFFFFFF);

  // ─── Sidebar ───────────────────────────────────────────────
  static const Color sidebarBg = Color(0xFF2B4EFF);
  static const Color sidebarItem = Color(0xFFFFFFFF);
  static const Color sidebarActive = Color(0xFF1A3ADB);

  // ─── Bottom Navigation ─────────────────────────────────────
  static const Color bottomNavBg = Color(0xFF2B4EFF);
  static const Color bottomNavActive = Color(0xFFFFFFFF);
  static const Color bottomNavInactive = Color(0xFFBBC4FF);

  // ─── Action Buttons ────────────────────────────────────────
  static const Color buttonPrimary = Color(0xFF2B4EFF);
  static const Color buttonReport = Color(0xFFFF3B30);
  static const Color buttonUpdate = Color(0xFF34C759);
  static const Color buttonDisabled = Color(0xFFCDD0E3);

  // ─── FAB ───────────────────────────────────────────────────
  static const Color fab = Color(0xFFFF3B30);
  static const Color fabIcon = Color(0xFFFFFFFF);

  // ─── Alert Levels ──────────────────────────────────────────
  // Critical: Darker, unmistakable red
  static const Color critical = Color(0xFFD32F2F); // Darker red
  static const Color high = Color(0xFFFF6B2B); // Bright orange
  static const Color medium = Color(0xFFFFCC00); // Yellow
  static const Color low = Color(0xFF34C759); // Green

  // Background tints for badges/cards
  static const Color criticalBg = Color(0xFFFFEBEE); // Very light pink/red
  static const Color highBg = Color(0xFFFFF0EA); // Light orange
  static const Color mediumBg = Color(0xFFFFFBE6); // Light yellow
  static const Color lowBg = Color(0xFFEAF9EE); // Light green

  // ─── Text on status backgrounds (improved contrast) ────────
  static const Color onCriticalBg = Color(
    0xFFB71C1C,
  ); // Dark red for text on light red bg
  static const Color onHighBg = Color(0xFFB45F06); // Dark orange
  static const Color onMediumBg = Color(0xFFB8860B); // Dark gold
  static const Color onLowBg = Color(0xFF1E7A3A); // Dark green

  // ─── General Status ────────────────────────────────────────
  static const Color success = Color(0xFF34C759);
  static const Color warning = Color(0xFFFFCC00);
  static const Color error = Color(0xFFFF3B30);
  static const Color info = Color(0xFF2B4EFF);

  // ─── All Clear Banner ──────────────────────────────────────
  static const Color allClearBg = Color(0xFFDFF5E3);
  static const Color allClearIcon = Color(0xFF34C759);
  static const Color allClearText = Color(0xFF1A7A36);

  // ─── Incident Status Tags ──────────────────────────────────
  static const Color tagUnderReview = Color(0xFFFF6B2B);
  static const Color tagResolved = Color(0xFF34C759);
  static const Color tagPending = Color(0xFFFFCC00);
  static const Color tagRejected = Color(0xFFFF3B30);

  static const Color tagUnderReviewBg = Color(0xFFFFF0EA);
  static const Color tagResolvedBg = Color(0xFFEAF9EE);
  static const Color tagPendingBg = Color(0xFFFFFBE6);
  static const Color tagRejectedBg = Color(0xFFFFEBEE);

  // ─── Divider & Border ──────────────────────────────────────
  static const Color divider = Color(0xFFE4E7F0);
  static const Color border = Color(0xFFD0D4E8);
  static const Color borderFocused = Color(0xFF2B4EFF);

  // ─── Overlay ───────────────────────────────────────────────
  static const Color overlay = Color(0x802B4EFF);
  static const Color shadowColor = Color(0x1A2B4EFF);
}
