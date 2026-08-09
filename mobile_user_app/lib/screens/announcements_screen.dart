import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import '../services/api_service.dart';
import 'announcement_detail_screen.dart';

class Announcement {
  final dynamic id;
  final String title;
  final String body;
  final DateTime publishedAt;
  final String? barangay;
  final String? imageUrl;

  Announcement({
    required this.id,
    required this.title,
    required this.body,
    required this.publishedAt,
    this.barangay,
    this.imageUrl,
  });
}

class AnnouncementsScreen extends StatefulWidget {
  const AnnouncementsScreen({super.key});

  @override
  State<AnnouncementsScreen> createState() => _AnnouncementsScreenState();
}

class _AnnouncementsScreenState extends State<AnnouncementsScreen> {
  List<Announcement> _announcements = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAnnouncements();
  }

  Future<void> _loadAnnouncements() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ApiService();
      final data = await api.getAnnouncements();
      final List<Announcement> list = data.map((json) {
        return Announcement(
          id: json['id'],
          title: json['title'] ?? '',
          body: json['body'] ?? '',
          publishedAt: DateTime.parse(
            json['published_at'] ??
                json['created_at'] ??
                DateTime.now().toIso8601String(),
          ),
          barangay: json['barangay'],
          imageUrl: json['image_url'],
        );
      }).toList();
      if (mounted) {
        setState(() {
          _announcements = list;
          _isLoading = false;
        });
      }
    } catch (e) {
      print('❌ Error loading announcements: $e');
      if (mounted) {
        setState(() {
          _error = 'Failed to load announcements. Pull to refresh.';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _refresh() async => _loadAnnouncements();

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const ColoredBox(
        color: SreaColors.background,
        child: Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null) {
      return ColoredBox(
        color: SreaColors.background,
        child: RefreshIndicator(
          onRefresh: _refresh,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.error_outline,
                      size: 48,
                      color: SreaColors.error,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      _error!,
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: _loadAnnouncements,
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    if (_announcements.isEmpty) {
      return ColoredBox(
        color: SreaColors.background,
        child: RefreshIndicator(
          onRefresh: _refresh,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.announcement,
                      size: 64,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No announcements',
                      style: SreaText.bodyLarge(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    return ColoredBox(
      color: SreaColors.background,
      child: RefreshIndicator(
        onRefresh: _refresh,
        child: Column(
          children: [
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
                itemCount: _announcements.length,
                itemBuilder: (context, index) {
                  final ann = _announcements[index];
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(
                      borderRadius: SreaRadius.card,
                      // ✅ removed left border
                    ),
                    child: SreaCard(
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) =>
                              AnnouncementDetailScreen(announcement: ann),
                        ),
                      ),
                      child: Row(
                        children: [
                          // ✅ Colored icon circle (replaces left border)
                          Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: SreaColors.primary.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              Icons.announcement_outlined,
                              color: SreaColors.primary,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  ann.title,
                                  style: SreaText.bodyLarge(
                                    context,
                                  ).copyWith(fontWeight: FontWeight.w700),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  ann.body,
                                  style: SreaText.bodySmall(context).copyWith(
                                    color: SreaColors.textSecondary,
                                    height: 1.4,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Icon(
                                      Icons.calendar_today_outlined,
                                      size: 12,
                                      color: SreaColors.textHint,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      _formatDate(ann.publishedAt),
                                      style: SreaText.label(
                                        context,
                                      ).copyWith(color: SreaColors.textHint),
                                    ),
                                    if (ann.barangay != null &&
                                        ann.barangay!.isNotEmpty) ...[
                                      const SizedBox(width: 12),
                                      Icon(
                                        Icons.location_on_outlined,
                                        size: 12,
                                        color: SreaColors.textHint,
                                      ),
                                      const SizedBox(width: 4),
                                      Expanded(
                                        child: Text(
                                          ann.barangay!,
                                          style: SreaText.label(context)
                                              .copyWith(
                                                color: SreaColors.textHint,
                                                fontSize: 11,
                                              ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inDays > 0) return '${diff.inDays} days ago';
    if (diff.inHours > 0) return '${diff.inHours} hours ago';
    if (diff.inMinutes > 0) return '${diff.inMinutes} minutes ago';
    return 'Just now';
  }
}
