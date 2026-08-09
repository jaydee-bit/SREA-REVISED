// File: alert_detail_screen.dart
// Path: mobile_user_app/lib/screens/alert_detail_screen.dart

import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';

class AlertDetailScreen extends StatelessWidget {
  final Map<String, dynamic> alert;

  const AlertDetailScreen({super.key, required this.alert});

  String _formatDateTime(String? dateString) {
    if (dateString == null) return '';
    try {
      final date = DateTime.parse(dateString);
      final month = _monthAbbr(date.month);
      final day = date.day;
      final year = date.year;
      int hour = date.hour;
      final minute = date.minute.toString().padLeft(2, '0');
      final amPm = hour >= 12 ? 'PM' : 'AM';
      if (hour > 12) hour -= 12;
      if (hour == 0) hour = 12;
      return '$month $day, $year at $hour:$minute $amPm';
    } catch (e) {
      return '';
    }
  }

  String _monthAbbr(int month) {
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ];
    return months[month - 1];
  }

  Color _getLevelColor(String level) {
    switch (level.toLowerCase()) {
      case 'critical':
      case 'high':
        return SreaColors.critical;
      case 'medium':
        return SreaColors.warning;
      default:
        return SreaColors.low;
    }
  }

  @override
  Widget build(BuildContext context) {
    final level = alert['level']?.toString() ?? 'low';
    final color = _getLevelColor(level);
    final isBarangaySpecific =
        alert['barangay'] != null && alert['barangay'].isNotEmpty;
    final formattedDateTime = _formatDateTime(alert['created_at']);

    return Scaffold(
      backgroundColor: SreaColors.background,
      appBar: AppBar(
        backgroundColor: SreaColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: SreaColors.textOnPrimary,
          ),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Alert Details',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textOnPrimary),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ─── Title + Badge (same row) ──────────────────────────
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Text(
                      alert['title'] ?? '',
                      style: SreaText.headlineSmall(context).copyWith(
                        fontWeight: FontWeight.w800,
                        color: SreaColors.textPrimary,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: color.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: color.withOpacity(0.3)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: color,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          level.toUpperCase(),
                          style: TextStyle(
                            color: color,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // ─── Metadata: Date + Barangay ──────────────────────────
              Wrap(
                spacing: 16,
                runSpacing: 8,
                children: [
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.calendar_today_outlined,
                        size: 16,
                        color: SreaColors.textHint,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        formattedDateTime,
                        style: SreaText.bodySmall(
                          context,
                        ).copyWith(color: SreaColors.textSecondary),
                      ),
                    ],
                  ),
                  if (isBarangaySpecific)
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.location_on_outlined,
                          size: 16,
                          color: SreaColors.textHint,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          alert['barangay'],
                          style: SreaText.bodySmall(
                            context,
                          ).copyWith(color: SreaColors.textSecondary),
                        ),
                      ],
                    ),
                ],
              ),
              const SizedBox(height: 16),

              const Divider(),
              const SizedBox(height: 16),

              // ─── Description ───────────────────────────────────────────
              Text(
                alert['description'] ?? '',
                style: SreaText.bodyLarge(
                  context,
                ).copyWith(color: SreaColors.textPrimary, height: 1.6),
              ),
              const SizedBox(height: 24),

              // ─── MDRRMO contact footer ────────────────────────────────
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: SreaColors.primaryLight,
                  borderRadius: SreaRadius.input,
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.phone_in_talk_rounded,
                      size: 20,
                      color: SreaColors.primary,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'For questions, contact MDRRMO',
                            style: SreaText.bodySmall(context).copyWith(
                              fontWeight: FontWeight.w700,
                              color: SreaColors.primary,
                            ),
                          ),
                          Text(
                            '(044) 123-4567',
                            style: SreaText.label(
                              context,
                            ).copyWith(color: SreaColors.primary),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
