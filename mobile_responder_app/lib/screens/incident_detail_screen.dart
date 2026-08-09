import 'package:flutter/material.dart';
import 'package:srea_shared/srea_shared.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';
import '../models/incident_report_model.dart';
import '../services/api_service.dart';

// ─── Incident Type Constants ──────────────────────────────────────────
const List<String> incidentTypes = [
  'Fire',
  'Medical',
  'Flood',
  'Accident',
  'Calamity',
  'Crime',
  'Traffic',
  'Other',
];

class IncidentDetailScreen extends StatefulWidget {
  final IncidentReport incident;
  const IncidentDetailScreen({super.key, required this.incident});

  @override
  State<IncidentDetailScreen> createState() => _IncidentDetailScreenState();
}

class _IncidentDetailScreenState extends State<IncidentDetailScreen> {
  late IncidentReport _incident;
  late TextEditingController _notesController;
  bool _isUpdating = false;
  String _currentUserRole = 'responder';
  String _currentUserId = '';

  // ─── Video Controller ──────────────────────────────────────────
  VideoPlayerController? _videoController;
  bool _videoFailedToLoad = false;

  String _getFullPhotoUrl() {
    final path = _incident.photoPath;
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

  String? _getFullVideoUrl() {
    final path = _incident.videoPath;
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http')) return path;
    return ApiService().getFullImageUrl(path);
  }

  void _showFullPhoto(String url) {
    showDialog(
      context: context,
      builder: (_) => Dialog(
        backgroundColor: Colors.black,
        insetPadding: EdgeInsets.zero,
        child: GestureDetector(
          onTap: () => Navigator.pop(context),
          child: InteractiveViewer(
            panEnabled: true,
            scaleEnabled: true,
            child: Image.network(
              url,
              fit: BoxFit.contain,
              errorBuilder: (_, __, ___) => const Center(
                child: Icon(Icons.broken_image, size: 60, color: Colors.white),
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ─── Open Maps ──────────────────────────────────────────────────────
  void _openMaps() async {
    final lat = _incident.coordinates.latitude;
    final lng = _incident.coordinates.longitude;

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
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _initializeVideo() {
    final videoUrl = _getFullVideoUrl();
    print('📹 Video URL: $videoUrl');
    if (videoUrl != null && videoUrl.isNotEmpty) {
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
  void initState() {
    super.initState();
    _incident = widget.incident;
    _notesController = TextEditingController(
      text: _incident.responderNotes ?? '',
    );
    _fetchUserRoleAndId();
    _initializeVideo();
  }

  @override
  void dispose() {
    _notesController.dispose();
    _videoController?.dispose();
    super.dispose();
  }

  Future<void> _fetchUserRoleAndId() async {
    try {
      final api = ApiService();
      final user = await api.getUser();
      if (mounted) {
        setState(() {
          _currentUserRole = user['role'] ?? 'responder';
          _currentUserId = user['id']?.toString() ?? '';
        });
      }
    } catch (e) {
      print('Error fetching user data: $e');
    }
  }

  // ─── Perform update with better error handling ─────────────────────
  Future<void> _performUpdate(
    Future<void> Function() apiCall,
    String snackbarMessage,
    Color snackbarColor,
  ) async {
    if (_isUpdating) return;
    setState(() => _isUpdating = true);
    try {
      await apiCall();
      final api = ApiService();
      final data = await api.getIncident(_incident.id);
      final updated = IncidentReport.fromJson(data);
      setState(() {
        _incident = updated;
        _notesController.text = updated.responderNotes ?? '';
        _isUpdating = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(snackbarMessage),
          backgroundColor: snackbarColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (e) {
      setState(() => _isUpdating = false);
      // ✅ Parse actual error from backend
      String errorMessage = 'Action failed. Please try again.';
      if (e.toString().contains('422')) {
        try {
          // Try to extract validation errors from Dio response
          final response = (e as dynamic).response;
          if (response?.data != null && response!.data is Map) {
            final data = response.data as Map;
            if (data['errors'] != null) {
              final errors = data['errors'] as Map;
              final firstError = errors.values.firstOrNull;
              if (firstError is List && firstError.isNotEmpty) {
                errorMessage = firstError.first as String;
              }
            } else if (data['message'] != null) {
              errorMessage = data['message'] as String;
            }
          }
        } catch (_) {
          errorMessage = 'Validation error. Please check your inputs.';
        }
      } else if (e.toString().contains('403')) {
        errorMessage = 'You do not have permission to perform this action.';
      } else if (e.toString().contains('404')) {
        errorMessage = 'Incident not found. It may have been deleted.';
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(errorMessage),
          backgroundColor: SreaColors.error,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
        ),
      );
    }
  }

  // ─── All bottom sheet dialogs ───────────────────────────────────────

  void _showRespondBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: SreaRadius.bottomSheet),
      builder: (context) => Padding(
        padding: SreaSpacing.bottomSheetPadding(context),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const _DragHandle(),
            const SizedBox(height: 8),
            Text(
              'Respond to Incident',
              style: SreaText.titleLarge(
                context,
              ).copyWith(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              'You are about to take responsibility for this incident.',
              style: SreaText.bodySmall(
                context,
              ).copyWith(color: SreaColors.textSecondary),
            ),
            const SizedBox(height: 24),
            Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                SreaButton(
                  label: 'Confirm Respond',
                  onPressed: () async {
                    Navigator.pop(context);
                    await _performUpdate(
                      () => ApiService().respondToIncident(
                        _incident.id,
                        _currentUserId,
                      ),
                      'You are now assigned to this incident',
                      SreaColors.primary,
                    );
                  },
                  type: SreaButtonType.primary,
                ),
                const SizedBox(height: 12),
                SreaButton.outline(
                  label: 'Cancel',
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showReassignBottomSheet() {
    String? selectedReason;
    final otherController = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: SreaRadius.bottomSheet),
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: SreaSpacing.bottomSheetPadding(context),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const _DragHandle(),
              const SizedBox(height: 8),
              Text(
                'Reassign Incident',
                style: SreaText.titleLarge(
                  context,
                ).copyWith(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 4),
              Text(
                'Select a reason for reassigning this incident.',
                style: SreaText.bodySmall(
                  context,
                ).copyWith(color: SreaColors.textSecondary),
              ),
              const SizedBox(height: 20),
              SreaRadioGroup<String>(
                groupValue: selectedReason,
                onChanged: (value) =>
                    setSheetState(() => selectedReason = value),
                options: const [
                  SreaRadioItem(
                    value: 'Hands full — please assign to another responder',
                    label: 'Hands full — please assign to another responder',
                  ),
                  SreaRadioItem(
                    value: 'Outside my jurisdiction or barangay',
                    label: 'Outside my jurisdiction or barangay',
                  ),
                  SreaRadioItem(
                    value: 'Requires additional resources or authority',
                    label: 'Requires additional resources or authority',
                  ),
                  SreaRadioItem(
                    value: 'Duplicate or related to another active incident',
                    label: 'Duplicate or related to another active incident',
                  ),
                  SreaRadioItem(value: 'Other', label: 'Other'),
                ],
              ),
              if (selectedReason == 'Other') ...[
                const SizedBox(height: 12),
                SreaTextField(
                  label: 'Please specify',
                  hint: 'Enter reason...',
                  controller: otherController,
                  onChanged: (_) => setSheetState(() {}),
                ),
              ],
              const SizedBox(height: 24),
              Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  ElevatedButton(
                    onPressed:
                        selectedReason != null &&
                            (selectedReason != 'Other' ||
                                otherController.text.trim().isNotEmpty)
                        ? () async {
                            final reason = selectedReason == 'Other'
                                ? otherController.text.trim()
                                : selectedReason!;
                            Navigator.pop(context);
                            await _performUpdate(
                              () => ApiService().reassignIncident(
                                _incident.id,
                                reason,
                              ),
                              'Incident has been reassigned to admin',
                              SreaColors.high,
                            );
                          }
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: SreaColors.high,
                      foregroundColor: SreaColors.textOnPrimary,
                      shape: RoundedRectangleBorder(
                        borderRadius: SreaRadius.button,
                      ),
                      padding: SreaSpacing.buttonPadding(context),
                      minimumSize: const Size(0, 48),
                    ),
                    child: Text(
                      'Confirm Reassign',
                      style: SreaText.label(
                        context,
                      ).copyWith(fontWeight: FontWeight.w700),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SreaButton.outline(
                    label: 'Cancel',
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Resolve Dialog with Inline Validation ─────────────────────────
  void _showResolveBottomSheet() {
    final descriptionController = TextEditingController();
    final notesController = TextEditingController();
    final reporterNameController = TextEditingController();
    String selectedType = incidentTypes.first;
    final _formKey = GlobalKey<FormState>();
    bool _autovalidate = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: SreaRadius.bottomSheet),
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
          ),
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Form(
              key: _formKey,
              autovalidateMode: _autovalidate
                  ? AutovalidateMode.always
                  : AutovalidateMode.disabled,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.check_circle_outline,
                        color: SreaColors.success,
                      ),
                      const SizedBox(width: 12),
                      Text(
                        'Resolve Incident',
                        style: SreaText.titleLarge(
                          context,
                        ).copyWith(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // ─── Incident Type ──────────────────────────────────────
                  Text(
                    'Incident Type *',
                    style: SreaText.label(context).copyWith(
                      color: SreaColors.textSecondary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    decoration: BoxDecoration(
                      border: Border.all(color: SreaColors.border),
                      borderRadius: SreaRadius.card,
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: selectedType,
                        isExpanded: true,
                        items: incidentTypes.map((type) {
                          return DropdownMenuItem(
                            value: type,
                            child: Text(type),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setSheetState(() => selectedType = value!);
                        },
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // ─── Description ──────────────────────────────────────────
                  Text(
                    'Description * (min 10 characters)',
                    style: SreaText.label(context).copyWith(
                      color: SreaColors.textSecondary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: descriptionController,
                    maxLines: 4,
                    decoration: InputDecoration(
                      hintText: 'What happened? Provide a clear summary...',
                      border: OutlineInputBorder(borderRadius: SreaRadius.card),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().length < 10) {
                        return 'Description must be at least 10 characters.';
                      }
                      return null;
                    },
                    onChanged: (_) {
                      if (_autovalidate) setSheetState(() {});
                    },
                  ),
                  const SizedBox(height: 16),

                  // ─── Resolution Notes ──────────────────────────────────────
                  Text(
                    'Resolution Notes * (min 10 characters)',
                    style: SreaText.label(context).copyWith(
                      color: SreaColors.textSecondary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: notesController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'Actions taken by the responder...',
                      border: OutlineInputBorder(borderRadius: SreaRadius.card),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().length < 10) {
                        return 'Resolution notes must be at least 10 characters.';
                      }
                      return null;
                    },
                    onChanged: (_) {
                      if (_autovalidate) setSheetState(() {});
                    },
                  ),
                  const SizedBox(height: 16),

                  // ─── Reporter Name ──────────────────────────────────────────
                  Text(
                    'Reporter Name (optional)',
                    style: SreaText.label(context).copyWith(
                      color: SreaColors.textSecondary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: reporterNameController,
                    decoration: InputDecoration(
                      hintText: 'If the reporter provides their name...',
                      prefixIcon: Icon(
                        Icons.person_outline,
                        color: SreaColors.textHint,
                      ),
                      border: OutlineInputBorder(borderRadius: SreaRadius.card),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ─── Buttons ─────────────────────────────────────────────
                  Row(
                    children: [
                      Expanded(
                        child: SreaButton.outline(
                          label: 'Cancel',
                          onPressed: () => Navigator.pop(context),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: SreaButton(
                          label: 'Resolve',
                          onPressed: () {
                            // Trigger validation
                            setSheetState(() => _autovalidate = true);
                            if (_formKey.currentState!.validate()) {
                              final description = descriptionController.text
                                  .trim();
                              final notes = notesController.text.trim();
                              final reporterName =
                                  reporterNameController.text.trim().isNotEmpty
                                  ? reporterNameController.text.trim()
                                  : null;

                              Navigator.pop(context);
                              _performUpdate(
                                () => ApiService().resolveIncident(
                                  uuid: _incident.id,
                                  type: selectedType,
                                  description: description,
                                  resolutionNotes: notes,
                                  reporterName: reporterName,
                                ),
                                'Incident resolved successfully',
                                SreaColors.success,
                              );
                            }
                          },
                          type: SreaButtonType.primary,
                          icon: Icons.check_circle,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ─── Reject Dialog ──────────────────────────────────────────────────
  void _showRejectDialog() {
    final reasonController = TextEditingController();
    final _formKey = GlobalKey<FormState>();
    bool _autovalidate = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: SreaRadius.bottomSheet),
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
          ),
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Form(
              key: _formKey,
              autovalidateMode: _autovalidate
                  ? AutovalidateMode.always
                  : AutovalidateMode.disabled,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.block, color: SreaColors.error),
                      const SizedBox(width: 12),
                      Text(
                        'Reject Incident',
                        style: SreaText.titleLarge(
                          context,
                        ).copyWith(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Only Administrators can reject reports. Provide a reason.',
                    style: SreaText.bodySmall(
                      context,
                    ).copyWith(color: SreaColors.textSecondary),
                  ),
                  const SizedBox(height: 20),

                  // ─── Rejection Reason ──────────────────────────────────────
                  Text(
                    'Rejection Reason * (min 10 characters)',
                    style: SreaText.label(context).copyWith(
                      color: SreaColors.textSecondary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: reasonController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'e.g., Duplicate report, false alarm...',
                      border: OutlineInputBorder(borderRadius: SreaRadius.card),
                    ),
                    validator: (value) {
                      if (value == null || value.trim().length < 10) {
                        return 'Rejection reason must be at least 10 characters.';
                      }
                      return null;
                    },
                    onChanged: (_) {
                      if (_autovalidate) setSheetState(() {});
                    },
                  ),
                  const SizedBox(height: 24),

                  // ─── Buttons ─────────────────────────────────────────────
                  Row(
                    children: [
                      Expanded(
                        child: SreaButton.outline(
                          label: 'Cancel',
                          onPressed: () => Navigator.pop(context),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: SreaButton(
                          label: 'Confirm Reject',
                          onPressed: () {
                            setSheetState(() => _autovalidate = true);
                            if (_formKey.currentState!.validate()) {
                              final reason = reasonController.text.trim();
                              Navigator.pop(context);
                              _performUpdate(
                                () => ApiService().rejectIncident(
                                  uuid: _incident.id,
                                  reason: reason,
                                ),
                                'Incident rejected successfully',
                                SreaColors.error,
                              );
                            }
                          },
                          type: SreaButtonType.primary,
                          icon: Icons.block,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _showAddEditNotesBottomSheet() {
    final notesController = TextEditingController(text: _notesController.text);
    bool canSave = notesController.text.trim().isNotEmpty;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: SreaRadius.bottomSheet),
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: SreaSpacing.bottomSheetPadding(context),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const _DragHandle(),
              const SizedBox(height: 8),
              Text(
                _incident.responderNotes == null ? 'Add Notes' : 'Edit Notes',
                style: SreaText.titleLarge(
                  context,
                ).copyWith(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 4),
              Text(
                'Add internal notes. These are only visible to responders and admins.',
                style: SreaText.bodySmall(
                  context,
                ).copyWith(color: SreaColors.textSecondary),
              ),
              const SizedBox(height: 20),
              SreaTextField(
                label: 'Responder Notes',
                hint: 'Enter notes...',
                controller: notesController,
                maxLines: 4,
                required: true,
                onChanged: (_) => setSheetState(
                  () => canSave = notesController.text.trim().isNotEmpty,
                ),
              ),
              const SizedBox(height: 24),
              Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  SreaButton(
                    label: 'Save Notes',
                    onPressed: canSave
                        ? () async {
                            Navigator.pop(context);
                            await _performUpdate(
                              () => ApiService().addResponderNotes(
                                _incident.id,
                                notesController.text.trim(),
                              ),
                              'Notes saved successfully',
                              SreaColors.primary,
                            );
                          }
                        : null,
                    type: SreaButtonType.primary,
                    icon: Icons.note_add_rounded,
                  ),
                  const SizedBox(height: 12),
                  SreaButton.outline(
                    label: 'Cancel',
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Action Buttons ──────────────────────────────────────────────────
  Widget _buildActionButtons(BuildContext context) {
    final status = _incident.status;
    final isAdmin = _currentUserRole == 'admin';
    final isAssigned =
        _incident.assignedToId == _currentUserId && _currentUserId.isNotEmpty;

    if (status == 'Resolved') {
      return Container(
        padding: SreaSpacing.cardPaddingSmall(context),
        decoration: BoxDecoration(
          color: SreaColors.lowBg,
          borderRadius: SreaRadius.card,
          border: Border.all(color: SreaColors.low.withOpacity(0.4)),
        ),
        child: Row(
          children: [
            Icon(
              Icons.check_circle_outline_rounded,
              color: SreaColors.low,
              size: 24,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Incident Resolved',
                    style: SreaText.bodySmall(context).copyWith(
                      color: SreaColors.low,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text(
                    'This incident has been marked as resolved. No further action is required.',
                    style: SreaText.label(
                      context,
                    ).copyWith(color: SreaColors.low),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    if (status == 'Rejected') {
      return Container(
        padding: SreaSpacing.cardPaddingSmall(context),
        decoration: BoxDecoration(
          color: SreaColors.error.withOpacity(0.08),
          borderRadius: SreaRadius.card,
          border: Border.all(color: SreaColors.error.withOpacity(0.4)),
        ),
        child: Row(
          children: [
            Icon(Icons.block, color: SreaColors.error, size: 24),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Incident Rejected',
                    style: SreaText.bodySmall(context).copyWith(
                      color: SreaColors.error,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text(
                    'This incident has been rejected by an administrator.',
                    style: SreaText.label(
                      context,
                    ).copyWith(color: SreaColors.error),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    if (status == 'Escalated') {
      return Container(
        padding: SreaSpacing.cardPaddingSmall(context),
        decoration: BoxDecoration(
          color: SreaColors.highBg,
          borderRadius: SreaRadius.card,
          border: Border.all(color: SreaColors.high.withOpacity(0.4)),
        ),
        child: Row(
          children: [
            Icon(Icons.info_outline_rounded, color: SreaColors.high, size: 24),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Incident Reassigned',
                    style: SreaText.bodySmall(context).copyWith(
                      color: SreaColors.high,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text(
                    'This incident has been reassigned to admin. Waiting for a new responder.',
                    style: SreaText.label(
                      context,
                    ).copyWith(color: SreaColors.high),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    final showRespond = status == 'Pending';
    final showResolve = status == 'Responding' && (isAssigned || isAdmin);

    return Column(
      children: [
        if (showRespond || showResolve)
          Row(
            children: [
              Expanded(
                child: showRespond
                    ? SreaButton(
                        label: 'Respond',
                        onPressed: _isUpdating ? null : _showRespondBottomSheet,
                        type: SreaButtonType.primary,
                        icon: Icons.assignment_turned_in_rounded,
                      )
                    : SreaButton(
                        label: 'Resolve',
                        onPressed: _isUpdating ? null : _showResolveBottomSheet,
                        type: SreaButtonType.primary,
                        icon: Icons.check_circle_outline_rounded,
                      ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed: _isUpdating ? null : _showReassignBottomSheet,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: SreaColors.high,
                    foregroundColor: SreaColors.textOnPrimary,
                    shape: RoundedRectangleBorder(
                      borderRadius: SreaRadius.button,
                    ),
                    padding: SreaSpacing.buttonPadding(context),
                    minimumSize: const Size(0, 48),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.swap_horiz_rounded, size: 20),
                      const SizedBox(width: 8),
                      Text(
                        'Reassign',
                        style: SreaText.label(
                          context,
                        ).copyWith(fontWeight: FontWeight.w700),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

        if (!showRespond &&
            !showResolve &&
            status == 'Responding' &&
            !isAssigned &&
            !isAdmin)
          Container(
            padding: const EdgeInsets.all(12),
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(
              color: SreaColors.surfaceVariant,
              borderRadius: SreaRadius.card,
              border: Border.all(color: SreaColors.border),
            ),
            child: Row(
              children: [
                Icon(Icons.info_outline_rounded, color: SreaColors.textHint),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'This incident is being handled by another responder. You can still add notes.',
                    style: SreaText.bodySmall(context).copyWith(
                      color: SreaColors.textSecondary,
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ),
              ],
            ),
          ),

        const SizedBox(height: 12),

        Row(
          children: [
            if (isAdmin) ...[
              Expanded(
                child: ElevatedButton(
                  onPressed: _isUpdating ? null : _showRejectDialog,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: SreaColors.error,
                    foregroundColor: SreaColors.textOnPrimary,
                    shape: RoundedRectangleBorder(
                      borderRadius: SreaRadius.button,
                    ),
                    padding: SreaSpacing.buttonPadding(context),
                    minimumSize: const Size(0, 48),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.block, size: 20),
                      const SizedBox(width: 8),
                      Text(
                        'Reject',
                        style: SreaText.label(
                          context,
                        ).copyWith(fontWeight: FontWeight.w700),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),
            ],
            Expanded(
              child: SreaButton.outline(
                label: 'Add Notes',
                onPressed: _isUpdating ? null : _showAddEditNotesBottomSheet,
                fullWidth: true,
                icon: Icons.note_add_rounded,
              ),
            ),
          ],
        ),
      ],
    );
  }

  // ─── Build ──────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final fullPhotoUrl = _getFullPhotoUrl();
    final hasPhoto = fullPhotoUrl.isNotEmpty;
    final videoUrl = _getFullVideoUrl();
    final hasVideo = videoUrl != null && videoUrl.isNotEmpty;
    final isAnonymous = _incident.reporterName == 'Anonymous';
    final showVideo =
        _videoController != null && _videoController!.value.isInitialized;

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
        actions: [
          if (_isUpdating)
            const Padding(
              padding: EdgeInsets.all(16),
              child: SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: Colors.white,
                ),
              ),
            ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ─── Potential Duplicate Banner ──────────────────────────
              if (_incident.isPotentialDuplicate &&
                  _incident.potentialDuplicateCount > 0)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: SreaColors.warning.withOpacity(0.12),
                    borderRadius: SreaRadius.card,
                    border: Border.all(
                      color: SreaColors.warning.withOpacity(0.3),
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.warning_amber_rounded,
                        color: SreaColors.warning,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          '⚠️ Potential duplicate: ${_incident.potentialDuplicateCount} similar report(s) nearby (same barangay, within 100m & 15 min).',
                          style: SreaText.bodySmall(context).copyWith(
                            color: SreaColors.warning,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

              // ─── Escalated by (if escalated) ──────────────────────────
              if (_incident.status == 'Escalated' &&
                  _incident.escalatedByName != null) ...[
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: SreaColors.highBg,
                    borderRadius: SreaRadius.pill,
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.person_outline,
                        size: 14,
                        color: SreaColors.high,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Escalated by: ${_incident.escalatedByName}',
                        style: SreaText.label(context).copyWith(
                          color: SreaColors.high,
                          fontWeight: FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 8),
              ],

              // ─── Row: Type + Status ──────────────────────────────────
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      _incident.type,
                      style: SreaText.headlineSmall(
                        context,
                      ).copyWith(fontWeight: FontWeight.w800),
                    ),
                  ),
                  SreaBadge(
                    type: _statusToBadgeType(_incident.status),
                    label: _incident.status,
                    showDot: true,
                  ),
                ],
              ),
              const SizedBox(height: 4),

              // ─── Reporter ──────────────────────────────────────────────
              Row(
                children: [
                  Text(
                    'Reported by: ',
                    style: SreaText.bodySmall(
                      context,
                    ).copyWith(color: SreaColors.textSecondary),
                  ),
                  Text(
                    _incident.reporterName,
                    style: SreaText.bodySmall(context).copyWith(
                      color: isAnonymous
                          ? SreaColors.textHint
                          : SreaColors.textPrimary,
                      fontWeight: isAnonymous
                          ? FontWeight.w400
                          : FontWeight.w600,
                      fontStyle: isAnonymous
                          ? FontStyle.italic
                          : FontStyle.normal,
                    ),
                  ),
                  if (isAnonymous) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: SreaColors.textHint.withOpacity(0.12),
                        borderRadius: SreaRadius.pill,
                      ),
                      child: Text(
                        'Anonymous',
                        style: SreaText.label(context).copyWith(
                          color: SreaColors.textHint,
                          fontSize: 9,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
              const SizedBox(height: 8),

              // ─── Barangay ─────────────────────────────────────────────
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 8,
                ),
                decoration: BoxDecoration(
                  color: SreaColors.primaryLight,
                  borderRadius: SreaRadius.input,
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      size: 16,
                      color: SreaColors.primary,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        '${_incident.barangay}${_incident.locationDetails != null ? ' • ${_incident.locationDetails}' : ''}',
                        style: SreaText.bodySmall(context).copyWith(
                          color: SreaColors.primary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),

              // ─── Reported At ──────────────────────────────────────────
              Row(
                children: [
                  Icon(
                    Icons.calendar_today_outlined,
                    size: 14,
                    color: SreaColors.textHint,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    _formatDate(_incident.reportedAt),
                    style: SreaText.bodySmall(
                      context,
                    ).copyWith(color: SreaColors.textHint),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // ─── Description ──────────────────────────────────────────
              Text(
                'Description',
                style: SreaText.bodyLarge(
                  context,
                ).copyWith(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              Text(
                _incident.description,
                style: SreaText.bodySmall(
                  context,
                ).copyWith(color: SreaColors.textSecondary, height: 1.6),
              ),

              // ─── Photo ───────────────────────────────────────────────
              if (hasPhoto) ...[
                const SizedBox(height: 20),
                Text(
                  'Photo',
                  style: SreaText.bodyLarge(
                    context,
                  ).copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                GestureDetector(
                  onTap: () => _showFullPhoto(fullPhotoUrl),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(SreaRadius.md),
                    child: Image.network(
                      fullPhotoUrl,
                      height: 200,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      loadingBuilder: (context, child, loadingProgress) {
                        if (loadingProgress == null) return child;
                        return Container(
                          height: 200,
                          color: SreaColors.surfaceVariant,
                          child: const Center(
                            child: CircularProgressIndicator(),
                          ),
                        );
                      },
                      errorBuilder: (_, __, ___) => Container(
                        height: 200,
                        color: SreaColors.surfaceVariant,
                        child: const Center(
                          child: Icon(
                            Icons.broken_image,
                            size: 60,
                            color: SreaColors.textHint,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ],

              // ─── Video ───────────────────────────────────────────────
              if (hasVideo) ...[
                const SizedBox(height: 20),
                Text(
                  'Video Recording',
                  style: SreaText.bodyLarge(
                    context,
                  ).copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                if (showVideo)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(SreaRadius.md),
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
              ],

              const SizedBox(height: 20),

              // ─── Map ──────────────────────────────────────────────────
              Text(
                'Location on Map',
                style: SreaText.bodyLarge(
                  context,
                ).copyWith(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              Container(
                height: 200,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(SreaRadius.md),
                  border: Border.all(color: SreaColors.border),
                ),
                child: GestureDetector(
                  onTap: _openMaps,
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(SreaRadius.md),
                    child: FlutterMap(
                      options: MapOptions(
                        initialCenter: _incident.coordinates,
                        initialZoom: 15,
                        interactionOptions: const InteractionOptions(
                          flags:
                              InteractiveFlag.pinchZoom | InteractiveFlag.drag,
                        ),
                      ),
                      children: [
                        TileLayer(
                          urlTemplate:
                              'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                          userAgentPackageName: 'com.example.responder_app',
                        ),
                        MarkerLayer(
                          markers: [
                            Marker(
                              width: 40,
                              height: 40,
                              point: _incident.coordinates,
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
              ),
              const SizedBox(height: 8),
              Text(
                _incident.address,
                style: SreaText.label(
                  context,
                ).copyWith(color: SreaColors.textHint),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 12),

              // ─── Open in Maps Button ──────────────────────────────────
              OutlinedButton.icon(
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
                  minimumSize: const Size(double.infinity, 48),
                ),
              ),
              const SizedBox(height: 20),

              // ─── Action Buttons ─────────────────────────────────────
              _buildActionButtons(context),
              const SizedBox(height: 20),

              // ─── Responder Notes ─────────────────────────────────────
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Responder Notes',
                    style: SreaText.bodyLarge(
                      context,
                    ).copyWith(fontWeight: FontWeight.w700),
                  ),
                  if (![
                    'Resolved',
                    'Rejected',
                    'Escalated',
                  ].contains(_incident.status))
                    IconButton(
                      icon: const Icon(
                        Icons.edit_outlined,
                        color: SreaColors.primary,
                      ),
                      onPressed: _showAddEditNotesBottomSheet,
                    ),
                ],
              ),
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: SreaSpacing.cardPaddingSmall(context),
                decoration: BoxDecoration(
                  color: SreaColors.surfaceVariant,
                  borderRadius: SreaRadius.card,
                  border: Border.all(color: SreaColors.border),
                ),
                child: _notesController.text.trim().isEmpty
                    ? Text(
                        'No notes added yet.',
                        style: SreaText.bodySmall(context).copyWith(
                          color: SreaColors.textHint,
                          fontStyle: FontStyle.italic,
                        ),
                      )
                    : Text(
                        _notesController.text,
                        style: SreaText.bodySmall(
                          context,
                        ).copyWith(color: SreaColors.textPrimary),
                      ),
              ),

              // ─── Resolution Notes ────────────────────────────────────
              if (_incident.resolutionNotes != null) ...[
                const SizedBox(height: 20),
                Text(
                  'Resolution Notes',
                  style: SreaText.bodyLarge(
                    context,
                  ).copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                Container(
                  width: double.infinity,
                  padding: SreaSpacing.cardPaddingSmall(context),
                  decoration: BoxDecoration(
                    color: SreaColors.lowBg,
                    borderRadius: SreaRadius.card,
                    border: Border.all(color: SreaColors.low.withOpacity(0.4)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _incident.resolutionNotes!,
                        style: SreaText.bodySmall(
                          context,
                        ).copyWith(color: SreaColors.textPrimary),
                      ),
                      if (_incident.resolvedAt != null) ...[
                        const SizedBox(height: 8),
                        Text(
                          'Resolved on: ${_formatDate(_incident.resolvedAt!)}',
                          style: SreaText.label(
                            context,
                          ).copyWith(color: SreaColors.textHint),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 32),

              // ─── Contact MDRRMO ──────────────────────────────────────
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: SreaColors.primaryLight,
                  borderRadius: SreaRadius.input,
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.phone_in_talk_rounded,
                      size: 20,
                      color: SreaColors.primary,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'For assistance, contact MDRRMO',
                            style: SreaText.bodySmall(context).copyWith(
                              fontWeight: FontWeight.w700,
                              color: SreaColors.primary,
                            ),
                          ),
                          Text(
                            '(044) 123-4567',
                            style: SreaText.label(
                              context,
                            ).copyWith(color: SreaColors.primary),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── ✅ FIXED: _formatDate now includes time and converts to local ───
  String _formatDate(DateTime date) {
    final localDate = date.toLocal();
    final month = _monthAbbr(localDate.month);
    final day = localDate.day;
    final year = localDate.year;
    int hour = localDate.hour;
    final minute = localDate.minute.toString().padLeft(2, '0');
    final amPm = hour >= 12 ? 'PM' : 'AM';
    if (hour > 12) hour -= 12;
    if (hour == 0) hour = 12;
    return '$month $day, $year at $hour:$minute $amPm';
  }

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

class _DragHandle extends StatelessWidget {
  const _DragHandle();
  @override
  Widget build(BuildContext context) => Container(
    width: 40,
    height: 4,
    decoration: BoxDecoration(
      color: SreaColors.border,
      borderRadius: SreaRadius.pill,
    ),
  );
}
