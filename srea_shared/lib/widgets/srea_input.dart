// File: srea_input.dart
// Path: srea_shared/lib/widgets/srea_input.dart

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../theme/theme.dart';

EdgeInsets _responsiveInputPadding(BuildContext context) {
  final width = MediaQuery.of(context).size.width;
  final horizontal = (width * 0.045).clamp(12.0, 24.0);
  final vertical = (width * 0.035).clamp(12.0, 18.0);
  return EdgeInsets.symmetric(horizontal: horizontal, vertical: vertical);
}

double _responsiveInputFontSize(BuildContext context) {
  final width = MediaQuery.of(context).size.width;
  return (14.0 * (width / 375).clamp(0.85, 1.1)).clamp(12.0, 16.0);
}

double _responsiveLabelFontSize(BuildContext context) {
  final width = MediaQuery.of(context).size.width;
  return (13.0 * (width / 375).clamp(0.85, 1.1)).clamp(11.0, 15.0);
}

InputDecoration _sreaInputDecoration({
  required BuildContext context,
  required String hint,
  IconData? prefixIcon,
  Widget? suffix,
}) {
  return InputDecoration(
    hintText: hint,
    hintStyle: SreaText.bodySmall(context).copyWith(
      fontSize: _responsiveInputFontSize(context),
      color: SreaColors.textHint,
    ),
    prefixIcon: prefixIcon != null
        ? Icon(prefixIcon, size: 18, color: SreaColors.textHint)
        : null,
    suffixIcon: suffix,
    contentPadding: _responsiveInputPadding(context),
    filled: true,
    fillColor: SreaColors.surface,
    enabledBorder: OutlineInputBorder(
      borderRadius: SreaRadius.input,
      borderSide: const BorderSide(color: SreaColors.border, width: 1),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: SreaRadius.input,
      borderSide: const BorderSide(color: SreaColors.borderFocused, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: SreaRadius.input,
      borderSide: const BorderSide(color: SreaColors.error, width: 1),
    ),
    focusedErrorBorder: OutlineInputBorder(
      borderRadius: SreaRadius.input,
      borderSide: const BorderSide(color: SreaColors.error, width: 1.5),
    ),
    disabledBorder: OutlineInputBorder(
      borderRadius: SreaRadius.input,
      borderSide: BorderSide(
        color: SreaColors.border.withValues(alpha: 0.5),
        width: 1,
      ),
    ),
  );
}

class SreaInputLabel extends StatelessWidget {
  final String label;
  final bool required;
  const SreaInputLabel({super.key, required this.label, this.required = false});

  @override
  Widget build(BuildContext context) {
    final labelSize = _responsiveLabelFontSize(context);
    return RichText(
      text: TextSpan(
        text: label,
        style: SreaText.bodySmall(context).copyWith(
          fontSize: labelSize,
          color: SreaColors.textSecondary,
          fontWeight: FontWeight.w600,
        ),
        children: required
            ? [
                TextSpan(
                  text: ' *',
                  style: SreaText.bodySmall(
                    context,
                  ).copyWith(fontSize: labelSize, color: SreaColors.error),
                ),
              ]
            : [],
      ),
    );
  }
}

class SreaTextField extends StatelessWidget {
  final String hint;
  final String? label;
  final TextEditingController? controller;
  final TextInputType keyboardType;
  final String? Function(String?)? validator;
  final void Function(String)? onChanged;
  final List<TextInputFormatter>? inputFormatters;
  final bool enabled;
  final IconData? prefixIcon;
  final int? maxLines;
  final bool required;

  const SreaTextField({
    super.key,
    required this.hint,
    this.label,
    this.controller,
    this.keyboardType = TextInputType.text,
    this.validator,
    this.onChanged,
    this.inputFormatters,
    this.enabled = true,
    this.prefixIcon,
    this.maxLines = 1,
    this.required = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (label != null) ...[
          SreaInputLabel(label: label!, required: required),
          const SizedBox(height: 6),
        ],
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          validator: validator,
          onChanged: onChanged,
          inputFormatters: inputFormatters,
          enabled: enabled,
          maxLines: maxLines,
          style: SreaText.bodySmall(context).copyWith(
            fontSize: _responsiveInputFontSize(context),
            color: SreaColors.textPrimary,
          ),
          decoration: _sreaInputDecoration(
            context: context,
            hint: hint,
            prefixIcon: prefixIcon,
          ),
        ),
      ],
    );
  }
}

