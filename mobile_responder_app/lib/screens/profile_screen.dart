import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import '../services/api_service.dart';
import 'auth/login_screen.dart';
import 'incident_list_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  late final ApiService _api;
  bool _isLoading = true;
  String? _error;

  String _responderName = '';
  String _responderEmail = '';
  String _responderRole = '';
  String _responderBadge = '';
  int _incidentsHandled = 0;
  int _activeIncidents = 0;
  bool _isAdmin = false;

  @override
  void initState() {
    super.initState();
    _api = ApiService();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final userData = await _api.getUser();
      if (!mounted) return;

      setState(() {
        _responderName = userData['name'] ?? 'Unknown Responder';
        _responderEmail = userData['email'] ?? '';
        _responderRole = userData['role'] ?? 'Emergency Responder';
        _isAdmin = userData['role'] == 'admin';
        final isVerified = userData['is_verified'] == true;
        _responderBadge = isVerified ? 'Verified Responder' : 'Responder';

        _incidentsHandled = (userData['incidents_handled'] ?? 0).toInt();
        _activeIncidents = (userData['active_incidents'] ?? 0).toInt();

        _isLoading = false;
      });
    } catch (e) {
      print('❌ Profile load error: $e');
      if (!mounted) return;
      setState(() {
        _error = 'Failed to load profile. Pull down to refresh.';
        _isLoading = false;
      });
    }
  }

  Future<void> _refresh() => _loadProfile();

  void _confirmLogout(BuildContext context) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: SreaRadius.modal),
        title: Text(
          'Logout',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textPrimary),
        ),
        content: Text(
          'Are you sure you want to logout?',
          style: SreaText.bodySmall(
            context,
          ).copyWith(color: SreaColors.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: Text(
              'Cancel',
              style: SreaText.bodySmall(
                context,
              ).copyWith(color: SreaColors.textSecondary),
            ),
          ),
          TextButton(
            onPressed: () async {
              Navigator.pop(dialogContext);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Logging out...'),
                  duration: Duration(seconds: 1),
                ),
              );
              await _api.logout();
              if (!mounted) return;
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (route) => false,
              );
            },
            child: Text(
              'Logout',
              style: SreaText.bodySmall(
                context,
              ).copyWith(color: SreaColors.error, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return RefreshIndicator(
        onRefresh: _refresh,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(32),
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
                    onPressed: _loadProfile,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _refresh,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          children: [
            // Header
            Container(
              width: double.infinity,
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [SreaColors.primaryDark, SreaColors.primary],
                ),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
              ),
              padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
              child: Column(
                children: [
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      color: SreaColors.surface,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.1),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Icon(
                      _isAdmin
                          ? Icons.admin_panel_settings_rounded
                          : Icons.verified_user_rounded,
                      size: 56,
                      color: SreaColors.primary,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    _responderName,
                    style: SreaText.headlineSmall(context).copyWith(
                      color: SreaColors.textOnPrimary,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _responderEmail,
                    style: SreaText.bodySmall(
                      context,
                    ).copyWith(color: SreaColors.bottomNavInactive),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: _isAdmin ? SreaColors.highBg : SreaColors.lowBg,
                      borderRadius: SreaRadius.pill,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          _isAdmin
                              ? Icons.admin_panel_settings_rounded
                              : Icons.verified_rounded,
                          size: 14,
                          color: _isAdmin ? SreaColors.high : SreaColors.low,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _isAdmin ? 'Administrator' : _responderBadge,
                          style: SreaText.label(context).copyWith(
                            color: _isAdmin ? SreaColors.high : SreaColors.low,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => Scaffold(
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
                                  'Resolved Incidents',
                                  style: SreaText.titleLarge(
                                    context,
                                  ).copyWith(color: SreaColors.textOnPrimary),
                                ),
                              ),
                              body: const IncidentListScreen(
                                initialFilter: 'resolved',
                                assignedToMe: true,
                              ),
                            ),
                          ),
                        );
                      },
                      child: _StatCard(
                        title: 'Resolved',
                        value: _incidentsHandled.toString(),
                        icon: Icons.check_circle_outline_rounded,
                        color: SreaColors.buttonUpdate,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => Scaffold(
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
                                  'Assigned Incidents',
                                  style: SreaText.titleLarge(
                                    context,
                                  ).copyWith(color: SreaColors.textOnPrimary),
                                ),
                              ),
                              body: const IncidentListScreen(
                                initialFilter: 'active',
                                assignedToMe: true,
                              ),
                            ),
                          ),
                        );
                      },
                      child: _StatCard(
                        title: 'Assigned',
                        value: _activeIncidents.toString(),
                        icon: Icons.pending_actions_rounded,
                        color: SreaColors.medium,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: SreaCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Role Information',
                      style: SreaText.bodyLarge(context).copyWith(
                        fontWeight: FontWeight.w700,
                        color: SreaColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: SreaColors.primaryLight,
                            borderRadius: SreaRadius.input,
                          ),
                          child: Icon(
                            _isAdmin
                                ? Icons.admin_panel_settings_rounded
                                : Icons.shield_outlined,
                            size: 20,
                            color: SreaColors.primary,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _isAdmin ? 'Administrator' : 'Responder Role',
                                style: SreaText.label(
                                  context,
                                ).copyWith(color: SreaColors.textSecondary),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                _responderRole,
                                style: SreaText.bodySmall(context).copyWith(
                                  color: SreaColors.textPrimary,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: SreaButton.report(
                label: 'Logout',
                onPressed: () => _confirmLogout(context),
                fullWidth: true,
                icon: Icons.logout_rounded,
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;
  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: SreaColors.surface,
        borderRadius: SreaRadius.card,
        boxShadow: [
          BoxShadow(
            color: SreaColors.shadowColor,
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(icon, size: 32, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: SreaText.headlineSmall(context).copyWith(
              color: SreaColors.textPrimary,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: SreaText.label(
              context,
            ).copyWith(color: SreaColors.textSecondary),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
