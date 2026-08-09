import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:latlong2/latlong.dart';
import '../models/incident_report_model.dart';
import 'incident_detail_screen.dart';
import '../services/api_service.dart';

class IncidentListScreen extends StatelessWidget {
  final String? initialFilter;
  final bool assignedToMe;
  const IncidentListScreen({
    super.key,
    this.initialFilter,
    this.assignedToMe = false,
  });

  @override
  Widget build(BuildContext context) {
    return _IncidentListBody(
      initialFilter: initialFilter,
      assignedToMe: assignedToMe,
    );
  }
}

class _IncidentListBody extends StatefulWidget {
  final String? initialFilter;
  final bool assignedToMe;
  const _IncidentListBody({this.initialFilter, this.assignedToMe = false});

  @override
  State<_IncidentListBody> createState() => _IncidentListBodyState();
}

class _IncidentListBodyState extends State<_IncidentListBody> {
  List<IncidentReport> _allIncidents = [];
  bool _isLoading = true;
  String? _error;
  String _filterStatus = 'Active';
  String? _filterBarangay;

  final List<String> _statusOptions = [
    'All',
    'Active',
    'Pending',
    'Responding',
    'Resolved',
    'Rejected',
  ];

  final List<String> _barangayOptions = [
    'All',
    'Banca-Banca',
    'BMA – Balagtas',
    'Caingin',
    'Capihan',
    'Coral na Bato',
    'Cruz na Daan',
    'Dagat-Dagatan',
    'Diliman I',
    'Diliman II',
    'Libis',
    'Lico',
    'Maasim',
    'Mabalas-Balas',
    'Maguinao',
    'Maronquillo',
    'Paco',
    'Pansumaloc',
    'Pantubig',
    'Pasong Bangkal',
    'Pasong Callos',
    'Pasong Intsik',
    'Pinacpinacan',
    'Poblacion',
    'Pulo',
    'Pulong Bayabas',
    'Salapungan',
    'Sampaloc',
    'San Agustin',
    'San Roque',
    'Sapang Pahalang',
    'Talacsan',
    'Tambubong',
    'Tukod',
    'Ulingao',
  ];

  @override
  void initState() {
    super.initState();
    _loadIncidents();
    if (widget.initialFilter == 'active') _filterStatus = 'Active';
    if (widget.initialFilter == 'resolved') _filterStatus = 'Resolved';
  }

