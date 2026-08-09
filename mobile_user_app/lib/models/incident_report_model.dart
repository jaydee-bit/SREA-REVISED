import 'package:latlong2/latlong.dart';

class IncidentReport {
  final String id;
  final String type;
  final String description;
  final String? photoPath;
  final String? videoPath;
  final String barangay;
  final String? locationDetails;
  final LatLng coordinates;
  final String address;
  final String status;
  final DateTime reportedAt;
  final String? assignedToName;
  final String? reporterName;
  final String? responderNotes;
  final String? escalationReason;
  final String? escalatedBy;
  final String? escalatedByName; // ✅ NEW – Name of who escalated
  final DateTime? escalatedAt;
  final String? resolutionNotes;
  final DateTime? resolvedAt;
  final bool isPotentialDuplicate;
  final int potentialDuplicateCount;

  IncidentReport({
    required this.id,
    required this.type,
    required this.description,
    this.photoPath,
    this.videoPath,
    required this.barangay,
    this.locationDetails,
    required this.coordinates,
    required this.address,
    required this.status,
    required this.reportedAt,
    this.assignedToName,
    this.reporterName,
    this.responderNotes,
    this.escalationReason,
    this.escalatedBy,
    this.escalatedByName, // ✅ NEW
    this.escalatedAt,
    this.resolutionNotes,
    this.resolvedAt,
    this.isPotentialDuplicate = false,
    this.potentialDuplicateCount = 0,
  });

  factory IncidentReport.fromJson(Map<String, dynamic> json) {
    // ✅ Parse escalated by name
    String? escalatedByName;
    if (json['escalated_by'] != null) {
      if (json['escalated_by'] is Map<String, dynamic>) {
        escalatedByName = json['escalated_by']['name']?.toString();
      } else {
        escalatedByName = json['escalated_by_name']?.toString();
      }
    }

    return IncidentReport(
      id: json['uuid'] ?? json['id'].toString(),
      type: json['type'] ?? 'Emergency',
      description: json['description'] ?? '',
      photoPath: json['photo_path'],
      videoPath: json['video_path'],
      barangay: json['barangay'] ?? '',
      locationDetails: json['location_details'],
      coordinates: LatLng(
        double.parse(json['latitude']?.toString() ?? '0'),
        double.parse(json['longitude']?.toString() ?? '0'),
      ),
      address: json['address'] ?? '',
      status: json['status'] ?? 'Pending',
      reportedAt: DateTime.parse(json['reported_at']),
      assignedToName: json['assigned_to']?['name'] ?? null,
      reporterName: json['reporter_name'] ?? null,
      responderNotes: json['responder_notes'],
      escalationReason: json['escalation_reason'],
      escalatedBy: json['escalated_by']?.toString(),
      escalatedByName: escalatedByName, // ✅ NEW
      escalatedAt: json['escalated_at'] != null
          ? DateTime.parse(json['escalated_at'])
          : null,
      resolutionNotes: json['resolution_notes'],
      resolvedAt: json['resolved_at'] != null
          ? DateTime.parse(json['resolved_at'])
          : null,
      isPotentialDuplicate: json['is_potential_duplicate'] ?? false,
      potentialDuplicateCount: json['potential_duplicate_count'] ?? 0,
    );
  }
}
