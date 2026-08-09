// File: srea_radio_option.dart

import 'package:flutter/material.dart';
import '../theme/theme.dart';

class SreaRadioOption<T> extends StatelessWidget {
  final String label;
  final T value;
  final T? groupValue;
  final void Function(T?)? onChanged;
  final String? subtitle;

  const SreaRadioOption({
    super.key,
    required this.label,
    required this.value,
    required this.groupValue,
    required this.onChanged,
    this.subtitle,
  });

  bool get _isSelected => groupValue == value;

  EdgeInsets _getPadding(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final horizontal = (width * 0.04).clamp(12.0, 24.0);
    final vertical = (width * 0.03).clamp(10.0, 18.0);
    return EdgeInsets.symmetric(horizontal: horizontal, vertical: vertical);
  }

  double _getLabelFontSize(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    return (14.0 * (width / 375).clamp(0.85, 1.1)).clamp(12.0, 16.0);
  }

  double _getSubtitleFontSize(BuildContext context) =>
      _getLabelFontSize(context) * 0.85;

  double _getRadioSize(BuildContext context) => _getLabelFontSize(context) * 1.2;

  @override
  Widget build(BuildContext context) {
    final radioSize = _getRadioSize(context);
    return GestureDetector(
      onTap: () => onChanged?.call(value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: _getPadding(context),
        decoration: BoxDecoration(
          color: _isSelected ? SreaColors.primaryLight : SreaColors.surface,
          borderRadius: SreaRadius.input,
          border: Border.all(
            color: _isSelected ? SreaColors.primary : SreaColors.border,
            width: _isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: radioSize,
              height: radioSize,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: _isSelected ? SreaColors.primary : SreaColors.border,
                  width: _isSelected ? 2 : 1.5,
                ),
                color: _isSelected ? SreaColors.primary : SreaColors.surface,
              ),
              child: _isSelected
                  ? Center(
                      child: Icon(
                        Icons.circle,
                        size: radioSize * 0.4,
                        color: SreaColors.textOnPrimary,
                      ),
                    )
                  : null,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: SreaText.bodySmall(context).copyWith(
                      fontSize: _getLabelFontSize(context),
                      color: _isSelected
                          ? SreaColors.primary
                          : SreaColors.textPrimary,
                      fontWeight: _isSelected ? FontWeight.w600 : FontWeight.w400,
                    ),
                  ),
                  if (subtitle != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      subtitle!,
                      style: SreaText.label(context).copyWith(
                        fontSize: _getSubtitleFontSize(context),
                        color: SreaColors.textSecondary,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class SreaRadioItem<T> {
  final String label;
  final T value;
  final String? subtitle;

  const SreaRadioItem({
    required this.label,
    required this.value,
    this.subtitle,
  });
}

class SreaRadioGroup<T> extends StatelessWidget {
  final String? label;
  final T? groupValue;
  final void Function(T?)? onChanged;
  final List<SreaRadioItem<T>> options;

  const SreaRadioGroup({
    super.key,
    this.label,
    required this.groupValue,
    required this.onChanged,
    required this.options,
  });

  @override
  Widget build(BuildContext context) {
    final labelSize = (MediaQuery.of(context).size.width * 0.035).clamp(12.0, 16.0);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (label != null) ...[
          Text(
            label!,
            style: SreaText.bodySmall(context).copyWith(
              fontSize: labelSize,
              color: SreaColors.textSecondary,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 6),
        ],
        ...options.map((option) => Padding(
              padding: EdgeInsets.only(
                bottom: option != options.last ? 8.0 : 0,
              ),
              child: SreaRadioOption<T>(
                label: option.label,
                value: option.value,
                groupValue: groupValue,
                onChanged: onChanged,
                subtitle: option.subtitle,
              ),
            )),
      ],
    );
  }
}