  Future<void> _loadIncidents() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ApiService();
      final data = await api.getIncidents(assignedToMe: widget.assignedToMe);
      final List<IncidentReport> incidents = data
          .map((json) => IncidentReport.fromJson(json))
          .toList();
      if (!mounted) return;
      setState(() {
        _allIncidents = incidents;
        _isLoading = false;
      });
    } catch (e) {
      print('❌ ERROR in _loadIncidents: $e');
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _refresh() async => _loadIncidents();

  List<IncidentReport> get _filteredIncidents {
    List<IncidentReport> filtered = List.from(_allIncidents);
    if (_filterStatus != 'All') {
      if (_filterStatus == 'Active') {
        filtered = filtered
            .where(
              (inc) => inc.status == 'Pending' || inc.status == 'Responding',
            )
            .toList();
      } else {
        filtered = filtered
            .where((inc) => inc.status == _filterStatus)
            .toList();
      }
    }
    if (_filterBarangay != null && _filterBarangay != 'All') {
      filtered = filtered
          .where((inc) => inc.barangay == _filterBarangay)
          .toList();
    }
    filtered.sort((a, b) {
      int getPriority(IncidentReport inc) {
        if (inc.status == 'Pending') return 0;
        if (inc.status == 'Responding') return 1;
        return 2;
      }

      final aPriority = getPriority(a);
      final bPriority = getPriority(b);
      if (aPriority != bPriority) return aPriority.compareTo(bPriority);
      return b.reportedAt.compareTo(a.reportedAt);
    });
    return filtered;
  }

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
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(20.0),
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
                  'Error loading incidents',
                  style: SreaText.titleLarge(
                    context,
                  ).copyWith(color: SreaColors.error),
                ),
                const SizedBox(height: 8),
                Text(
                  _error!,
                  style: SreaText.bodySmall(
                    context,
                  ).copyWith(color: SreaColors.textSecondary),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: _loadIncidents,
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return ColoredBox(
      color: SreaColors.background,
      child: RefreshIndicator(
        onRefresh: _refresh,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              // ─── Filters ─────────────────────────────────────────────
              Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Expanded(
                      child: SreaDropdown<String>(
                        label: 'Status',
                        hint: 'All',
                        value: _filterStatus,
                        items: _statusOptions,
                        onChanged: (v) => setState(() => _filterStatus = v!),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: SreaDropdown<String>(
                        label: 'Barangay',
                        hint: 'All',
                        value: _filterBarangay ?? 'All',
                        items: _barangayOptions,
                        onChanged: (v) => setState(
                          () => _filterBarangay = v == 'All' ? null : v,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // ─── Incident list ──────────────────────────────────────
              _filteredIncidents.isEmpty
                  ? const Padding(
                      padding: EdgeInsets.all(32),
                      child: Center(child: Text('No incidents found')),
                    )
                  : ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(12),
                      itemCount: _filteredIncidents.length,
                      itemBuilder: (context, index) =>
                          _IncidentCard(incident: _filteredIncidents[index]),
                    ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Incident Card ────────────────────────────────────────────────────

class _IncidentCard extends StatelessWidget {
  final IncidentReport incident;
  const _IncidentCard({required this.incident});

  String _getThumbnailUrl() {
    final path = incident.photoPath;
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final base = ApiService.baseImageUrl.endsWith('/')
        ? ApiService.baseImageUrl.substring(
            0,
            ApiService.baseImageUrl.length - 1,
          )
        : ApiService.baseImageUrl;
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    return '$base$normalizedPath';
  }

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final thumbSize = width * 0.19;
    final thumbRadius = width * 0.018;
    final innerGap = width * 0.012;

    final description = incident.description.length > 120
        ? '${incident.description.substring(0, 120)}...'
        : incident.description;
    final thumbnailUrl = _getThumbnailUrl();
    final hasPhoto = thumbnailUrl.isNotEmpty;
    final hasVideo =
        incident.videoPath != null && incident.videoPath!.isNotEmpty;

    // ─── Potential Duplicate Badge ──────────────────────────────────
    final showDuplicateBadge =
        incident.isPotentialDuplicate && incident.potentialDuplicateCount > 0;

    return Padding(
      padding: EdgeInsets.only(bottom: SreaSpacing.sm(context)),
      child: SreaCard(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => IncidentDetailScreen(incident: incident),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ─── Row: Type, Reporter, Status ──────────────────────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        incident.type,
                        style: SreaText.bodyLarge(
                          context,
                        ).copyWith(fontWeight: FontWeight.w700),
                        overflow: TextOverflow.ellipsis,
                        maxLines: 1,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Reported by: ${incident.reporterName}',
                        style: SreaText.label(context).copyWith(
                          color: incident.reporterName == 'Anonymous'
                              ? SreaColors.textHint
                              : SreaColors.textSecondary,
                          fontSize: width * 0.025,
                          fontStyle: incident.reporterName == 'Anonymous'
                              ? FontStyle.italic
                              : FontStyle.normal,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    SreaBadge(
                      type: _statusToBadgeType(incident.status),
                      label: incident.status,
                      showDot: true,
                    ),
                    // ─── Duplicate badge ──────────────────────────────
                    if (showDuplicateBadge) ...[
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
                              '⚠️ ${incident.potentialDuplicateCount} nearby',
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
                  ],
                ),
              ],
            ),
            SizedBox(height: innerGap),
            // ─── Row: Details + Photo ──────────────────────────────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${incident.barangay}  ·  ${_formatDate(incident.reportedAt)}',
                        style: SreaText.label(
                          context,
                        ).copyWith(color: SreaColors.textSecondary),
                        overflow: TextOverflow.ellipsis,
                        maxLines: 1,
                      ),
                      SizedBox(height: innerGap),
                      Text(
                        description,
                        style: SreaText.bodySmall(context).copyWith(
                          color: SreaColors.textSecondary,
                          height: 1.45,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      if (incident.locationDetails != null &&
                          incident.locationDetails!.isNotEmpty) ...[
                        SizedBox(height: innerGap),
                        Row(
                          children: [
                            Icon(
                              Icons.location_on_outlined,
                              size: width * 0.03,
                              color: SreaColors.textHint,
                            ),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                incident.locationDetails!,
                                style: SreaText.label(
                                  context,
                                ).copyWith(color: SreaColors.textHint),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                // ─── Thumbnail with Video Indicator ──────────────────
                Stack(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(thumbRadius),
                      child: Container(
                        width: thumbSize,
                        height: thumbSize,
                        color: SreaColors.surfaceVariant,
                        child: hasPhoto
                            ? Image.network(
                                thumbnailUrl,
                                fit: BoxFit.cover,
                                cacheWidth: thumbSize.round(),
                                cacheHeight: thumbSize.round(),
                                errorBuilder: (_, __, ___) => Icon(
                                  Icons.broken_image_outlined,
                                  size: thumbSize * 0.42,
                                  color: SreaColors.textHint,
                                ),
                              )
                            : Icon(
                                Icons.image_not_supported_outlined,
                                size: thumbSize * 0.42,
                                color: SreaColors.textHint,
                              ),
                      ),
                    ),
                    // ─── Video indicator (play icon overlay) ──────────
                    if (hasVideo)
                      Positioned(
                        bottom: 4,
                        right: 4,
                        child: Container(
                          padding: const EdgeInsets.all(2),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.7),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Icon(
                            Icons.play_arrow_rounded,
                            color: Colors.white,
                            size: 16,
                          ),
                        ),
                      ),
                  ],
                ),
              ],
            ),
            SizedBox(height: innerGap * 1.2),
            const Divider(height: 1, color: SreaColors.divider),
            SizedBox(height: innerGap),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton(
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => IncidentDetailScreen(incident: incident),
                  ),
                ),
                style: TextButton.styleFrom(
                  padding: EdgeInsets.symmetric(
                    horizontal: SreaSpacing.sm(context),
                    vertical: width * 0.012,
                  ),
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: Text(
                  'View details →',
                  style: SreaText.label(context).copyWith(
                    color: SreaColors.primary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(DateTime date) =>
      '${_monthAbbr(date.month)} ${date.day}, ${date.year}';
  String _monthAbbr(int m) => const [
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
  ][m - 1];

  SreaBadgeType _statusToBadgeType(String status) {
    switch (status.toLowerCase()) {
      case 'resolved':
        return SreaBadgeType.resolved;
      case 'rejected':
        return SreaBadgeType.rejected;
      case 'responding':
        return SreaBadgeType.underReview;
      default:
        return SreaBadgeType.pending;
    }
  }
}
