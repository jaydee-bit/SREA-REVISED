import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import '../services/api_service.dart';
import 'traffic_advisory_detail_screen.dart';

class TrafficAdvisory {
  final dynamic id;
  final String title;
  final String description;
  final String location;
  final SreaBadgeType severity;
  final DateTime publishedAt;
  final DateTime? effectiveFrom;
  final DateTime? effectiveTo;

  TrafficAdvisory({
    required this.id,
    required this.title,
    required this.description,
    required this.location,
    required this.severity,
    required this.publishedAt,
    this.effectiveFrom,
    this.effectiveTo,
  });
}

class TrafficAdvisoriesScreen extends StatefulWidget {
  const TrafficAdvisoriesScreen({super.key});

  @override
  State<TrafficAdvisoriesScreen> createState() =>
      _TrafficAdvisoriesScreenState();
}

class _TrafficAdvisoriesScreenState extends State<TrafficAdvisoriesScreen> {
  List<TrafficAdvisory> _advisories = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadAdvisories();
  }

  Future<void> _loadAdvisories() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ApiService();
      final data = await api.getTrafficAdvisories();
      final severityMap = {
        'high': SreaBadgeType.high,
        'medium': SreaBadgeType.medium,
        'low': SreaBadgeType.low,
      };
      final list = data.map((json) {
        return TrafficAdvisory(
          id: json['id'],
          title: json['title'] ?? '',
          description: json['description'] ?? '',
          location: json['location'] ?? '',
          severity: severityMap[json['severity']] ?? SreaBadgeType.low,
          publishedAt: DateTime.parse(
            json['created_at'] ?? DateTime.now().toIso8601String(),
          ),
          effectiveFrom: json['effective_from'] != null
              ? DateTime.parse(json['effective_from'])
              : null,
          effectiveTo: json['effective_to'] != null
              ? DateTime.parse(json['effective_to'])
              : null,
        );
      }).toList();
      if (mounted) {
        setState(() {
          _advisories = list;
          _isLoading = false;
        });
      }
    } catch (e) {
      print('❌ Error loading traffic advisories: $e');
      if (mounted) {
        setState(() {
          _error = 'Failed to load traffic advisories. Pull to refresh.';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _refresh() async => _loadAdvisories();

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
                      onPressed: _loadAdvisories,
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

    if (_advisories.isEmpty) {
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
                      Icons.traffic_outlined,
                      size: 64,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No traffic advisories',
                      style: SreaText.bodyLarge(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
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
                itemCount: _advisories.length,
                itemBuilder: (context, index) {
                  final adv = _advisories[index];
                  final color = _getSeverityColor(adv.severity);

                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(borderRadius: SreaRadius.card),
                    child: SreaCard(
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) =>
                              TrafficAdvisoryDetailScreen(advisory: adv),
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
                              Icons.traffic_rounded,
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
                                        adv.title,
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
                                        adv.severity.name.toUpperCase(),
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
                                  adv.description,
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
                                      Icons.location_on_outlined,
                                      size: 12,
                                      color: SreaColors.textHint,
                                    ),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        adv.location,
                                        style: SreaText.label(context).copyWith(
                                          color: SreaColors.textHint,
                                          fontSize: 11,
                                        ),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  _formatDate(adv.publishedAt),
                                  style: SreaText.label(
                                    context,
                                  ).copyWith(color: SreaColors.textHint),
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

  Color _getSeverityColor(SreaBadgeType severity) {
    switch (severity) {
      case SreaBadgeType.high:
        return SreaColors.critical;
      case SreaBadgeType.medium:
        return SreaColors.warning;
      case SreaBadgeType.low:
        return SreaColors.low;
      default:
        return SreaColors.textSecondary;
    }
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inDays > 0) return '${diff.inDays} days ago';
    if (diff.inHours > 0) return '${diff.inHours} hours ago';
    if (diff.inMinutes > 0) return '${diff.inMinutes} minutes ago';
    return 'Just now';
  }
}
