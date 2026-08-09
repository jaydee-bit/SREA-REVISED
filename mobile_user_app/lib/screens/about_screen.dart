// File: about_screen.dart

import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: SreaColors.background,
      body: SafeArea(
        child: Column(
          children: [
            _AboutAppBar(),
            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  children: [
                    _HeroHeader(),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 24, 20, 40),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _AboutSection(
                            icon: Icons.info_outline_rounded,
                            iconColor: SreaColors.primary,
                            title: 'What is SREA?',
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'The San Rafael Emergency Alert (SREA) System is designed to provide a fast, reliable, and efficient way of reporting and responding to emergencies. Our system improves communication between residents, administrators, and emergency responders by utilizing modern mobile and web technologies.',
                                  style: SreaText.bodySmall(context).copyWith(
                                    color: SreaColors.textPrimary,
                                    height: 1.7,
                                  ),
                                ),
                                SizedBox(height: SreaSpacing.sm(context)),
                                Text(
                                  'SREA allows users to send real-time emergency alerts along with their location, ensuring that help can be dispatched quickly and accurately.',
                                  style: SreaText.bodySmall(context).copyWith(
                                    color: SreaColors.textPrimary,
                                    height: 1.7,
                                  ),
                                ),
                                SizedBox(height: SreaSpacing.sm(context)),
                                Text(
                                  'To enhance public safety, reduce emergency response time, and support better decision-making during critical situations — creating a safer and more connected community through technology.',
                                  style: SreaText.bodySmall(context).copyWith(
                                    color: SreaColors.textPrimary,
                                    height: 1.7,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          SizedBox(height: SreaSpacing.lg(context)),
                          _AboutSection(
                            icon: Icons.star_outline_rounded,
                            iconColor: SreaColors.primary,
                            title: 'Key Features',
                            child: Column(
                              children: const [
                                _FeatureItem(
                                  icon: Icons.location_on_outlined,
                                  iconColor: SreaColors.primary,
                                  title: 'Real-time Location Alerts',
                                  description: 'Send emergency alerts with your GPS location for faster dispatch.',
                                ),
                                SizedBox(height: 10),
                                _FeatureItem(
                                  icon: Icons.monitor_heart_outlined,
                                  iconColor: Color(0xFF34C759),
                                  title: 'Incident Monitoring',
                                  description: 'Admins can monitor, verify, and coordinate responses in real time.',
                                ),
                                SizedBox(height: 10),
                                _FeatureItem(
                                  icon: Icons.campaign_outlined,
                                  iconColor: Color(0xFFFF6B2B),
                                  title: 'Community Announcements',
                                  description: 'Residents receive timely advisories and traffic updates.',
                                ),
                              ],
                            ),
                          ),
                          SizedBox(height: SreaSpacing.lg(context)),
                          _AboutSection(
                            icon: Icons.location_on_rounded,
                            iconColor: SreaColors.primary,
                            title: 'Location',
                            child: _LocationCard(),
                          ),
                          SizedBox(height: SreaSpacing.lg(context)),
                          _ContactFooter(),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AboutAppBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      color: SreaColors.primary,
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back_ios_new_rounded, color: SreaColors.textOnPrimary, size: 20),
            onPressed: () => Navigator.pop(context),
          ),
          Text(
            'About Us',
            style: SreaText.titleLarge(context).copyWith(
              color: SreaColors.textOnPrimary,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroHeader extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [SreaColors.primaryDark, SreaColors.primary],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(28),
          bottomRight: Radius.circular(28),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(24, 8, 24, 32),
      child: Column(
        children: [
          Image.asset('assets/images/mdrrmo_logo.jpg', height: 110, width: 110),
          SizedBox(height: SreaSpacing.md(context)),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: SreaRadius.pill,
              border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.location_on_outlined, size: 14, color: SreaColors.textOnPrimary),
                const SizedBox(width: 4),
                Text(
                  'San Rafael, Bulacan',
                  style: SreaText.label(context).copyWith(
                    color: SreaColors.textOnPrimary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          SizedBox(height: SreaSpacing.md(context)),
          Text(
            'San Rafael\nEmergency Alert System',
            style: SreaText.headlineSmall(context).copyWith(
              color: SreaColors.textOnPrimary,
              fontWeight: FontWeight.w800,
              height: 1.2,
            ),
            textAlign: TextAlign.center,
          ),
          SizedBox(height: SreaSpacing.sm(context)),
          Text(
            'A fast, reliable, and efficient mobile platform for\nemergency reporting and community safety.',
            style: SreaText.bodySmall(context).copyWith(
              color: SreaColors.bottomNavInactive,
              height: 1.5,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class _AboutSection extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final Widget child;

  const _AboutSection({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: SreaSpacing.cardPadding(context),
      decoration: BoxDecoration(
        color: SreaColors.surface,
        borderRadius: SreaRadius.card,
        boxShadow: [
          BoxShadow(color: SreaColors.shadowColor, blurRadius: 12, offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: 0.1),
                  borderRadius: SreaRadius.input,
                ),
                child: Icon(icon, size: 18, color: iconColor),
              ),
              const SizedBox(width: 10),
              Text(
                title,
                style: SreaText.bodyLarge(context).copyWith(
                  color: SreaColors.primary,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(color: SreaColors.divider, height: 1),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }
}

class _FeatureItem extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title;
  final String description;

  const _FeatureItem({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.description,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: SreaColors.surfaceVariant,
        borderRadius: SreaRadius.input,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: iconColor.withValues(alpha: 0.1),
              borderRadius: SreaRadius.input,
            ),
            child: Icon(icon, size: 18, color: iconColor),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: SreaText.bodySmall(context).copyWith(
                    fontWeight: FontWeight.w700,
                    color: SreaColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  description,
                  style: SreaText.label(context).copyWith(
                    color: SreaColors.textSecondary,
                    height: 1.5,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _LocationCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          height: 140,
          width: double.infinity,
          decoration: BoxDecoration(
            color: SreaColors.primaryLight,
            borderRadius: SreaRadius.input,
            border: Border.all(color: SreaColors.border),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.map_outlined, size: 36, color: SreaColors.primary),
              const SizedBox(height: 8),
              Text(
                'San Rafael, Bulacan',
                style: SreaText.bodySmall(context).copyWith(
                  color: SreaColors.primary,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
        SizedBox(height: SreaSpacing.md(context)),
        _InfoRow(
          icon: Icons.location_on_outlined,
          label: 'Address',
          value: 'Municipal Hall, San Rafael, Bulacan',
        ),
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: SreaColors.primary),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: SreaText.label(context).copyWith(
                  color: SreaColors.textSecondary,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: SreaText.bodySmall(context).copyWith(
                  color: SreaColors.textPrimary,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ContactFooter extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: SreaSpacing.cardPadding(context),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [SreaColors.primaryDark, SreaColors.primary],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: SreaRadius.card,
      ),
      child: Column(
        children: [
          Text(
            'SREA — San Rafael Emergency Alert',
            style: SreaText.bodySmall(context).copyWith(
              color: SreaColors.textOnPrimary,
              fontWeight: FontWeight.w700,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          Text(
            'Municipality of San Rafael, Bulacan\nMDRRMO Office',
            style: SreaText.label(context).copyWith(
              color: SreaColors.bottomNavInactive,
              height: 1.6,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 12),
          Container(height: 1, color: Colors.white.withValues(alpha: 0.15)),
          const SizedBox(height: 12),
          Text(
            '© 2025 San Rafael MDRRMO. All rights reserved.',
            style: SreaText.label(context).copyWith(
              color: SreaColors.bottomNavInactive,
              fontSize: 11,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}