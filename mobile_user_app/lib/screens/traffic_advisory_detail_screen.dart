import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'traffic_advisories_screen.dart';

class TrafficAdvisoryDetailScreen extends StatelessWidget {
  final TrafficAdvisory advisory;
  const TrafficAdvisoryDetailScreen({super.key, required this.advisory});

  String _formatDateTime(DateTime date) {
    final month = _monthAbbr(date.month);
    final day = date.day;
    final year = date.year;
    int hour = date.hour;
    final minute = date.minute.toString().padLeft(2, '0');
    final amPm = hour >= 12 ? 'PM' : 'AM';
    if (hour > 12) hour -= 12;
    if (hour == 0) hour = 12;
    return '$month $day, $year at $hour:$minute $amPm';
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

  Color _getSeverityColor(SreaBadgeType severity) {
    switch (severity) {
      case SreaBadgeType.high:
        return SreaColors.critical;
      case SreaBadgeType.medium:
        return SreaColors.warning;
      case SreaBadgeType.low:
        return SreaColors.low;
      default:
        return SreaColors.textSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    final color = _getSeverityColor(advisory.severity);
    final hasLocation = advisory.location.isNotEmpty;
    final hasEffectivePeriod =
        advisory.effectiveFrom != null || advisory.effectiveTo != null;

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
          'Traffic Advisory',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textOnPrimary),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ─── Title + Badge (same row) ──────────────────────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Expanded(
                  child: Text(
                    advisory.title,
                    style: SreaText.headlineSmall(
                      context,
                    ).copyWith(fontWeight: FontWeight.w800),
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
                        advisory.severity.name.toUpperCase(),
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

            // ─── Metadata: Date + Location ──────────────────────────
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
                      _formatDateTime(advisory.publishedAt),
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                  ],
                ),
                if (hasLocation)
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
                        advisory.location,
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

            // ─── Description ──────────────────────────────────────────
            Text(
              'Description',
              style: SreaText.bodyLarge(
                context,
              ).copyWith(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              advisory.description,
              style: SreaText.bodyLarge(
                context,
              ).copyWith(color: SreaColors.textPrimary, height: 1.6),
            ),
            const SizedBox(height: 16),

            // ─── Effective Period (if any) ──────────────────────────────
            if (hasEffectivePeriod) ...[
              Text(
                'Effective Period',
                style: SreaText.bodyLarge(
                  context,
                ).copyWith(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: SreaColors.surfaceVariant,
                  borderRadius: SreaRadius.card,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (advisory.effectiveFrom != null)
                      Row(
                        children: [
                          const Icon(
                            Icons.calendar_today_outlined,
                            size: 14,
                            color: SreaColors.textHint,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'From: ${_formatDateTime(advisory.effectiveFrom!)}',
                            style: SreaText.bodySmall(
                              context,
                            ).copyWith(color: SreaColors.textSecondary),
                          ),
                        ],
                      ),
                    if (advisory.effectiveTo != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(
                            Icons.calendar_today_outlined,
                            size: 14,
                            color: SreaColors.textHint,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'To: ${_formatDateTime(advisory.effectiveTo!)}',
                            style: SreaText.bodySmall(
                              context,
                            ).copyWith(color: SreaColors.textSecondary),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
