import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'screens/home_screen.dart';
import 'screens/alert_detail_screen.dart';
import 'services/api_service.dart';
import 'services/notification_service.dart';
import 'widgets/notification_banner.dart';

final navigatorKey = GlobalKey<NavigatorState>();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();

  ApiService().registerDeviceToken();

  // Case 1: app was fully closed, user tapped the notification to open it
  final initialMessage = await FirebaseMessaging.instance.getInitialMessage();

  // Case 2: app is open right now, notification arrives
  FirebaseMessaging.onMessage.listen((message) {
    _addPushToNotificationList(message);
    _showInAppBanner(message);
  });

  // Case 3: app was backgrounded (not closed), user taps the notification
  FirebaseMessaging.onMessageOpenedApp.listen((message) {
    _addPushToNotificationList(message);
    _navigateToAlert(message);
  });

  runApp(const MyApp());

  // Handle case 1 after runApp, so navigatorKey.currentState is ready
  if (initialMessage != null) {
    _addPushToNotificationList(initialMessage);
    _navigateToAlert(initialMessage);
  }
}

void _addPushToNotificationList(RemoteMessage message) {
  final id = message.data['alert_id'] ??
      message.data['advisory_id'] ??
      DateTime.now().millisecondsSinceEpoch.toString();

  final notification = AppNotification(
    id: id,
    type: message.data['type'] ?? 'alert',
    title: message.notification?.title ?? '',
    body: message.notification?.body ?? '',
    timestamp: DateTime.now(),
    payload: message.data,
  );

  NotificationService().addNotification(notification);
}

Future<void> _navigateToAlert(RemoteMessage message) async {
  final alertId = message.data['alert_id'];
  if (alertId == null) return;

  try {
    final alerts = await ApiService().getAlerts();
    final alert = alerts.firstWhere(
      (a) => a['id'].toString() == alertId,
      orElse: () => null,
    );
    if (alert != null) {
      navigatorKey.currentState?.push(
        MaterialPageRoute(builder: (_) => AlertDetailScreen(alert: alert)),
      );
    }
  } catch (e) {
    // Fetch failed — fail quietly
  }
}

OverlayEntry? _bannerEntry;

void _showInAppBanner(RemoteMessage message) {
  final title = message.notification?.title ?? '';
  final body = message.notification?.body ?? '';

  final overlay = navigatorKey.currentState?.overlay;
  if (overlay == null) return;

  _bannerEntry?.remove();
  _bannerEntry = OverlayEntry(
    builder: (context) => Positioned(
      top: 0,
      left: 0,
      right: 0,
      child: NotificationBanner(
        title: title,
        body: body,
        onTap: () async {
          _bannerEntry?.remove();
          _bannerEntry = null;
          await _navigateToAlert(message);
        },
      ),
    ),
  );

  overlay.insert(_bannerEntry!);

  Future.delayed(const Duration(seconds: 5), () {
    _bannerEntry?.remove();
    _bannerEntry = null;
  });
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'SREA - Resident',
      theme: ThemeData(
        fontFamily: 'PlusJakartaSans',
        scaffoldBackgroundColor: SreaColors.background,
        useMaterial3: true,
      ),
      home: const HomeScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}