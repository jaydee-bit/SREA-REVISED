import 'dart:async';
import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../widgets/srea_sidebar.dart';
import '../services/notification_service.dart';
import 'alerts_screen.dart';
import 'traffic_advisories_screen.dart';
import 'report_incident_screen.dart';
import 'my_reports_screen.dart';
import 'about_screen.dart';
import 'notifications_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> with WidgetsBindingObserver {
  String _currentRoute = '/report';
  Timer? _notificationTimer;
  final NotificationService _notificationService = NotificationService();

  final Map<String, Widget> _screens = {
    '/report': const ReportIncidentScreen(),
    '/alerts': const AlertsScreen(),
    '/traffic': const TrafficAdvisoriesScreen(),
    '/my-reports': const MyReportsScreen(),
    '/about': const AboutScreen(),
  };

  static const Map<String, String> _routeTitles = {
    '/report': 'Report Emergency',
    '/alerts': 'Alerts',
    '/traffic': 'Traffic Advisories',
    '/my-reports': 'My Reports',
    '/about': 'About',
  };

  @override
  void initState() {
    super.initState();
    // Observe app lifecycle
    WidgetsBinding.instance.addObserver(this);
    // Listen to notification service updates
    _notificationService.addListener(_onNotificationUpdate);
    // Run once on app launch
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _notificationService.checkIncidentStatusChanges();
    });
    // Start periodic check every 10 seconds
    _startNotificationTimer();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _notificationTimer?.cancel();
    _notificationService.removeListener(_onNotificationUpdate);
    super.dispose();
  }

  void _startNotificationTimer() {
    _notificationTimer?.cancel();
    _notificationTimer = Timer.periodic(const Duration(seconds: 10), (_) async {
      if (mounted) {
        await _notificationService.checkIncidentStatusChanges();
      }
    });
  }

  void _onNotificationUpdate() {
    if (mounted) setState(() {});
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      // App came back to foreground – check immediately
      _notificationService.checkIncidentStatusChanges();
    }
  }

  void _onNavigate(String route) {
    setState(() {
      _currentRoute = route;
    });
  }

  // ─── Emergency Call with Confirmation ──────────────────────────────
  Future<void> _makeEmergencyCall() async {
    const String hotline = '09171234567';

    final bool? confirm = await showDialog<bool>(
      context: context,
      barrierDismissible: true,
      builder: (context) => AlertDialog(
        title: const Text('Emergency Call'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('You are about to call the emergency hotline:'),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: SreaColors.primaryLight,
                borderRadius: SreaRadius.card,
              ),
              child: Text(
                hotline,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: SreaColors.primary,
                ),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Only use this for life-threatening emergencies.',
              style: TextStyle(fontSize: 12, color: SreaColors.textSecondary),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: SreaColors.error,
              foregroundColor: Colors.white,
            ),
            child: const Text('Call Now'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final Uri phoneUri = Uri(scheme: 'tel', path: hotline);
    try {
      await launchUrl(phoneUri);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'Unable to open dialer. Please call $hotline manually.',
            ),
            backgroundColor: SreaColors.error,
          ),
        );
      }
    }
  }

  // ─── Notification helpers ──────────────────────────────────────────
  Future<bool> _hasUnreadNotifications() async {
    final prefs = await SharedPreferences.getInstance();
    final lastView = prefs.getString('last_notification_view_time');
    final latestTimestamp = prefs.getString('latest_notification_timestamp');

    if (latestTimestamp == null) return false;
    if (lastView == null) return true;

    try {
      final latest = DateTime.parse(latestTimestamp);
      final last = DateTime.parse(lastView);
      return latest.isAfter(last);
    } catch (e) {
      return false;
    }
  }

  Future<void> _markNotificationsRead() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
      'last_notification_view_time',
      DateTime.now().toIso8601String(),
    );
  }

  // ─── Build ──────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: SreaSidebar(activeRoute: _currentRoute, onNavigate: _onNavigate),
      appBar: AppBar(
        backgroundColor: SreaColors.primary,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
        title: Text(
          _routeTitles[_currentRoute] ?? '',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textOnPrimary),
        ),
        actions: [
          // ─── Emergency Call ──────────────────────────────────────────
          IconButton(
            icon: const Icon(
              Icons.phone_in_talk_rounded,
              color: SreaColors.textOnPrimary,
            ),
            onPressed: _makeEmergencyCall,
            tooltip: 'Emergency Call',
          ),
          // ─── Notifications Bell with Red Dot ──────────────────────
          FutureBuilder<bool>(
            future: _hasUnreadNotifications(),
            initialData: false,
            builder: (context, snapshot) {
              final hasUnread = snapshot.data ?? false;
              return IconButton(
                icon: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Icon(
                      Icons.notifications_none_outlined,
                      color: SreaColors.textOnPrimary,
                      size: 26,
                    ),
                    if (hasUnread)
                      Positioned(
                        right: -2,
                        top: -2,
                        child: Container(
                          width: 10,
                          height: 10,
                          decoration: const BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                          ),
                        ),
                      ),
                  ],
                ),
                onPressed: () async {
                  await _markNotificationsRead();
                  if (mounted) {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const NotificationsScreen(),
                      ),
                    );
                  }
                },
                tooltip: 'Notifications',
              );
            },
          ),
        ],
      ),
      body: _screens[_currentRoute] ?? const ReportIncidentScreen(),
    );
  }
}
