// File: radius.dart

import 'package:flutter/material.dart';

class SreaRadius {
  SreaRadius._();

  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double full = 999;

  static BorderRadius button = BorderRadius.circular(lg);
  static BorderRadius card = BorderRadius.circular(md);
  static BorderRadius input = BorderRadius.circular(sm);
  static BorderRadius bottomSheet = const BorderRadius.only(
    topLeft: Radius.circular(lg),
    topRight: Radius.circular(lg),
  );
  static BorderRadius modal = BorderRadius.circular(md);
  static BorderRadius avatar = BorderRadius.circular(xl);
  static BorderRadius pill = BorderRadius.circular(full);
}