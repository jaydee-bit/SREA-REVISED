// File: login_screen.dart
// Path: mobile_responder_app/lib/screens/auth/login_screen.dart

import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/api_service.dart';
import '../home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;

  late AnimationController _animController;
  late Animation<double> _fadeAnim;
  late Animation<Offset> _slideAnim;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );
    _fadeAnim = CurvedAnimation(parent: _animController, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(
      begin: const Offset(0, 0.06),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animController, curve: Curves.easeOut));
    _animController.forward();
  }

  @override
  void dispose() {
    _animController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);
    try {
      final api = ApiService();
      await api.login(
        _emailController.text.trim(),
        _passwordController.text.trim(),
      );
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const HomeScreen()),
      );
    } catch (e) {
      if (!mounted) return;
      String errorMsg = 'Invalid email or password.';
      if (e.toString().contains('403')) {
        errorMsg = 'This account is not authorized for the responder app.';
      } else if (e.toString().contains('401')) {
        errorMsg = 'Invalid credentials.';
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(errorMsg),
          backgroundColor: SreaColors.error,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: SreaColors.background,
      body: SafeArea(
        child: FadeTransition(
          opacity: _fadeAnim,
          child: SlideTransition(
            position: _slideAnim,
            child: SingleChildScrollView(
              child: Column(
                children: [
                  const _HeaderSection(),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(28, 32, 28, 24),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Responder Login',
                            style: SreaText.headlineSmall(
                              context,
                            ).copyWith(color: SreaColors.textPrimary),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Sign in to manage incidents',
                            style: SreaText.bodySmall(
                              context,
                            ).copyWith(color: SreaColors.textSecondary),
                          ),
                          const SizedBox(height: 28),

                          SreaTextField(
                            label: 'Email',
                            hint: 'responder@sanrafael.gov.ph',
                            controller: _emailController,
                            keyboardType: TextInputType.emailAddress,
                            prefixIcon: Icons.mail_outline_rounded,
                            required: true,
                            validator: (v) {
                              if (v == null || v.isEmpty)
                                return 'Email is required';
                              if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(v)) {
                                return 'Enter a valid email';
                              }
                              return null;
                            },
                          ),
                          SizedBox(height: SreaSpacing.inputGap(context)),

                          SreaPasswordField(
                            label: 'Password',
                            hint: '••••••••',
                            controller: _passwordController,
                            required: true,
                            validator: (v) {
                              if (v == null || v.isEmpty)
                                return 'Password is required';
                              if (v.length < 6) return 'Minimum 6 characters';
                              return null;
                            },
                          ),
                          const SizedBox(height: 10),

                          Align(
                            alignment: Alignment.centerLeft,
                            child: Text(
                              'Accounts are provided by the administrator.',
                              style: SreaText.label(
                                context,
                              ).copyWith(color: SreaColors.textHint),
                            ),
                          ),
                          const SizedBox(height: 28),

                          SreaButton(
                            label: 'Login',
                            onPressed: _handleLogin,
                            fullWidth: true,
                            isLoading: _isLoading,
                            size: SreaButtonSize.large,
                          ),
                          const SizedBox(height: 24),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _HeaderSection extends StatelessWidget {
  const _HeaderSection();

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        ClipPath(
          clipper: _WaveClipper(),
          child: Container(
            height: 260,
            width: double.infinity,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [SreaColors.primaryDark, SreaColors.primary],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
          ),
        ),
        Positioned(
          top: 32,
          child: Column(
            children: [
              RichText(
                text: TextSpan(
                  style: GoogleFonts.montserrat(
                    fontSize: 56,
                    fontWeight: FontWeight.w900,
                    fontStyle: FontStyle.italic,
                    letterSpacing: 4,
                  ),
                  children: const [
                    TextSpan(
                      text: 'SR',
                      style: TextStyle(color: Colors.white),
                    ),
                    TextSpan(
                      text: 'EA',
                      style: TextStyle(color: Color(0xFFFF3B30)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'San Rafael Emergency Alert',
                style: SreaText.label(context).copyWith(
                  color: SreaColors.bottomNavInactive,
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: SreaRadius.pill,
                ),
                child: Text(
                  'RESPONDER PORTAL',
                  style: SreaText.label(context).copyWith(
                    color: SreaColors.textOnPrimary,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1,
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _WaveClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    final path = Path();
    path.lineTo(0, size.height - 50);
    path.quadraticBezierTo(
      size.width * 0.25,
      size.height,
      size.width * 0.5,
      size.height - 28,
    );
    path.quadraticBezierTo(
      size.width * 0.75,
      size.height - 56,
      size.width,
      size.height - 18,
    );
    path.lineTo(size.width, 0);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(CustomClipper<Path> oldClipper) => false;
}