class SreaPasswordField extends StatefulWidget {
  final String hint;
  final String? label;
  final TextEditingController? controller;
  final String? Function(String?)? validator;
  final void Function(String)? onChanged;
  final bool required;

  const SreaPasswordField({
    super.key,
    required this.hint,
    this.label,
    this.controller,
    this.validator,
    this.onChanged,
    this.required = false,
  });

  @override
  State<SreaPasswordField> createState() => _SreaPasswordFieldState();
}

class _SreaPasswordFieldState extends State<SreaPasswordField> {
  bool _obscure = true;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (widget.label != null) ...[
          SreaInputLabel(label: widget.label!, required: widget.required),
          const SizedBox(height: 6),
        ],
        TextFormField(
          controller: widget.controller,
          obscureText: _obscure,
          validator: widget.validator,
          onChanged: widget.onChanged,
          style: SreaText.bodySmall(context).copyWith(
            fontSize: _responsiveInputFontSize(context),
            color: SreaColors.textPrimary,
          ),
          decoration: _sreaInputDecoration(
            context: context,
            hint: widget.hint,
            prefixIcon: Icons.lock_outline_rounded,
            suffix: IconButton(
              icon: Icon(
                _obscure
                    ? Icons.visibility_off_outlined
                    : Icons.visibility_outlined,
                size: 20,
                color: SreaColors.textHint,
              ),
              onPressed: () => setState(() => _obscure = !_obscure),
            ),
          ),
        ),
      ],
    );
  }
}

class SreaDropdown<T> extends StatelessWidget {
  final String hint;
  final String? label;
  final T? value;
  final List<T> items;
  final String Function(T)? itemLabel;
  final void Function(T?)? onChanged;
  final String? Function(T?)? validator;
  final bool required;

  const SreaDropdown({
    super.key,
    required this.hint,
    required this.items,
    this.label,
    this.value,
    this.itemLabel,
    this.onChanged,
    this.validator,
    this.required = false,
  });

  @override
  Widget build(BuildContext context) {
    // Ensure value is either null or exists in items to avoid assertion error
    final isValidValue = value == null || items.contains(value);
    final effectiveValue = isValidValue ? value : null;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (label != null) ...[
          SreaInputLabel(label: label!, required: required),
          const SizedBox(height: 6),
        ],
        // DropdownButtonFormField requires a Material ancestor.
        // Wrapping here makes SreaDropdown self-contained regardless of context.
        Material(
          color: Colors.transparent,
          child: DropdownButtonFormField<T>(
            value: effectiveValue,
            validator: validator,
            onChanged: onChanged,
            isExpanded: true,
            menuMaxHeight: 300,
            style: SreaText.bodySmall(context).copyWith(
              fontSize: _responsiveInputFontSize(context),
              color: SreaColors.textPrimary,
            ),
            icon: const Icon(
              Icons.keyboard_arrow_down_rounded,
              color: SreaColors.textHint,
            ),
            decoration: _sreaInputDecoration(context: context, hint: hint),
            items: items.map((item) {
              final label = itemLabel != null
                  ? itemLabel!(item)
                  : item.toString();
              return DropdownMenuItem<T>(
                value: item,
                child: Container(
                  constraints: BoxConstraints(
                    maxWidth: MediaQuery.of(context).size.width - 40,
                  ),
                  child: Text(
                    label,
                    overflow: TextOverflow.ellipsis,
                    style: SreaText.bodySmall(context).copyWith(
                      fontSize: _responsiveInputFontSize(context),
                      color: SreaColors.textPrimary,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }
}
