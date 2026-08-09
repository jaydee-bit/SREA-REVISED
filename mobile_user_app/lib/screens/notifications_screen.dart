// lib/screens/notifications_screen.dart
import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:latlong2/latlong.dart'; // ✅ ADDED
import '../services/api_service.dart';
import '../services/notification_service.dart';
import 'alert_detail_screen.dart';
import 'announcements_screen.dart';
import 'announcement_detail_screen.dart';
import 'traffic_advisories_screen.dart';
import 'traffic_advisory_detail_screen.dart';
import 'incident_report_detail_screen.dart';
import '../models/incident_report_model.dart';

class NotificationItem {
  final String id;
  final String type; // 'alert', 'announcement', 'traffic', 'incident_status'
  final String title;
  final String message;
  final DateTime timestamp;
  final Map<String, dynamic>? rawData;

  NotificationItem({
    required this.id,
    required this.type,
    required this.title,
    required this.message,
    required this.timestamp,
    this.rawData,
  });
}

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<NotificationItem> _notifications = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ApiService();
      final results = await Future.wait([
        api.getAlerts(),
        api.getAnnouncements(),
        api.getTrafficAdvisories(),
      ]);

      final alerts = results[0] as List<dynamic>;
      final announcements = results[1] as List<dynamic>;
      final traffic = results[2] as List<dynamic>;

      final List<NotificationItem> items = [];

      // 1. Alerts (all)
      for (var alert in alerts) {
        items.add(
          NotificationItem(
            id: 'alert_${alert['id']}',
            type: 'alert',
            title: alert['title'] ?? '',
            message: alert['description'] ?? '',
            timestamp: DateTime.parse(alert['created_at']),
            rawData: alert,
          ),
        );
      }

      // 2. Announcements (all)
      for (var ann in announcements) {
        items.add(
          NotificationItem(
            id: 'announcement_${ann['id']}',
            type: 'announcement',
            title: ann['title'] ?? '',
            message: ann['body'] ?? '',
            timestamp: DateTime.parse(
              ann['published_at'] ??
                  ann['created_at'] ??
                  DateTime.now().toIso8601String(),
            ),
            rawData: ann,
          ),
        );
      }

      // 3. Traffic advisories (all)
      for (var adv in traffic) {
        items.add(
          NotificationItem(
            id: 'traffic_${adv['id']}',
            type: 'traffic',
            title: adv['title'] ?? '',
            message: adv['description'] ?? '',
            timestamp: DateTime.parse(adv['created_at']),
            rawData: adv,
          ),
        );
      }

      // 4. Incident status notifications from NotificationService
      final appNotifications = NotificationService().notifications;
      for (var appNotif in appNotifications) {
        if (appNotif.type == 'incident_status') {
          items.add(
            NotificationItem(
              id: appNotif.id,
              type: 'incident_status',
              title: appNotif.title,
              message: appNotif.body,
              timestamp: appNotif.timestamp,
              rawData: appNotif.payload,
            ),
          );
        }
      }

      items.sort((a, b) => b.timestamp.compareTo(a.timestamp));

      // ─── Store latest notification timestamp for red dot ──────────
      final prefs = await SharedPreferences.getInstance();
      if (items.isNotEmpty) {
        final latest = items.first.timestamp;
        await prefs.setString(
          'latest_notification_timestamp',
          latest.toIso8601String(),
        );
      } else {
        await prefs.remove('latest_notification_timestamp');
      }

      // ─── Mark all as read (open = mark as read) ──────────────────
      await prefs.setString(
        'last_notification_view_time',
        DateTime.now().toIso8601String(),
      );

      if (!mounted) return;
      setState(() {
        _notifications = items;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = 'Failed to load notifications. Pull to refresh.';
        _isLoading = false;
      });
    }
  }

  Future<void> _refresh() async => await _loadNotifications();

  void _onNotificationTap(NotificationItem item) async {
    if (item.type == 'alert' && item.rawData != null) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => AlertDetailScreen(alert: item.rawData!),
        ),
      );
    } else if (item.type == 'announcement' && item.rawData != null) {
      final announcement = Announcement(
        id: item.rawData!['id'],
        title: item.rawData!['title'] ?? '',
        body: item.rawData!['body'] ?? '',
        publishedAt: DateTime.parse(
          item.rawData!['published_at'] ??
              item.rawData!['created_at'] ??
              DateTime.now().toIso8601String(),
        ),
        barangay: item.rawData!['barangay'],
        imageUrl: item.rawData!['image_url'],
      );
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => AnnouncementDetailScreen(announcement: announcement),
        ),
      );
    } else if (item.type == 'traffic' && item.rawData != null) {
      final severityMap = {
        'high': SreaBadgeType.high,
        'medium': SreaBadgeType.medium,
        'low': SreaBadgeType.low,
      };
      final severity =
          severityMap[item.rawData!['severity']] ?? SreaBadgeType.low;
      final advisory = TrafficAdvisory(
        id: item.rawData!['id'],
        title: item.rawData!['title'] ?? '',
        description: item.rawData!['description'] ?? '',
        location: item.rawData!['location'] ?? '',
        severity: severity,
        publishedAt: DateTime.parse(
          item.rawData!['created_at'] ?? DateTime.now().toIso8601String(),
        ),
        effectiveFrom: item.rawData!['effective_from'] != null
            ? DateTime.parse(item.rawData!['effective_from'])
            : null,
        effectiveTo: item.rawData!['effective_to'] != null
            ? DateTime.parse(item.rawData!['effective_to'])
            : null,
      );
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => TrafficAdvisoryDetailScreen(advisory: advisory),
        ),
      );
    } else if (item.type == 'incident_status' && item.rawData != null) {
      // ✅ Navigate to incident detail – fetch full incident data
      final incidentId = item.rawData!['incident_id'];
      if (incidentId != null) {
        try {
          final api = ApiService();
          final data = await api.getIncidentById(incidentId);
          final report = IncidentReport.fromJson(data);
          if (mounted) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => IncidentReportDetailScreen(report: report),
              ),
            );
          }
        } catch (e) {
          print('❌ Error fetching incident for notification: $e');
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Could not load incident details.'),
                backgroundColor: SreaColors.error,
              ),
            );
          }
        }
      }
    }
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inDays > 0) return '${diff.inDays} days ago';
    if (diff.inHours > 0) return '${diff.inHours} hours ago';
    if (diff.inMinutes > 0) return '${diff.inMinutes} minutes ago';
    return 'Just now';
  }

  @override
  Widget build(BuildContext context) {
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
          'Notifications',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textOnPrimary),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      _error!,
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: _loadNotifications,
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              )
            : _notifications.isEmpty
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.notifications_none_outlined,
                      size: 64,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No notifications',
                      style: SreaText.bodyLarge(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                  ],
                ),
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _notifications.length,
                itemBuilder: (context, index) {
                  final item = _notifications[index];
                  IconData icon;
                  switch (item.type) {
                    case 'alert':
                      icon = Icons.warning_amber_rounded;
                      break;
                    case 'announcement':
                      icon = Icons.campaign_rounded;
                      break;
                    case 'traffic':
                      icon = Icons.traffic_rounded;
                      break;
                    case 'incident_status':
                      icon = Icons.update_rounded;
                      break;
                    default:
                      icon = Icons.info_outline_rounded;
                  }
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: SreaCard(
                      onTap: () => _onNotificationTap(item),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(icon, size: 22, color: SreaColors.primary),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Text(
                                  item.title,
                                  style: SreaText.bodyLarge(context).copyWith(
                                    fontWeight: FontWeight.w700,
                                    color: SreaColors.textPrimary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            item.message,
                            style: SreaText.bodySmall(context).copyWith(
                              color: SreaColors.textSecondary,
                              height: 1.4,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _formatDate(item.timestamp),
                            style: SreaText.label(
                              context,
                            ).copyWith(color: SreaColors.textHint),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }
}
