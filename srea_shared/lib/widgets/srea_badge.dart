// File: srea_badge.dart
// Path: srea_shared/lib/widgets/srea_badge.dart

import 'package:flutter/material.dart';
import '../theme/theme.dart';

enum SreaBadgeType {
  critical,
  high,
  medium,
  low,
  underReview,
  resolved,
  pending,
  rejected,
  info,
  custom,
}

class SreaBadge extends StatelessWidget {
  final SreaBadgeType type;
  final String label;
  final bool showDot;
  final Color? customColor;
  final Color? customBgColor;

  const SreaBadge({
    super.key,
    required this.type,
    required this.label,
    this.showDot = true,
    this.customColor,
    this.customBgColor,
  });

  const SreaBadge.custom({
    super.key,
    required this.label,
    required Color color,
    required Color bgColor,
    this.showDot = true,
  }) : type = SreaBadgeType.custom,
       customColor = color,
       customBgColor = bgColor;

  Color get _color {
    if (type == SreaBadgeType.custom) return customColor!;
    switch (type) {
      case SreaBadgeType.critical:
        return SreaColors.critical;
      case SreaBadgeType.high:
        return SreaColors.high;
      case SreaBadgeType.medium:
        return SreaColors.medium;
      case SreaBadgeType.low:
        return SreaColors.low;
      case SreaBadgeType.underReview:
        return SreaColors.tagUnderReview;
      case SreaBadgeType.resolved:
        return SreaColors.tagResolved;
      case SreaBadgeType.pending:
        return SreaColors.tagPending;
      case SreaBadgeType.rejected:
        return SreaColors.tagRejected;
      case SreaBadgeType.info:
        return SreaColors.primary;
      default:
        return SreaColors.primary;
    }
  }

  Color get _bgColor {
    if (type == SreaBadgeType.custom) return customBgColor!;
    switch (type) {
      case SreaBadgeType.critical:
        return SreaColors.criticalBg;
      case SreaBadgeType.high:
        return SreaColors.highBg;
      case SreaBadgeType.medium:
        return SreaColors.mediumBg;
      case SreaBadgeType.low:
        return SreaColors.lowBg;
      case SreaBadgeType.underReview:
        return SreaColors.tagUnderReviewBg;
      case SreaBadgeType.resolved:
        return SreaColors.tagResolvedBg;
      case SreaBadgeType.pending:
        return SreaColors.tagPendingBg;
      case SreaBadgeType.rejected:
        return SreaColors.tagRejectedBg;
      case SreaBadgeType.info:
        return SreaColors.primaryLight;
      default:
        return SreaColors.primaryLight;
    }
  }

  Color get _textColor {
    if (type == SreaBadgeType.custom) return customColor!;
    switch (type) {
      case SreaBadgeType.critical:
        return SreaColors.onCriticalBg;
      case SreaBadgeType.high:
        return SreaColors.onHighBg;
      case SreaBadgeType.medium:
        return SreaColors.onMediumBg;
      case SreaBadgeType.low:
        return SreaColors.onLowBg;
      case SreaBadgeType.underReview:
        return SreaColors.onHighBg;
      case SreaBadgeType.resolved:
        return SreaColors.onLowBg;
      case SreaBadgeType.pending:
        return SreaColors.onMediumBg;
      case SreaBadgeType.rejected:
        return SreaColors.onCriticalBg;
      case SreaBadgeType.info:
        return SreaColors.primary;
      default:
        return SreaColors.primary;
    }
  }

  EdgeInsets _getPadding(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final horizontal = (width * 0.025).clamp(8.0, 16.0);
    final vertical = (width * 0.01).clamp(4.0, 8.0);
    return EdgeInsets.symmetric(horizontal: horizontal, vertical: vertical);
  }

  double _getFontSize(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return (11.0 * (width / 375).clamp(0.8, 1.2)).clamp(9.0, 14.0);
  }

  double _getDotSize(BuildContext context) => _getFontSize(context) * 0.5;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: _getPadding(context),
      decoration: BoxDecoration(color: _bgColor, borderRadius: SreaRadius.pill),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (showDot) ...[
            Container(
              width: _getDotSize(context),
              height: _getDotSize(context),
              decoration: BoxDecoration(color: _color, shape: BoxShape.circle),
            ),
            SizedBox(width: _getDotSize(context) * 0.8),
          ],
          // Additional visual distinction for critical: add warning icon
          if (type == SreaBadgeType.critical) ...[
            Icon(
              Icons.warning_rounded,
              size: _getFontSize(context) - 2,
              color: _textColor,
            ),
            SizedBox(width: _getDotSize(context) * 0.5),
          ],
          Text(
            label,
            style: SreaText.label(context).copyWith(
              fontSize: _getFontSize(context),
              color: _textColor,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class SreaBadges {
  SreaBadges._();

  static Widget critical([String label = 'Critical']) =>
      SreaBadge(type: SreaBadgeType.critical, label: label);

  static Widget high([String label = 'High']) =>
      SreaBadge(type: SreaBadgeType.high, label: label);

  static Widget medium([String label = 'Medium']) =>
      SreaBadge(type: SreaBadgeType.medium, label: label);

  static Widget low([String label = 'Low']) =>
      SreaBadge(type: SreaBadgeType.low, label: label);

  static Widget underReview([String label = 'Under Review']) =>
      SreaBadge(type: SreaBadgeType.underReview, label: label);

  static Widget resolved([String label = 'Resolved']) =>
      SreaBadge(type: SreaBadgeType.resolved, label: label);

  static Widget pending([String label = 'Pending']) =>
      SreaBadge(type: SreaBadgeType.pending, label: label);

  static Widget rejected([String label = 'Rejected']) =>
      SreaBadge(type: SreaBadgeType.rejected, label: label);
}
