// File: srea_button.dart

import 'package:flutter/material.dart';
import '../theme/theme.dart';

enum SreaButtonType { primary, report, update, outline, ghost }
enum SreaButtonSize { small, medium, large }

class SreaButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final SreaButtonType type;
  final SreaButtonSize size;
  final IconData? icon;
  final bool fullWidth;
  final bool isLoading;

  const SreaButton({
    super.key,
    required this.label,
    this.onPressed,
    this.type = SreaButtonType.primary,
    this.size = SreaButtonSize.medium,
    this.icon,
    this.fullWidth = false,
    this.isLoading = false,
  });

  const SreaButton.report({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.fullWidth = false,
    this.isLoading = false,
  })  : type = SreaButtonType.report,
        size = SreaButtonSize.medium;

  const SreaButton.update({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.fullWidth = false,
    this.isLoading = false,
  })  : type = SreaButtonType.update,
        size = SreaButtonSize.medium;

  const SreaButton.outline({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.fullWidth = false,
    this.isLoading = false,
  })  : type = SreaButtonType.outline,
        size = SreaButtonSize.medium;

  Color get _bgColor {
    switch (type) {
      case SreaButtonType.primary:
        return SreaColors.buttonPrimary;
      case SreaButtonType.report:
        return SreaColors.buttonReport;
      case SreaButtonType.update:
        return SreaColors.buttonUpdate;
      case SreaButtonType.outline:
      case SreaButtonType.ghost:
        return Colors.transparent;
    }
  }

  Color get _fgColor {
    switch (type) {
      case SreaButtonType.outline:
        return SreaColors.primary;
      case SreaButtonType.ghost:
        return SreaColors.textSecondary;
      default:
        return SreaColors.textOnPrimary;
    }
  }

  EdgeInsets _getPadding(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    double hPadding;
    switch (size) {
      case SreaButtonSize.small:
        hPadding = width * 0.04;
        break;
      case SreaButtonSize.large:
        hPadding = width * 0.07;
        break;
      case SreaButtonSize.medium:
        hPadding = width * 0.055;
        break;
    }
    hPadding = hPadding.clamp(12.0, 32.0);
    final vPadding = size == SreaButtonSize.small ? 8.0 : 12.0;
    return EdgeInsets.symmetric(horizontal: hPadding, vertical: vPadding);
  }

  double _getFontSize(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    double baseSize;
    switch (size) {
      case SreaButtonSize.small:
        baseSize = 12.0;
        break;
      case SreaButtonSize.large:
        baseSize = 16.0;
        break;
      case SreaButtonSize.medium:
        baseSize = 14.0;
        break;
    }
    final scale = (width / 375).clamp(0.8, 1.2);
    return baseSize * scale;
  }

  double _getIconSize(BuildContext context) => _getFontSize(context) + 2;

  @override
  Widget build(BuildContext context) {
    final isDisabled = onPressed == null && !isLoading;

    final textStyle = SreaText.label(context).copyWith(
      fontSize: _getFontSize(context),
      fontWeight: FontWeight.w700,
      color: isDisabled ? SreaColors.textHint : _fgColor,
    );

    Widget content = isLoading
        ? SizedBox(
            height: _getFontSize(context) + 2,
            width: _getFontSize(context) + 2,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: _fgColor,
            ),
          )
        : Row(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (icon != null) ...[
                Icon(
                  icon,
                  size: _getIconSize(context),
                  color: isDisabled ? SreaColors.textHint : _fgColor,
                ),
                const SizedBox(width: 8),
              ],
              Text(label, style: textStyle),
            ],
          );

    final isOutline = type == SreaButtonType.outline;

    return SizedBox(
      width: fullWidth ? double.infinity : null,
      child: AnimatedOpacity(
        opacity: isDisabled ? 0.5 : 1.0,
        duration: const Duration(milliseconds: 200),
        child: Container(
          decoration: BoxDecoration(
            color: isDisabled ? SreaColors.buttonDisabled : _bgColor,
            borderRadius: SreaRadius.button,
            border: isOutline
                ? Border.all(color: SreaColors.primary, width: 1.5)
                : null,
          ),
          child: Material(
            color: Colors.transparent,
            borderRadius: SreaRadius.button,
            child: InkWell(
              onTap: (isLoading || isDisabled) ? null : onPressed,
              borderRadius: SreaRadius.button,
              focusColor: SreaColors.primaryLight,
              hoverColor: SreaColors.primaryLight.withValues(alpha: 0.1),
              splashColor: Colors.white.withValues(alpha: 0.15),
              highlightColor: Colors.white.withValues(alpha: 0.08),
              child: Padding(
                padding: _getPadding(context),
                child: Center(child: content),
              ),
            ),
          ),
        ),
      ),
    );
  }
}