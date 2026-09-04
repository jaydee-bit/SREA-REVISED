import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:firebase_core/firebase_core.dart';
import 'screens/auth/login_screen.dart';
import 'screens/home_screen.dart';
import 'screens/incident_detail_screen.dart';
import 'models/incident_report_model.dart';
import 'services/api_service.dart';
import 'widgets/notification_banner.dart';

final navigatorKey = GlobalKey<NavigatorState>();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();

  // Case 1: app was fully closed, user tapped the notification to open it
  final initialMessage = await FirebaseMessaging.instance.getInitialMessage();

  // Case 2: app is open right now, notification arrives
  FirebaseMessaging.onMessage.listen((message) {
    _showInAppBanner(message);
  });

  // Case 3: app was backgrounded (not closed), user tapped the notification
  FirebaseMessaging.onMessageOpenedApp.listen((message) {
    _handleNotificationTap(message);
  });

  runApp(const ResponderApp());

  // Handle case 1 after runApp, so navigatorKey.currentState is ready
  if (initialMessage != null) {
    _handleNotificationTap(initialMessage);
  }
}

void _handleNotificationTap(RemoteMessage message) async {
  final incidentId = message.data['incident_id'];
  if (incidentId == null) return;

  try {
    final api = ApiService();
    final json = await api.getIncident(incidentId);
    final incident = IncidentReport.fromJson(json);

    navigatorKey.currentState?.push(
      MaterialPageRoute(
        builder: (_) => IncidentDetailScreen(incident: incident),
      ),
    );
  } catch (e) {
    // Incident may have been deleted, or the fetch failed — fail quietly
    // rather than crash the app on a notification tap.
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
        onTap: () {
          _bannerEntry?.remove();
          _bannerEntry = null;
          _handleNotificationTap(message);
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

class ResponderApp extends StatelessWidget {
  const ResponderApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'SREA Responder',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primaryColor: SreaColors.primary,
        scaffoldBackgroundColor: SreaColors.background,
        fontFamily: 'PlusJakartaSans',
        appBarTheme: const AppBarTheme(
          backgroundColor: SreaColors.primary,
          foregroundColor: SreaColors.textOnPrimary,
          elevation: 0,
        ),
        inputDecorationTheme: InputDecorationTheme(
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(SreaRadius.md),
            borderSide: const BorderSide(color: SreaColors.border),
          ),
          filled: true,
          fillColor: SreaColors.surface,
        ),
      ),
      home: const AuthCheckScreen(),
    );
  }
}

class AuthCheckScreen extends StatefulWidget {
  const AuthCheckScreen({super.key});

  @override
  State<AuthCheckScreen> createState() => _AuthCheckScreenState();
}

class _AuthCheckScreenState extends State<AuthCheckScreen> {
  bool _isChecking = true;
  bool _isAuthenticated = false;

  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    try {
      const storage = FlutterSecureStorage();
      final token = await storage.read(key: 'auth_token');
      if (token == null) {
        setState(() {
          _isAuthenticated = false;
          _isChecking = false;
        });
        return;
      }
      final api = ApiService();
      await api.getUser();
      setState(() {
        _isAuthenticated = true;
        _isChecking = false;
      });
    } catch (e) {
      const storage = FlutterSecureStorage();
      await storage.delete(key: 'auth_token');
      setState(() {
        _isAuthenticated = false;
        _isChecking = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isChecking) {
      return Scaffold(
        backgroundColor: SreaColors.primary,
        body: const Center(
          child: CircularProgressIndicator(color: Colors.white),
        ),
      );
    }
    return _isAuthenticated ? const HomeScreen() : const LoginScreen();
  }
}