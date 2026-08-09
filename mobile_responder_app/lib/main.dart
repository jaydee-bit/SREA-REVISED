import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:srea_shared/srea_shared.dart';
import 'screens/auth/login_screen.dart';
import 'screens/home_screen.dart';
import 'services/api_service.dart';

void main() {
  runApp(const ResponderApp());
}

class ResponderApp extends StatelessWidget {
  const ResponderApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
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
