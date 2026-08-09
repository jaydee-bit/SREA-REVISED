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
  final String? reporterName; // ✅ Added – from reporter_name column
  final String? responderNotes;
  final String? escalationReason;
  final String? escalatedBy;
  final DateTime? escalatedAt;
  final String? resolutionNotes;
  final DateTime? resolvedAt;

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
    this.reporterName, // ✅ Added
    this.responderNotes,
    this.escalationReason,
    this.escalatedBy,
    this.escalatedAt,
    this.resolutionNotes,
    this.resolvedAt,
  });

  factory IncidentReport.fromJson(Map<String, dynamic> json) {
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
      reporterName: json['reporter_name'] ?? null, // ✅ Parse from column
      responderNotes: json['responder_notes'],
      escalationReason: json['escalation_reason'],
      escalatedBy: json['escalated_by']?.toString(),
      escalatedAt: json['escalated_at'] != null
          ? DateTime.parse(json['escalated_at'])
          : null,
      resolutionNotes: json['resolution_notes'],
      resolvedAt: json['resolved_at'] != null
          ? DateTime.parse(json['resolved_at'])
          : null,
    );
  }
}
