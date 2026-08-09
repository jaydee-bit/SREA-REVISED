// File: srea_image_upload.dart

import 'dart:io';
import 'package:flutter/material.dart';
import '../theme/theme.dart';

class SreaImageUpload extends StatelessWidget {
  final String? label;
  final File? selectedImage;
  final VoidCallback onTap;
  final VoidCallback? onRemove;
  final String hint;
  final double? height;

  const SreaImageUpload({
    super.key,
    this.label,
    this.selectedImage,
    required this.onTap,
    this.onRemove,
    this.hint = 'Tap to upload',
    this.height,
  });

  double _getHeight(BuildContext context) {
    if (height != null) return height!;
    final width = MediaQuery.of(context).size.width;
    return (width * 0.35).clamp(120.0, 200.0);
  }

  @override
  Widget build(BuildContext context) {
    final h = _getHeight(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (label != null) ...[
          Text(
            label!,
            style: SreaText.bodySmall(context).copyWith(
              color: SreaColors.textSecondary,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 6),
        ],
        GestureDetector(
          onTap: onTap,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            height: h,
            width: double.infinity,
            decoration: BoxDecoration(
              color: selectedImage != null
                  ? SreaColors.surface
                  : SreaColors.surfaceVariant,
              borderRadius: SreaRadius.input,
              border: Border.all(
                color: selectedImage != null
                    ? SreaColors.borderFocused
                    : SreaColors.border,
                width: 1.5,
              ),
            ),
            child: selectedImage != null
                ? _SelectedImagePreview(
                    file: selectedImage!,
                    onRemove: onRemove,
                  )
                : _UploadPlaceholder(hint: hint, height: h),
          ),
        ),
      ],
    );
  }
}

class _UploadPlaceholder extends StatelessWidget {
  final String hint;
  final double height;

  const _UploadPlaceholder({required this.hint, required this.height});

  @override
  Widget build(BuildContext context) {
    final iconSize = (height * 0.25).clamp(30.0, 60.0);
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          width: iconSize,
          height: iconSize,
          decoration: BoxDecoration(
            color: SreaColors.primaryLight,
            borderRadius: SreaRadius.pill,
          ),
          child: Icon(
            Icons.add_photo_alternate_outlined,
            color: SreaColors.primary,
            size: iconSize * 0.5,
          ),
        ),
        SizedBox(height: height * 0.08),
        Text(
          hint,
          style: SreaText.bodySmall(context).copyWith(
            fontSize: (height * 0.06).clamp(12.0, 16.0),
            color: SreaColors.textSecondary,
            fontWeight: FontWeight.w500,
          ),
        ),
        SizedBox(height: height * 0.04),
        Text(
          'JPG, PNG up to 5MB',
          style: SreaText.label(context).copyWith(
            fontSize: (height * 0.045).clamp(9.0, 12.0),
            color: SreaColors.textHint,
          ),
        ),
      ],
    );
  }
}

class _SelectedImagePreview extends StatelessWidget {
  final File file;
  final VoidCallback? onRemove;

  const _SelectedImagePreview({required this.file, this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Stack(
      fit: StackFit.expand,
      children: [
        ClipRRect(
          borderRadius: SreaRadius.input,
          child: Image.file(
            file,
            fit: BoxFit.cover,
          ),
        ),
        if (onRemove != null)
          Positioned(
            top: 8,
            right: 8,
            child: GestureDetector(
              onTap: onRemove,
              child: Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: SreaColors.error,
                  borderRadius: SreaRadius.pill,
                ),
                child: const Icon(
                  Icons.close_rounded,
                  size: 16,
                  color: Colors.white,
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class SreaDashedBorder extends StatelessWidget {
  final Widget child;
  final Color color;
  final double strokeWidth;
  final double gap;
  final BorderRadius borderRadius;

  const SreaDashedBorder({
    super.key,
    required this.child,
    this.color = SreaColors.border,
    this.strokeWidth = 1.5,
    this.gap = 6,
    this.borderRadius = const BorderRadius.all(Radius.circular(8)),
  });

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: _DashedBorderPainter(
        color: color,
        strokeWidth: strokeWidth,
        gap: gap,
        borderRadius: borderRadius,
      ),
      child: child,
    );
  }
}

class _DashedBorderPainter extends CustomPainter {
  final Color color;
  final double strokeWidth;
  final double gap;
  final BorderRadius borderRadius;

  _DashedBorderPainter({
    required this.color,
    required this.strokeWidth,
    required this.gap,
    required this.borderRadius,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = strokeWidth
      ..style = PaintingStyle.stroke;

    final rrect = borderRadius.toRRect(Offset.zero & size);
    final path = Path()..addRRect(rrect);
    final dashPath = _createDashedPath(path, 6, gap);
    canvas.drawPath(dashPath, paint);
  }

  Path _createDashedPath(Path source, double dashLength, double dashGap) {
    final dest = Path();
    for (final metric in source.computeMetrics()) {
      double distance = 0;
      bool draw = true;
      while (distance < metric.length) {
        final len = draw ? dashLength : dashGap;
        if (draw) {
          dest.addPath(
            metric.extractPath(distance, distance + len),
            Offset.zero,
          );
        }
        distance += len;
        draw = !draw;
      }
    }
    return dest;
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}