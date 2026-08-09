import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class ResponderNotification {
  final String id;
  final String title;
  final String body;
  final DateTime timestamp;
  bool isRead;
  final String? incidentId;

  ResponderNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.timestamp,
    this.isRead = false,
    this.incidentId,
  });
}

class ResponderNotificationService extends ChangeNotifier {
  static final ResponderNotificationService _instance =
      ResponderNotificationService._internal();
  factory ResponderNotificationService() => _instance;
  ResponderNotificationService._internal();

  List<ResponderNotification> _notifications = [];
  List<String> _readNotificationIds = [];
  static const String _readIdsKey = 'read_notification_ids';

  List<ResponderNotification> get notifications => _notifications;
  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  // ─── Load read IDs from SharedPreferences ──────────────────────────
  Future<void> _loadReadIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final ids = prefs.getStringList(_readIdsKey) ?? [];
      _readNotificationIds = ids;
    } catch (e) {
      print('❌ Error loading read IDs: $e');
      _readNotificationIds = [];
    }
  }

  // ─── Save read IDs to SharedPreferences ──────────────────────────
  Future<void> _saveReadIds() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList(_readIdsKey, _readNotificationIds);
    } catch (e) {
      print('❌ Error saving read IDs: $e');
    }
  }

  // ─── Load real notifications from API ──────────────────────────────
  Future<void> loadNotifications() async {
    try {
      await _loadReadIds();

      final api = ApiService();
      final incidents = await api.getIncidents();

      final List<ResponderNotification> notifications = [];

      for (var incident in incidents) {
        final reportedAt = DateTime.parse(incident['reported_at']);
        final sevenDaysAgo = DateTime.now().subtract(const Duration(days: 7));
        if (reportedAt.isBefore(sevenDaysAgo)) continue;

        // ✅ Use UUID from 'uuid' field, fallback to 'id'
        final rawId = incident['uuid']?.toString() ?? incident['id'].toString();
        final isRead = _readNotificationIds.contains(rawId);

        notifications.add(
          ResponderNotification(
            id: 'incident_$rawId',
            title: 'New ${incident['type'] ?? 'Emergency'} Report',
            body:
                '${incident['barangay'] ?? 'Unknown location'} - ${incident['description'] ?? 'No description'}',
            timestamp: DateTime.parse(incident['reported_at']),
            isRead: isRead,
            incidentId: rawId, // ✅ Raw UUID
          ),
        );
      }

      notifications.sort((a, b) => b.timestamp.compareTo(a.timestamp));

      _notifications = notifications;
      notifyListeners();
    } catch (e) {
      print('❌ Error loading notifications: $e');
      _notifications = [];
      notifyListeners();
    }
  }

  void markAsRead(String notificationId) {
    final index = _notifications.indexWhere((n) => n.id == notificationId);
    if (index != -1 && !_notifications[index].isRead) {
      _notifications[index].isRead = true;
      final incidentId = _notifications[index].incidentId;
      if (incidentId != null && !_readNotificationIds.contains(incidentId)) {
        _readNotificationIds.add(incidentId);
        _saveReadIds();
      }
      notifyListeners();
    }
  }

  void markAllAsRead() {
    bool changed = false;
    for (var n in _notifications) {
      if (!n.isRead) {
        n.isRead = true;
        if (n.incidentId != null &&
            !_readNotificationIds.contains(n.incidentId)) {
          _readNotificationIds.add(n.incidentId!);
          changed = true;
        }
      }
    }
    if (changed) {
      _saveReadIds();
    }
    notifyListeners();
  }

  void addNotification(ResponderNotification notification) {
    _notifications.insert(0, notification);
    notifyListeners();
  }

  Future<void> refresh() async => await loadNotifications();
}
