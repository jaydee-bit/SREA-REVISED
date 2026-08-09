import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import '../services/notification_service.dart';
import 'incident_detail_screen.dart';
import '../models/incident_report_model.dart';
import '../services/api_service.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final ResponderNotificationService _notificationService =
      ResponderNotificationService();
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
    _notificationService.addListener(_updateUnreadCount);
  }

  @override
  void dispose() {
    _notificationService.removeListener(_updateUnreadCount);
    super.dispose();
  }

  void _updateUnreadCount() {
    if (mounted) setState(() {});
  }

  Future<void> _loadNotifications() async {
    setState(() => _isLoading = true);
    await _notificationService.loadNotifications();
    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _refresh() async => await _loadNotifications();

  void _onNotificationTap(ResponderNotification notification) async {
    // Mark as read
    if (!notification.isRead) {
      _notificationService.markAsRead(notification.id);
      if (mounted) setState(() {});
    }

    // ✅ Use raw UUID from incidentId
    if (notification.incidentId != null &&
        notification.incidentId!.isNotEmpty) {
      try {
        final api = ApiService();
        print('🔍 Fetching incident with UUID: ${notification.incidentId}');
        final data = await api.getIncident(notification.incidentId!);
        final incident = IncidentReport.fromJson(data);
        if (mounted) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => IncidentDetailScreen(incident: incident),
            ),
          );
        }
      } catch (e) {
        print('❌ Error fetching incident: $e');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Could not load incident details.'),
              backgroundColor: SreaColors.error,
              duration: const Duration(seconds: 3),
            ),
          );
        }
      }
    } else {
      // No incident ID – show a message
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('This notification does not link to an incident.'),
            backgroundColor: SreaColors.warning,
            duration: Duration(seconds: 2),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = _notificationService.unreadCount;

    return Scaffold(
      backgroundColor: SreaColors.background,
      appBar: AppBar(
        title: Text(
          'Notifications',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textOnPrimary),
        ),
        backgroundColor: SreaColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: SreaColors.textOnPrimary,
          ),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (unreadCount > 0)
            TextButton(
              onPressed: () {
                _notificationService.markAllAsRead();
                if (mounted) setState(() {});
              },
              child: Text(
                'Mark all read',
                style: SreaText.label(
                  context,
                ).copyWith(color: SreaColors.textOnPrimary),
              ),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : ListenableBuilder(
                listenable: _notificationService,
                builder: (context, child) {
                  final notifications = _notificationService.notifications;
                  if (notifications.isEmpty) {
                    return const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.notifications_none_outlined,
                            size: 64,
                            color: SreaColors.textHint,
                          ),
                          SizedBox(height: 16),
                          Text(
                            'No notifications yet',
                            style: TextStyle(color: SreaColors.textSecondary),
                          ),
                          SizedBox(height: 8),
                          Text(
                            'New incidents will appear here.',
                            style: TextStyle(color: SreaColors.textHint),
                          ),
                        ],
                      ),
                    );
                  }
                  return ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: notifications.length,
                    itemBuilder: (context, index) {
                      final notif = notifications[index];
                      return GestureDetector(
                        onTap: () => _onNotificationTap(notif),
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: notif.isRead
                                ? SreaColors.surface
                                : SreaColors.primaryLight,
                            borderRadius: SreaRadius.card,
                            border: Border.all(
                              color: notif.isRead
                                  ? SreaColors.border
                                  : SreaColors.primary.withOpacity(0.3),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Icon(
                                    Icons.notifications_active_rounded,
                                    size: 18,
                                    color: notif.isRead
                                        ? SreaColors.textHint
                                        : SreaColors.primary,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      notif.title,
                                      style: SreaText.bodyLarge(context)
                                          .copyWith(
                                            fontWeight: FontWeight.w700,
                                            color: notif.isRead
                                                ? SreaColors.textSecondary
                                                : SreaColors.textPrimary,
                                          ),
                                    ),
                                  ),
                                  if (!notif.isRead)
                                    Container(
                                      width: 8,
                                      height: 8,
                                      decoration: const BoxDecoration(
                                        color: SreaColors.primary,
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                notif.body,
                                style: SreaText.bodySmall(context).copyWith(
                                  color: notif.isRead
                                      ? SreaColors.textHint
                                      : SreaColors.textSecondary,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _formatTime(notif.timestamp),
                                style: SreaText.label(
                                  context,
                                ).copyWith(color: SreaColors.textHint),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  );
                },
              ),
      ),
    );
  }

  String _formatTime(DateTime time) {
    final now = DateTime.now();
    final diff = now.difference(time);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inHours < 1) return '${diff.inMinutes} min ago';
    if (diff.inDays < 1) return '${diff.inHours} hours ago';
    return '${diff.inDays} days ago';
  }
}
