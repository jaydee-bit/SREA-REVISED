// File: srea_shared/lib/widgets/srea_card.dart

import 'package:flutter/material.dart';
import '../theme/theme.dart';

EdgeInsets _responsiveCardPadding(BuildContext context, {bool small = false}) {
  final width = MediaQuery.of(context).size.width;
  final horizontal = (width * 0.045).clamp(12.0, 28.0);
  final vertical = small
      ? (width * 0.025).clamp(8.0, 16.0)
      : (width * 0.04).clamp(12.0, 24.0);
  return EdgeInsets.symmetric(horizontal: horizontal, vertical: vertical);
}

class SreaCard extends StatelessWidget {
  final Widget child;
  final EdgeInsets? padding;
  final VoidCallback? onTap;
  final Color? color;
  final BorderRadius? borderRadius;
  final bool hasShadow;

  const SreaCard({
    super.key,
    required this.child,
    this.padding,
    this.onTap,
    this.color,
    this.borderRadius,
    this.hasShadow = true,
  });

  @override
  Widget build(BuildContext context) {
    final br = borderRadius ?? SreaRadius.card;
    final effectivePadding = padding ?? _responsiveCardPadding(context);

    return Material(
      color: color ?? SreaColors.surface,
      borderRadius: br,
      child: InkWell(
        onTap: onTap,
        borderRadius: br,
        splashColor: SreaColors.primaryLight,
        highlightColor: SreaColors.primaryLight.withValues(alpha: 0.5),
        focusColor: SreaColors.primaryLight,
        hoverColor: SreaColors.primaryLight.withValues(alpha: 0.1),
        child: Container(
          padding: effectivePadding,
          decoration: BoxDecoration(
            borderRadius: br,
            border: Border.all(color: SreaColors.border, width: 0.5),
            boxShadow: hasShadow
                ? [
                    BoxShadow(
                      color: SreaColors.shadowColor,
                      blurRadius: 12,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : null,
          ),
          child: child,
        ),
      ),
    );
  }
}

class SreaAlertCard extends StatelessWidget {
  final String title;
  final String location;
  final String time;
  final Widget badge;
  final VoidCallback? onTap;
  final IconData? icon;

  const SreaAlertCard({
    super.key,
    required this.title,
    required this.location,
    required this.time,
    required this.badge,
    this.onTap,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final iconSize = (width * 0.08).clamp(24.0, 48.0);
    final iconContainerSize = iconSize + 12;

    return SreaCard(
      onTap: onTap,
      padding: _responsiveCardPadding(context, small: true),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: iconContainerSize,
            height: iconContainerSize,
            decoration: BoxDecoration(
              color: SreaColors.primaryLight,
              borderRadius: SreaRadius.input,
            ),
            child: Icon(
              icon ?? Icons.warning_amber_rounded,
              color: SreaColors.primary,
              size: iconSize * 0.6,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        title,
                        style: SreaText.bodySmall(context).copyWith(
                          fontWeight: FontWeight.w600,
                          color: SreaColors.textPrimary,
                          fontSize: (width * 0.04).clamp(13.0, 18.0),
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 4),
                    badge,
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      size: (width * 0.035).clamp(11.0, 16.0),
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(width: 3),
                    Expanded(
                      child: Text(
                        location,
                        style: SreaText.label(context).copyWith(
                          fontSize: (width * 0.032).clamp(10.0, 14.0),
                          color: SreaColors.textSecondary,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Text(
                      time,
                      style: SreaText.label(context).copyWith(
                        fontSize: (width * 0.032).clamp(10.0, 14.0),
                        color: SreaColors.textHint,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class SreaInfoCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color backgroundColor;
  final Color foregroundColor;
  final VoidCallback? onTap;

  const SreaInfoCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
    this.backgroundColor = SreaColors.surface,
    this.foregroundColor = SreaColors.textPrimary,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final iconSize = (width * 0.07).clamp(24.0, 42.0);

    return SreaCard(
      onTap: onTap,
      color: backgroundColor,
      child: Row(
        children: [
          Icon(icon, color: foregroundColor, size: iconSize),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: SreaText.label(context).copyWith(
                    fontSize: (width * 0.032).clamp(10.0, 14.0),
                    color: foregroundColor.withValues(alpha: 0.8),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: SreaText.bodySmall(context).copyWith(
                    fontSize: (width * 0.04).clamp(13.0, 18.0),
                    color: foregroundColor,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          Icon(
            Icons.chevron_right_rounded,
            size: iconSize * 0.7,
            color: foregroundColor.withValues(alpha: 0.7),
          ),
        ],
      ),
    );
  }
}

class SreaAllClearBanner extends StatelessWidget {
  final String message;

  const SreaAllClearBanner({
    super.key,
    this.message = 'No active alerts in your area.',
  });

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final iconSize = (width * 0.07).clamp(28.0, 48.0);

    return Container(
      padding: _responsiveCardPadding(context, small: true),
      decoration: BoxDecoration(
        color: SreaColors.allClearBg,
        borderRadius: SreaRadius.card,
        border: Border.all(
          color: SreaColors.allClearIcon.withValues(alpha: 0.3),
        ),
      ),
      child: Row(
        children: [
          Container(
            width: iconSize,
            height: iconSize,
            decoration: BoxDecoration(
              color: SreaColors.allClearIcon.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.check_circle_outline_rounded,
              color: SreaColors.allClearIcon,
              size: iconSize * 0.6,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'All Clear',
                  style: SreaText.bodySmall(context).copyWith(
                    fontSize: (width * 0.04).clamp(13.0, 18.0),
                    color: SreaColors.allClearText,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Text(
                  message,
                  style: SreaText.label(context).copyWith(
                    fontSize: (width * 0.032).clamp(10.0, 14.0),
                    color: SreaColors.allClearText.withValues(alpha: 0.8),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
