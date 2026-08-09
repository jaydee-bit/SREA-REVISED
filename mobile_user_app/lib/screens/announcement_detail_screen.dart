import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'announcements_screen.dart';

class AnnouncementDetailScreen extends StatelessWidget {
  final Announcement announcement;
  const AnnouncementDetailScreen({super.key, required this.announcement});

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

  @override
  Widget build(BuildContext context) {
    final hasImage =
        announcement.imageUrl != null && announcement.imageUrl!.isNotEmpty;
    final hasBarangay =
        announcement.barangay != null && announcement.barangay!.isNotEmpty;

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
          'Announcement',
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
            // ─── Title (no badge – announcements don't have severity) ──
            Text(
              announcement.title,
              style: SreaText.headlineSmall(
                context,
              ).copyWith(fontWeight: FontWeight.w800),
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
                      _formatDateTime(announcement.publishedAt),
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                  ],
                ),
                if (hasBarangay)
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
                        announcement.barangay!,
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

            // ─── Body ──────────────────────────────────────────────────
            Text(
              announcement.body,
              style: SreaText.bodyLarge(
                context,
              ).copyWith(color: SreaColors.textPrimary, height: 1.6),
            ),
            const SizedBox(height: 16),

            // ─── Image (if any) ────────────────────────────────────────
            if (hasImage)
              ClipRRect(
                borderRadius: BorderRadius.circular(SreaRadius.md),
                child: Image.network(
                  announcement.imageUrl!,
                  width: double.infinity,
                  height: 200,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const Icon(
                    Icons.broken_image,
                    size: 100,
                    color: SreaColors.textHint,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
