import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import '../services/api_service.dart';
import 'alert_detail_screen.dart';

class AlertsScreen extends StatefulWidget {
  const AlertsScreen({super.key});

  @override
  State<AlertsScreen> createState() => _AlertsScreenState();
}

class _AlertsScreenState extends State<AlertsScreen> {
  List<dynamic> _alerts = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAlerts();
  }

  Future<void> _loadAlerts() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ApiService();
      final data = await api.getAlerts();
      if (mounted) {
        setState(() {
          _alerts = data;
          _isLoading = false;
        });
      }
    } catch (e) {
      print('❌ Error loading alerts: $e');
      if (mounted) {
        setState(() {
          _error = 'Failed to load alerts. Pull to refresh.';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _refresh() async => _loadAlerts();

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const ColoredBox(
        color: SreaColors.background,
        child: Center(child: CircularProgressIndicator()),
      );
    }

    if (_error != null) {
      return ColoredBox(
        color: SreaColors.background,
        child: RefreshIndicator(
          onRefresh: _refresh,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.error_outline,
                      size: 48,
                      color: SreaColors.error,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      _error!,
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: _loadAlerts,
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    if (_alerts.isEmpty) {
      return ColoredBox(
        color: SreaColors.background,
        child: RefreshIndicator(
          onRefresh: _refresh,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.notifications_off_outlined,
                      size: 64,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No alerts',
                      style: SreaText.bodyLarge(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Check back later for updates.',
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textHint),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    return ColoredBox(
      color: SreaColors.background,
      child: RefreshIndicator(
        onRefresh: _refresh,
        child: Column(
          children: [
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
                itemCount: _alerts.length,
                itemBuilder: (context, index) {
                  final alert = _alerts[index];
                  final level = alert['level']?.toLowerCase() ?? 'low';
                  final color = _getLevelColor(level);

                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(borderRadius: SreaRadius.card),
                    child: SreaCard(
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => AlertDetailScreen(alert: alert),
                        ),
                      ),
                      child: Row(
                        children: [
                          // Colored icon circle (replaces left border)
                          Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: color.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              Icons.warning_amber_rounded,
                              color: color,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        alert['title'] ?? 'Alert',
                                        style: SreaText.bodyLarge(
                                          context,
                                        ).copyWith(fontWeight: FontWeight.w700),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    // ✅ Consistent badge style
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 10,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(
                                        color: color.withOpacity(0.12),
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(
                                          color: color.withOpacity(0.3),
                                        ),
                                      ),
                                      child: Text(
                                        level.toUpperCase(),
                                        style: TextStyle(
                                          color: color,
                                          fontSize: 10,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  alert['description'] ?? '',
                                  style: SreaText.bodySmall(context).copyWith(
                                    color: SreaColors.textSecondary,
                                    height: 1.4,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Icon(
                                      Icons.calendar_today_outlined,
                                      size: 12,
                                      color: SreaColors.textHint,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      _formatDate(alert['created_at']),
                                      style: SreaText.label(
                                        context,
                                      ).copyWith(color: SreaColors.textHint),
                                    ),
                                    if (alert['barangay'] != null &&
                                        alert['barangay'].isNotEmpty) ...[
                                      const SizedBox(width: 12),
                                      Icon(
                                        Icons.location_on_outlined,
                                        size: 12,
                                        color: SreaColors.textHint,
                                      ),
                                      const SizedBox(width: 4),
                                      Expanded(
                                        child: Text(
                                          alert['barangay'],
                                          style: SreaText.label(context)
                                              .copyWith(
                                                color: SreaColors.textHint,
                                                fontSize: 11,
                                              ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getLevelColor(String? level) {
    switch (level?.toLowerCase()) {
      case 'critical':
      case 'high':
        return SreaColors.critical;
      case 'medium':
        return SreaColors.warning;
      case 'low':
        return SreaColors.low;
      default:
        return SreaColors.textSecondary;
    }
  }

  String _formatDate(dynamic dateString) {
    try {
      final date = DateTime.parse(dateString);
      final now = DateTime.now();
      final diff = now.difference(date);
      if (diff.inDays > 0) return '${diff.inDays} days ago';
      if (diff.inHours > 0) return '${diff.inHours} hours ago';
      if (diff.inMinutes > 0) return '${diff.inMinutes} minutes ago';
      return 'Just now';
    } catch (e) {
      return dateString?.toString() ?? 'Unknown';
    }
  }
}
