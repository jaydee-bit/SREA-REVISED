// File: privacy_policy_screen.dart
// Path: mobile_user_app/lib/screens/privacy_policy_screen.dart

import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';

class PrivacyPolicyScreen extends StatelessWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: SreaColors.background,
      appBar: AppBar(
        backgroundColor: SreaColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: SreaColors.textOnPrimary),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Privacy Policy',
          style: SreaText.titleLarge(context).copyWith(color: SreaColors.textOnPrimary),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _SectionTitle(title: 'Privacy Policy'),
              const SizedBox(height: 8),
              _BodyText(
                'SREA (San Rafael Emergency Alert) is committed to protecting your privacy. '
                'This Privacy Policy explains how we collect, use, and safeguard your information '
                'when you use our mobile application.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Information We Collect'),
              const SizedBox(height: 8),
              _BodyText(
                'We may collect the following types of information to provide and improve our services:',
              ),
              const SizedBox(height: 8),
              _BulletPoint(text: 'Personal information: name, email address, phone number, and barangay'),
              _BulletPoint(text: 'Location data (GPS) to send accurate emergency alerts and locate you during incidents'),
              _BulletPoint(text: 'Device information: model, operating system, and unique device identifiers'),
              _BulletPoint(text: 'Usage data: how you interact with the app (e.g., alerts viewed, reports submitted)'),
              const SizedBox(height: 16),

              _SectionTitle(title: 'How We Use Your Information'),
              const SizedBox(height: 8),
              _BodyText('We use your information to:'),
              const SizedBox(height: 8),
              _BulletPoint(text: 'Send real‑time emergency alerts and advisories relevant to your area'),
              _BulletPoint(text: 'Process and respond to incident reports you submit'),
              _BulletPoint(text: 'Share your location with emergency responders when you report an incident'),
              _BulletPoint(text: 'Improve app performance, reliability, and user experience'),
              _BulletPoint(text: 'Comply with legal obligations and ensure public safety'),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Emergency Call & Location Sharing'),
              const SizedBox(height: 8),
              _BodyText(
                'When you tap the emergency call button, the app will connect you directly to the San Rafael DRRMO hotline. '
                'If you have granted location permission, your current location will be shared with the dispatcher to expedite response. '
                'You can disable location sharing at any time in your device settings, but some features may not work properly.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Data Security'),
              const SizedBox(height: 8),
              _BodyText(
                'We implement reasonable security measures to protect your personal information from unauthorized access, alteration, '
                'or disclosure. However, no method of transmission over the internet or electronic storage is 100% secure, and we cannot '
                'guarantee absolute security.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Data Retention'),
              const SizedBox(height: 8),
              _BodyText(
                'We retain your personal information only as long as necessary to fulfill the purposes outlined in this Privacy Policy, '
                'unless a longer retention period is required or permitted by law. Incident reports may be kept for archival and public safety purposes.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Third‑Party Services'),
              const SizedBox(height: 8),
              _BodyText(
                'SREA may use third‑party services (e.g., OpenStreetMap for maps, Firebase for push notifications) that collect information '
                'to improve their functionality. These services have their own privacy policies, and we encourage you to review them.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Children’s Privacy'),
              const SizedBox(height: 8),
              _BodyText(
                'Our Service does not address anyone under the age of 13. We do not knowingly collect personally identifiable information from children under 13. '
                'If you are a parent or guardian and you are aware that your child has provided us with personal information, please contact us.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Changes to This Privacy Policy'),
              const SizedBox(height: 8),
              _BodyText(
                'We may update our Privacy Policy from time to time. You will be notified of any changes by posting the new Privacy Policy on this page. '
                'Changes are effective immediately after they are posted.',
              ),
              const SizedBox(height: 16),

              _SectionTitle(title: 'Contact Us'),
              const SizedBox(height: 8),
              _BodyText(
                'If you have any questions about this Privacy Policy, please contact us:',
              ),
              const SizedBox(height: 8),
              _BodyText(
                '📧 Email: drrmo@sanrafael.gov.ph\n'
                '📞 Phone: (044) 123-4567\n'
                '🏢 Address: Municipal Hall, San Rafael, Bulacan',
              ),
              const SizedBox(height: 24),
              Center(
                child: Text(
                  '© ${DateTime.now().year} San Rafael DRRMO — SREA',
                  style: SreaText.label(context).copyWith(color: SreaColors.textHint),
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle({required this.title});

  @override
  Widget build(BuildContext context) {
    return Text(
      title,
      style: SreaText.bodyLarge(context).copyWith(
        fontWeight: FontWeight.w700,
        color: SreaColors.primary,
      ),
    );
  }
}

class _BodyText extends StatelessWidget {
  final String text;
  const _BodyText(this.text);

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: SreaText.bodySmall(context).copyWith(
        color: SreaColors.textPrimary,
        height: 1.5,
      ),
    );
  }
}

class _BulletPoint extends StatelessWidget {
  final String text;
  const _BulletPoint({required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('• ', style: SreaText.bodySmall(context).copyWith(color: SreaColors.primary)),
          Expanded(
            child: Text(
              text,
              style: SreaText.bodySmall(context).copyWith(
                color: SreaColors.textPrimary,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}