import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/incident_report_model.dart';
import '../services/api_service.dart';
import 'incident_report_detail_screen.dart';

class MyReportsScreen extends StatefulWidget {
  const MyReportsScreen({super.key});

  @override
  State<MyReportsScreen> createState() => _MyReportsScreenState();
}

class _MyReportsScreenState extends State<MyReportsScreen> {
  List<IncidentReport> _reports = [];
  bool _isLoading = true;
  String? _error;
  int _currentTabIndex = 0; // 0: Active, 1: Resolved

  @override
  void initState() {
    super.initState();
    _loadReports();
  }

  Future<void> _loadReports() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final List<String> ids = prefs.getStringList('report_ids') ?? [];

      if (ids.isEmpty) {
        setState(() {
          _reports = [];
          _isLoading = false;
        });
        return;
      }

      final api = ApiService();
      final List<Future<IncidentReport?>> futures = ids.map((id) async {
        try {
          final json = await api.getIncidentById(id);
          return IncidentReport.fromJson(json);
        } catch (e) {
          print('Error fetching report $id: $e');
          return null;
        }
      }).toList();

      final List<IncidentReport> results = [];
      for (var future in futures) {
        final report = await future;
        if (report != null) results.add(report);
      }
      results.sort((a, b) => b.reportedAt.compareTo(a.reportedAt));

      setState(() {
        _reports = results;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to load your reports. Pull to refresh.';
        _isLoading = false;
      });
    }
  }

  Future<void> _refresh() async => await _loadReports();

  // ─── Filter by tab ──────────────────────────────────────────────────
  List<IncidentReport> get _activeReports {
    return _reports.where((r) {
      final status = r.status.toLowerCase();
      return status == 'pending' ||
          status == 'responding' ||
          status == 'in_progress' ||
          status == 'active' ||
          status == 'escalated'; // ✅ ADDED – Escalated goes in Active tab
    }).toList();
  }

  List<IncidentReport> get _resolvedReports {
    return _reports.where((r) {
      final status = r.status.toLowerCase();
      return status == 'resolved' || status == 'rejected';
    }).toList();
  }

  // ─── Status helpers ──────────────────────────────────────────────────
  String _getStatusLabel(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'responding':
      case 'in_progress':
      case 'active':
        return 'In Progress';
      case 'escalated': // ✅ ADDED
        return 'Escalated';
      case 'resolved':
        return 'Resolved';
      case 'rejected':
        return 'Rejected';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return SreaColors.warning;
      case 'responding':
      case 'in_progress':
      case 'active':
        return SreaColors.high;
      case 'escalated': // ✅ ADDED – same color as In Progress
        return SreaColors.high;
      case 'resolved':
        return SreaColors.success;
      case 'rejected':
        return SreaColors.error;
      default:
        return SreaColors.textSecondary;
    }
  }

  Color _getStatusBgColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return SreaColors.mediumBg;
      case 'responding':
      case 'in_progress':
      case 'active':
        return SreaColors.highBg;
      case 'escalated': // ✅ ADDED
        return SreaColors.highBg;
      case 'resolved':
        return SreaColors.lowBg;
      case 'rejected':
        return SreaColors.error.withOpacity(0.08);
      default:
        return SreaColors.surfaceVariant;
    }
  }

  // ─── ✅ FIXED: _formatDate now converts to local time ─────────────
  String _formatDate(DateTime date) {
    final localDate = date.toLocal();
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ];
    return '${months[localDate.month - 1]} ${localDate.day}, ${localDate.year}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: SreaColors.background,
      body: Column(
        children: [
          // ─── Custom Tabs ─────────────────────────────────────────────
          Container(
            color: SreaColors.surface,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _currentTabIndex = 0),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        border: Border(
                          bottom: BorderSide(
                            color: _currentTabIndex == 0
                                ? SreaColors.primary
                                : Colors.transparent,
                            width: 2.5,
                          ),
                        ),
                      ),
                      child: Text(
                        'Active',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: _currentTabIndex == 0
                              ? SreaColors.primary
                              : SreaColors.textSecondary,
                          fontWeight: _currentTabIndex == 0
                              ? FontWeight.w700
                              : FontWeight.w500,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                ),
                Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _currentTabIndex = 1),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        border: Border(
                          bottom: BorderSide(
                            color: _currentTabIndex == 1
                                ? SreaColors.primary
                                : Colors.transparent,
                            width: 2.5,
                          ),
                        ),
                      ),
                      child: Text(
                        'Resolved',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: _currentTabIndex == 1
                              ? SreaColors.primary
                              : SreaColors.textSecondary,
                          fontWeight: _currentTabIndex == 1
                              ? FontWeight.w700
                              : FontWeight.w500,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          // ─── Tab Content ─────────────────────────────────────────────
          Expanded(
            child: IndexedStack(
              index: _currentTabIndex,
              children: [
                _buildTabContent(_activeReports, isActive: true),
                _buildTabContent(_resolvedReports, isActive: false),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTabContent(
    List<IncidentReport> reports, {
    required bool isActive,
  }) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 48, color: SreaColors.error),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: SreaText.bodyLarge(
                context,
              ).copyWith(color: SreaColors.textSecondary),
            ),
            const SizedBox(height: 16),
            SreaButton(
              label: 'Retry',
              onPressed: _loadReports,
              size: SreaButtonSize.medium,
            ),
          ],
        ),
      );
    }

    if (reports.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isActive ? Icons.inbox_outlined : Icons.check_circle_outline,
              size: 64,
              color: SreaColors.textHint,
            ),
            const SizedBox(height: 16),
            Text(
              isActive ? 'No active reports' : 'No resolved reports',
              style: SreaText.headlineSmall(
                context,
              ).copyWith(color: SreaColors.textSecondary),
            ),
            const SizedBox(height: 8),
            Text(
              isActive
                  ? 'Your ongoing reports will appear here.'
                  : 'Completed reports will appear here.',
              textAlign: TextAlign.center,
              style: SreaText.bodySmall(
                context,
              ).copyWith(color: SreaColors.textHint),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _refresh,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: reports.length,
        itemBuilder: (context, index) {
          return _ReportCard(report: reports[index]);
        },
      ),
    );
  }
}

