import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:google_fonts/google_fonts.dart';
import 'incident_list_screen.dart';
import 'profile_screen.dart';
import 'notifications_screen.dart';
import '../services/notification_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  final ResponderNotificationService _notificationService =
      ResponderNotificationService();

  @override
  void initState() {
    super.initState();
    // ✅ Load real notifications instead of mock
    _notificationService.loadNotifications();
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

  @override
  Widget build(BuildContext context) {
    final unreadCount = _notificationService.unreadCount;

    // Decide app bar content based on selected tab
    final appBarTitle = _currentIndex == 0
        ? Row(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              RichText(
                text: TextSpan(
                  style: GoogleFonts.montserrat(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    fontStyle: FontStyle.italic,
                    letterSpacing: 2,
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
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: SreaRadius.pill,
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.3),
                    width: 0.5,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.verified_user_rounded,
                      size: 10,
                      color: Colors.white,
                    ),
                    const SizedBox(width: 3),
                    Text(
                      'RESPONDER',
                      style: SreaText.label(context).copyWith(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.8,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          )
        : Text(
            'Profile',
            style: SreaText.titleLarge(
              context,
            ).copyWith(color: SreaColors.textOnPrimary),
          );

    final appBarActions = _currentIndex == 0
        ? [
            Stack(
              children: [
                IconButton(
                  icon: const Icon(
                    Icons.notifications_none_rounded,
                    color: SreaColors.textOnPrimary,
                  ),
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const NotificationsScreen(),
                      ),
                    );
                  },
                ),
                if (unreadCount > 0)
                  Positioned(
                    right: 4,
                    top: 4,
                    child: Container(
                      padding: const EdgeInsets.all(2),
                      decoration: const BoxDecoration(
                        color: SreaColors.buttonReport,
                        shape: BoxShape.circle,
                      ),
                      constraints: const BoxConstraints(
                        minWidth: 14,
                        minHeight: 14,
                      ),
                      child: Text(
                        unreadCount > 9 ? '9+' : '$unreadCount',
                        style: const TextStyle(
                          fontSize: 9,
                          color: Colors.white,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),
          ]
        : <Widget>[];

    return Scaffold(
      backgroundColor: SreaColors.background,
      appBar: AppBar(
        title: appBarTitle,
        actions: appBarActions,
        backgroundColor: SreaColors.primary,
        elevation: 0,
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: const [IncidentListScreen(), ProfileScreen()],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
        type: BottomNavigationBarType.fixed,
        backgroundColor: SreaColors.primary,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.white70,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.list_alt),
            label: 'Incidents',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}
