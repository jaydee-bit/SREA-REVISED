// File: notification_service.dart
// Path: mobile_user_app/lib/services/notification_service.dart

import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../screens/notifications_screen.dart';
import '../models/incident_report_model.dart';
import 'api_service.dart';

class AppNotification {
  final String id;
  final String type; // 'alert', 'announcement', 'traffic', 'incident_status'
  final String title;
  final String body;
  final DateTime timestamp;
  final bool isRead;
  final Map<String, dynamic>? payload;

  AppNotification({
    required this.id,
    required this.type,
    required this.title,
    required this.body,
    required this.timestamp,
    this.isRead = false,
    this.payload,
  });
}

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  final List<AppNotification> _notifications = [];
  final List<void Function()> _listeners = [];

  // Track last known status for each incident
  Map<String, String> _lastStatusMap = {};
  static const String _statusMapKey = 'incident_status_map';

  List<AppNotification> get notifications => List.unmodifiable(_notifications);

  void addListener(void Function() listener) {
    _listeners.add(listener);
  }

  void removeListener(void Function() listener) {
    _listeners.remove(listener);
  }

  void _notifyListeners() {
    for (var listener in _listeners) {
      listener();
    }
  }

  // ─── Update latest timestamp for red dot ──────────────────────────
  Future<void> _updateLatestTimestamp(DateTime timestamp) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final currentLatest = prefs.getString('latest_notification_timestamp');
      if (currentLatest == null ||
          timestamp.isAfter(DateTime.parse(currentLatest))) {
        await prefs.setString(
          'latest_notification_timestamp',
          timestamp.toIso8601String(),
        );
      }
    } catch (e) {
      print('❌ Error updating latest timestamp: $e');
    }
  }

  void addNotification(AppNotification notification) {
    // Check if already exists (avoid duplicates)
    final exists = _notifications.any((n) => n.id == notification.id);
    if (!exists) {
      _notifications.insert(0, notification);
      // ✅ Update latest timestamp for red dot
      _updateLatestTimestamp(notification.timestamp);
      _notifyListeners();
    }
  }

  void markAsRead(String id) {
    final index = _notifications.indexWhere((n) => n.id == id);
    if (index != -1 && !_notifications[index].isRead) {
      _notifications[index] = AppNotification(
        id: _notifications[index].id,
        type: _notifications[index].type,
        title: _notifications[index].title,
        body: _notifications[index].body,
        timestamp: _notifications[index].timestamp,
        isRead: true,
        payload: _notifications[index].payload,
      );
      _notifyListeners();
    }
  }

  void markAllAsRead() {
    for (int i = 0; i < _notifications.length; i++) {
      if (!_notifications[i].isRead) {
        _notifications[i] = AppNotification(
          id: _notifications[i].id,
          type: _notifications[i].type,
          title: _notifications[i].title,
          body: _notifications[i].body,
          timestamp: _notifications[i].timestamp,
          isRead: true,
          payload: _notifications[i].payload,
        );
      }
    }
    _notifyListeners();
  }

  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  void clearAll() {
    _notifications.clear();
    _notifyListeners();
  }

  // ─── Map status to user-friendly label ──────────────────────────────
  String _getDisplayStatus(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'responding':
      case 'in_progress':
      case 'active':
        return 'In Progress';
      case 'resolved':
        return 'Resolved';
      case 'rejected':
        return 'Rejected';
      default:
        return status;
    }
  }

  // ─── Load status map from SharedPreferences ────────────────────────
  Future<Map<String, String>> _loadStatusMap() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final json = prefs.getString(_statusMapKey);
      if (json == null || json.isEmpty) return {};
      return Map<String, String>.from(jsonDecode(json));
    } catch (e) {
      return {};
    }
  }

  // ─── Save status map to SharedPreferences ─────────────────────────
  Future<void> _saveStatusMap(Map<String, String> map) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_statusMapKey, jsonEncode(map));
    } catch (e) {
      // ignore
    }
  }

  // ─── Check for incident status changes ────────────────────────────
  Future<void> checkIncidentStatusChanges() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final reportIds = prefs.getStringList('report_ids') ?? [];

      if (reportIds.isEmpty) return;

      // Load last known statuses
      _lastStatusMap = await _loadStatusMap();

      final api = ApiService();
      bool hasChanges = false;

      for (var id in reportIds) {
        try {
          final data = await api.getIncidentById(id);
          final incident = IncidentReport.fromJson(data);
          final currentStatus = incident.status;
          final lastStatus = _lastStatusMap[id];

          // If status changed or first time seeing it
          if (lastStatus == null || lastStatus != currentStatus) {
            // ✅ Use display-friendly status
            final displayStatus = _getDisplayStatus(currentStatus);
            final notification = AppNotification(
              id: 'incident_status_${id}_${DateTime.now().millisecondsSinceEpoch}',
              type: 'incident_status',
              title: 'Incident Status Updated',
              body:
                  'Your report in ${incident.barangay} is now: $displayStatus',
              timestamp: DateTime.now(),
              isRead: false,
              payload: {
                'incident_id': id,
                'status': currentStatus,
                'barangay': incident.barangay,
              },
            );
            addNotification(notification);
            hasChanges = true;

            // Update last status
            _lastStatusMap[id] = currentStatus;
          }
        } catch (e) {
          print('❌ Error checking incident $id: $e');
          continue;
        }
      }

      if (hasChanges) {
        await _saveStatusMap(_lastStatusMap);
      }
    } catch (e) {
      print('❌ Error checking incident statuses: $e');
    }
  }
}