// ─── Report Card ──────────────────────────────────────────────────────────

class _ReportCard extends StatelessWidget {
  final IncidentReport report;
  const _ReportCard({required this.report});

  String _getStatusLabel(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'responding':
      case 'in_progress':
      case 'active':
        return 'In Progress';
      case 'escalated': // ✅ ADDED
        return 'Escalated';
      case 'resolved':
        return 'Resolved';
      case 'rejected':
        return 'Rejected';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return SreaColors.warning;
      case 'responding':
      case 'in_progress':
      case 'active':
        return SreaColors.high;
      case 'escalated': // ✅ ADDED
        return SreaColors.high;
      case 'resolved':
        return SreaColors.success;
      case 'rejected':
        return SreaColors.error;
      default:
        return SreaColors.textSecondary;
    }
  }

  Color _getStatusBgColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return SreaColors.mediumBg;
      case 'responding':
      case 'in_progress':
      case 'active':
        return SreaColors.highBg;
      case 'escalated': // ✅ ADDED
        return SreaColors.highBg;
      case 'resolved':
        return SreaColors.lowBg;
      case 'rejected':
        return SreaColors.error.withOpacity(0.08);
      default:
        return SreaColors.surfaceVariant;
    }
  }

  String _formatDate(DateTime date) {
    final localDate = date.toLocal();
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ];
    return '${months[localDate.month - 1]} ${localDate.day}, ${localDate.year}';
  }

  @override
  Widget build(BuildContext context) {
    final statusLabel = _getStatusLabel(report.status);
    final statusColor = _getStatusColor(report.status);
    final statusBgColor = _getStatusBgColor(report.status);
    final isRejected = report.status.toLowerCase() == 'rejected';
    final showDuplicate = report.isPotentialDuplicate && !isRejected;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: SreaColors.surface,
        borderRadius: SreaRadius.card,
        border: Border.all(color: SreaColors.border, width: 0.5),
        boxShadow: [
          BoxShadow(
            color: SreaColors.shadowColor,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: SreaRadius.card,
        child: InkWell(
          borderRadius: SreaRadius.card,
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => IncidentReportDetailScreen(report: report),
              ),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 8,
                  height: 8,
                  margin: const EdgeInsets.only(top: 4),
                  decoration: BoxDecoration(
                    color: statusColor,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child:
                      report.photoPath != null && report.photoPath!.isNotEmpty
                      ? Image.network(
                          ApiService().getFullImageUrl(report.photoPath)!,
                          width: 56,
                          height: 56,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 56,
                            height: 56,
                            color: SreaColors.surfaceVariant,
                            child: Icon(
                              Icons.broken_image_outlined,
                              size: 22,
                              color: SreaColors.textHint,
                            ),
                          ),
                        )
                      : Container(
                          width: 56,
                          height: 56,
                          color: SreaColors.surfaceVariant,
                          child: Icon(
                            Icons.report_outlined,
                            size: 22,
                            color: SreaColors.textHint,
                          ),
                        ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        report.barangay,
                        style: SreaText.bodyLarge(
                          context,
                        ).copyWith(fontWeight: FontWeight.w700, fontSize: 15),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${report.address.isNotEmpty ? report.address : report.type} · ${_formatDate(report.reportedAt)}',
                        style: SreaText.bodySmall(context).copyWith(
                          color: SreaColors.textSecondary,
                          fontSize: 12,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: statusBgColor,
                        borderRadius: SreaRadius.pill,
                        border: Border.all(color: statusColor.withOpacity(0.3)),
                      ),
                      child: Text(
                        statusLabel,
                        style: SreaText.label(context).copyWith(
                          color: statusColor,
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    if (showDuplicate) ...[
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: SreaColors.warning.withOpacity(0.15),
                          borderRadius: SreaRadius.pill,
                          border: Border.all(
                            color: SreaColors.warning.withOpacity(0.4),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.warning_amber_rounded,
                              size: 12,
                              color: SreaColors.warning,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              'Similar nearby',
                              style: SreaText.label(context).copyWith(
                                color: SreaColors.warning,
                                fontSize: 9,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                    const SizedBox(height: 4),
                    Text(
                      'View →',
                      style: SreaText.label(context).copyWith(
                        color: SreaColors.primary,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
