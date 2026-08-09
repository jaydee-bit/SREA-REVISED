// File: srea_sidebar.dart
// Path: mobile_user_app/lib/widgets/srea_sidebar.dart
// ANONYMOUS VERSION – No user info, no logout

import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:google_fonts/google_fonts.dart';

class SreaSidebar extends StatelessWidget {
  final String activeRoute;
  final Function(String) onNavigate;

  const SreaSidebar({
    super.key,
    required this.activeRoute,
    required this.onNavigate,
  });

  @override
  Widget build(BuildContext context) {
    return Drawer(
      width: MediaQuery.of(context).size.width * 0.80,
      backgroundColor: SreaColors.primary,
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ─── Header ────────────────────────────────────────────────
            Padding(
              padding: EdgeInsets.fromLTRB(
                SreaSpacing.lg(context),
                SreaSpacing.xl(context),
                SreaSpacing.lg(context),
                SreaSpacing.lg(context),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  RichText(
                    text: TextSpan(
                      style: GoogleFonts.montserrat(
                        fontSize: 28,
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
                  SizedBox(height: SreaSpacing.xs(context)),
                  Text(
                    'Emergency Alert System',
                    style: SreaText.bodySmall(
                      context,
                    ).copyWith(color: SreaColors.bottomNavInactive),
                  ),
                ],
              ),
            ),
            Container(height: 1, color: Colors.white.withOpacity(0.15)),
            SizedBox(height: SreaSpacing.sm(context)),
            // ─── Navigation Items ──────────────────────────────────────
            Expanded(
              child: ListView(
                padding: EdgeInsets.symmetric(
                  horizontal: SreaSpacing.sm(context),
                  vertical: SreaSpacing.sm(context),
                ),
                children: [
                  _SidebarItem(
                    icon: Icons.home_outlined,
                    activeIcon: Icons.home_rounded,
                    label: 'Report Emergency',
                    route: '/report',
                    activeRoute: activeRoute,
                    onTap: onNavigate,
                  ),
                  _SidebarItem(
                    icon: Icons.warning_amber_outlined,
                    activeIcon: Icons.warning_amber_rounded,
                    label: 'Alerts',
                    route: '/alerts',
                    activeRoute: activeRoute,
                    onTap: onNavigate,
                  ),
                  _SidebarItem(
                    icon: Icons.campaign_outlined,
                    activeIcon: Icons.campaign_rounded,
                    label: 'Announcements',
                    route: '/announcements',
                    activeRoute: activeRoute,
                    onTap: onNavigate,
                  ),
                  _SidebarItem(
                    icon: Icons.traffic_outlined,
                    activeIcon: Icons.traffic_rounded,
                    label: 'Traffic Advisories',
                    route: '/traffic',
                    activeRoute: activeRoute,
                    onTap: onNavigate,
                  ),
                  _SidebarItem(
                    icon: Icons.history_outlined,
                    activeIcon: Icons.history_rounded,
                    label: 'My Reports',
                    route: '/my-reports',
                    activeRoute: activeRoute,
                    onTap: onNavigate,
                  ),
                  // ❌ Notifications removed – use bell icon instead
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SidebarItem extends StatelessWidget {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final String route;
  final String activeRoute;
  final void Function(String) onTap;

  const _SidebarItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    required this.route,
    required this.activeRoute,
    required this.onTap,
  });

  bool get _isActive => activeRoute == route;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: SreaSpacing.xs(context)),
      child: Material(
        color: _isActive ? Colors.white.withOpacity(0.15) : Colors.transparent,
        borderRadius: SreaRadius.input,
        child: InkWell(
          onTap: () {
            Navigator.pop(context);
            onTap(route);
          },
          borderRadius: SreaRadius.input,
          splashColor: Colors.white.withOpacity(0.1),
          highlightColor: Colors.white.withOpacity(0.05),
          child: Padding(
            padding: EdgeInsets.symmetric(
              horizontal: SreaSpacing.md(context),
              vertical: SreaSpacing.sm(context),
            ),
            child: Row(
              children: [
                Icon(
                  _isActive ? activeIcon : icon,
                  color: _isActive
                      ? SreaColors.textOnPrimary
                      : SreaColors.bottomNavInactive,
                  size: 22,
                ),
                SizedBox(width: SreaSpacing.avatarGap(context)),
                Text(
                  label,
                  style: SreaText.bodySmall(context).copyWith(
                    color: _isActive
                        ? SreaColors.textOnPrimary
                        : SreaColors.bottomNavInactive,
                    fontWeight: _isActive ? FontWeight.w700 : FontWeight.w400,
                  ),
                ),
                if (_isActive) ...[
                  const Spacer(),
                  Container(
                    width: 4,
                    height: 4,
                    decoration: const BoxDecoration(
                      color: SreaColors.textOnPrimary,
                      shape: BoxShape.circle,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
