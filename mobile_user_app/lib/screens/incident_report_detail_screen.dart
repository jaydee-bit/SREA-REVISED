import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';
import '../models/incident_report_model.dart';
import '../services/api_service.dart';

class IncidentReportDetailScreen extends StatefulWidget {
  final IncidentReport report;
  const IncidentReportDetailScreen({super.key, required this.report});

  @override
  State<IncidentReportDetailScreen> createState() =>
      _IncidentReportDetailScreenState();
}

class _IncidentReportDetailScreenState
    extends State<IncidentReportDetailScreen> {
  final MapController _mapController = MapController();
  VideoPlayerController? _videoController;
  bool _videoFailedToLoad = false;

  @override
  void initState() {
    super.initState();
    final videoPath = widget.report.videoPath;
    print('📹 Video path: $videoPath');
    if (videoPath != null && videoPath.isNotEmpty) {
      final videoUrl = ApiService().getFullImageUrl(videoPath)!;
      print('📹 Video URL: $videoUrl');
      _videoController = VideoPlayerController.network(videoUrl)
        ..initialize()
            .then((_) {
              if (mounted) setState(() {});
              _videoController!.play();
            })
            .catchError((e) {
              print('❌ Video failed to initialize: $e');
              if (mounted) setState(() => _videoFailedToLoad = true);
            })
        ..setLooping(false)
        ..addListener(() {
          if (mounted) setState(() {});
        });
    }
  }

  @override
  void dispose() {
    _videoController?.dispose();
    super.dispose();
  }

  void _openMaps() async {
    final lat = widget.report.coordinates.latitude;
    final lng = widget.report.coordinates.longitude;

    final geoUri = Uri.parse('geo:$lat,$lng?q=$lat,$lng');
    final webUri = Uri.parse(
      'https://www.google.com/maps/search/?api=1&query=$lat,$lng',
    );

    try {
      if (await canLaunchUrl(geoUri)) {
        await launchUrl(geoUri);
        return;
      }
      if (await canLaunchUrl(webUri)) {
        await launchUrl(webUri, mode: LaunchMode.externalApplication);
        return;
      }
      throw 'No app available to open the map.';
    } catch (e) {
      print('❌ Error opening maps: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text(
              'Could not open maps. Is a maps app installed?',
            ),
            backgroundColor: SreaColors.error,
          ),
        );
      }
    }
  }

  String _formatDateTime(DateTime date) {
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
    final month = months[date.month - 1];
    final day = date.day;
    final year = date.year;
    int hour = date.hour;
    final minute = date.minute.toString().padLeft(2, '0');
    final amPm = hour >= 12 ? 'PM' : 'AM';
    if (hour > 12) hour -= 12;
    if (hour == 0) hour = 12;
    return '$month $day, $year at $hour:$minute $amPm';
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return SreaColors.warning;
      case 'in_progress':
      case 'responding':
        return SreaColors.high;
      case 'resolved':
        return SreaColors.success;
      default:
        return SreaColors.textSecondary;
    }
  }

  String _getStatusLabel(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'PENDING';
      case 'responding':
      case 'in_progress':
        return 'IN PROGRESS';
      case 'resolved':
        return 'RESOLVED';
      default:
        return status.toUpperCase();
    }
  }

  @override
  Widget build(BuildContext context) {
    final report = widget.report;
    final statusColor = _getStatusColor(report.status);
    final statusLabel = _getStatusLabel(report.status);

    final bool isClassified =
        report.type != 'Emergency' &&
        report.description != 'Emergency report with selfie and video';

    print('🔍 Report: ${report.id}');
    print('📸 Photo path: ${report.photoPath}');
    print('🎬 Video path: ${report.videoPath}');

    return Scaffold(
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
          'Incident Details',
          style: SreaText.titleLarge(
            context,
          ).copyWith(color: SreaColors.textOnPrimary),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ─── Incident Type + Status Badge (same row) ──────────────
            if (isClassified) ...[
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Text(
                      report.type,
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: SreaColors.textPrimary,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: statusColor.withOpacity(0.3)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: statusColor,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          statusLabel,
                          style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
            ] else ...[
              // ─── Unclassified: "Waiting" message + badge same row ──
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      child: Row(
                        children: [
                          Icon(
                            Icons.info_outline,
                            color: SreaColors.warning,
                            size: 20,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              'Waiting to be classified',
                              style: SreaText.bodySmall(context).copyWith(
                                color: SreaColors.textSecondary,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: statusColor.withOpacity(0.3)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: statusColor,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          statusLabel,
                          style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
            ],

            // ─── Metadata Row ──────────────────────────────────────────
            Wrap(
              spacing: 16,
              runSpacing: 8,
              children: [
                // Reporter
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.person_outline,
                      size: 16,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Reported by: ${report.reporterName ?? "Anonymous"}',
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                  ],
                ),
                // Location
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      size: 16,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '${report.barangay}${report.address.isNotEmpty ? " • ${report.address}" : ""}',
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
                // Reported At
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.calendar_today_outlined,
                      size: 14,
                      color: SreaColors.textHint,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      _formatDateTime(report.reportedAt),
                      style: SreaText.bodySmall(
                        context,
                      ).copyWith(color: SreaColors.textSecondary),
                    ),
                  ],
                ),
                // Assigned To (if any)
                if (report.assignedToName != null)
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.person_outline,
                        size: 14,
                        color: SreaColors.textHint,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Assigned to: ${report.assignedToName}',
                        style: SreaText.bodySmall(
                          context,
                        ).copyWith(color: SreaColors.textSecondary),
                      ),
                    ],
                  ),
              ],
            ),
            const SizedBox(height: 16),

            const Divider(),
            const SizedBox(height: 16),

            // ─── Description (only if classified) ────────────────────────
            if (isClassified && report.description.isNotEmpty) ...[
              const Text(
                'Description',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: SreaColors.textSecondary,
                ),
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: SreaColors.surface,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: SreaColors.border),
                ),
                child: Text(
                  report.description,
                  style: SreaText.bodyLarge(
                    context,
                  ).copyWith(color: SreaColors.textPrimary),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // ─── Selfie Photo ─────────────────────────────────────────────
            const Text(
              'Selfie Photo',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: SreaColors.textSecondary,
              ),
            ),
            const SizedBox(height: 6),
            if (report.photoPath != null && report.photoPath!.isNotEmpty)
              GestureDetector(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => Scaffold(
                        backgroundColor: Colors.black,
                        appBar: AppBar(
                          backgroundColor: Colors.transparent,
                          elevation: 0,
                          leading: IconButton(
                            icon: const Icon(Icons.close, color: Colors.white),
                            onPressed: () => Navigator.pop(context),
                          ),
                        ),
                        body: Center(
                          child: InteractiveViewer(
                            minScale: 0.5,
                            maxScale: 4.0,
                            child: Image.network(
                              ApiService().getFullImageUrl(report.photoPath)!,
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) => const Center(
                                child: Text(
                                  'Image not available',
                                  style: TextStyle(color: Colors.white),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  );
                },
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    ApiService().getFullImageUrl(report.photoPath)!,
                    height: 200,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    loadingBuilder: (context, child, loadingProgress) {
                      if (loadingProgress == null) return child;
                      return Container(
                        height: 200,
                        color: SreaColors.surfaceVariant,
                        child: const Center(child: CircularProgressIndicator()),
                      );
                    },
                    errorBuilder: (_, __, ___) => Container(
                      height: 200,
                      color: SreaColors.surfaceVariant,
                      child: const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.broken_image,
                              size: 48,
                              color: SreaColors.textHint,
                            ),
                            SizedBox(height: 8),
                            Text(
                              'Image not available',
                              style: TextStyle(color: SreaColors.textHint),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              )
            else
              Container(
                height: 150,
                color: SreaColors.surfaceVariant,
                child: const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.photo_camera_back,
                        size: 48,
                        color: SreaColors.textHint,
                      ),
                      SizedBox(height: 8),
                      Text(
                        'No selfie photo submitted',
                        style: TextStyle(color: SreaColors.textHint),
                      ),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 16),

            // ─── Video ────────────────────────────────────────────────────
            const Text(
              'Video Recording',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: SreaColors.textSecondary,
              ),
            ),
            const SizedBox(height: 6),
            if (_videoController != null &&
                _videoController!.value.isInitialized)
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: AspectRatio(
                  aspectRatio: _videoController!.value.aspectRatio,
                  child: Stack(
                    alignment: Alignment.center,
                    children: [
                      VideoPlayer(_videoController!),
                      Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.bottomCenter,
                            end: Alignment.topCenter,
                            colors: [
                              Colors.black.withOpacity(0.3),
                              Colors.transparent,
                            ],
                          ),
                        ),
                      ),
                      Positioned(
                        bottom: 16,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.6),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: Icon(
                                  _videoController!.value.isPlaying
                                      ? Icons.pause
                                      : Icons.play_arrow,
                                  color: Colors.white,
                                ),
                                onPressed: () {
                                  setState(() {
                                    _videoController!.value.isPlaying
                                        ? _videoController!.pause()
                                        : _videoController!.play();
                                  });
                                },
                              ),
                              Text(
                                _videoController!.value.position
                                    .toString()
                                    .split('.')[0],
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                '/ ${_videoController!.value.duration.toString().split('.')[0]}',
                                style: const TextStyle(
                                  color: Colors.white70,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else
              Container(
                height: 150,
                color: SreaColors.surfaceVariant,
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        _videoFailedToLoad
                            ? Icons.error_outline
                            : Icons.videocam_off,
                        size: 48,
                        color: SreaColors.textHint,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _videoFailedToLoad
                            ? 'Video failed to load'
                            : 'No video submitted',
                        style: const TextStyle(color: SreaColors.textHint),
                      ),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 16),

            // ─── Map ──────────────────────────────────────────────────────
            const Text(
              'Location on Map',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: SreaColors.textSecondary,
              ),
            ),
            const SizedBox(height: 6),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: SizedBox(
                height: 220,
                child: FlutterMap(
                  mapController: _mapController,
                  options: MapOptions(
                    initialCenter: report.coordinates,
                    initialZoom: 15,
                  ),
                  children: [
                    TileLayer(
                      urlTemplate:
                          'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                      userAgentPackageName: 'com.example.mobile_user_app',
                    ),
                    MarkerLayer(
                      markers: [
                        Marker(
                          width: 40,
                          height: 40,
                          point: report.coordinates,
                          child: const Icon(
                            Icons.location_pin,
                            color: Colors.red,
                            size: 40,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // ─── Open in Maps button ──────────────────────────────────────
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _openMaps,
                icon: const Icon(Icons.map_outlined, size: 18),
                label: const Text('Open in Maps'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: SreaColors.primary,
                  side: BorderSide(color: SreaColors.primary),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ),
            ),

            // ─── Responder Notes ──────────────────────────────────────────
            if (report.responderNotes != null) ...[
              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 8),
              const Text(
                'Responder Notes',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: SreaColors.textSecondary,
                ),
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: SreaColors.surfaceVariant,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: SreaColors.border),
                ),
                child: Text(
                  report.responderNotes!,
                  style: SreaText.bodyLarge(
                    context,
                  ).copyWith(color: SreaColors.textPrimary),
                ),
              ),
            ],

            // ─── Resolution Notes ──────────────────────────────────────────
            if (report.resolutionNotes != null) ...[
              const SizedBox(height: 16),
              const Divider(),
              const SizedBox(height: 8),
              const Text(
                'Resolution Notes',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: SreaColors.textSecondary,
                ),
              ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: SreaColors.success.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: SreaColors.success.withOpacity(0.3),
                  ),
                ),
                child: Text(
                  report.resolutionNotes!,
                  style: SreaText.bodyLarge(
                    context,
                  ).copyWith(color: SreaColors.textPrimary),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
