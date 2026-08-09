import 'package:latlong2/latlong.dart';

class IncidentReport {
  final String id; // UUID (string)
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
  final String reporterName;
  final String? assignedToId;
  final String? responderNotes;
  final String? escalationReason;
  final String? escalatedBy;
  final String? escalatedAt;
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
    required this.reporterName,
    this.assignedToId,
    this.responderNotes,
    this.escalationReason,
    this.escalatedBy,
    this.escalatedAt,
    this.resolutionNotes,
    this.resolvedAt,
    this.isPotentialDuplicate = false,
    this.potentialDuplicateCount = 0,
  });

  factory IncidentReport.fromJson(Map<String, dynamic> json) {
    // Handle anonymous reporter
    String name = 'Anonymous';
    if (json['reporter'] != null && json['reporter'] is Map<String, dynamic>) {
      name = json['reporter']['name'] ?? 'Anonymous';
    } else if (json['reporterName'] != null) {
      name = json['reporterName'] as String;
    }

    String? assignedId;
    if (json['assigned_to'] != null) {
      if (json['assigned_to'] is Map<String, dynamic>) {
        assignedId = json['assigned_to']['id']?.toString();
      } else {
        assignedId = json['assigned_to'].toString();
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
        _parseCoordinate(json['latitude']),
        _parseCoordinate(json['longitude']),
      ),
      address: json['address'] ?? '',
      status: json['status'] ?? 'Pending',
      reportedAt: json['reported_at'] != null
          ? DateTime.parse(json['reported_at'])
          : DateTime.now(),
      reporterName: name,
      assignedToId: assignedId,
      responderNotes: json['responder_notes'],
      escalationReason: json['escalation_reason'],
      escalatedBy: json['escalated_by']?.toString(),
      escalatedAt: json['escalated_at'],
      resolutionNotes: json['resolution_notes'],
      resolvedAt: json['resolved_at'] != null
          ? DateTime.parse(json['resolved_at'])
          : null,
      isPotentialDuplicate: json['is_potential_duplicate'] ?? false,
      potentialDuplicateCount: json['potential_duplicate_count'] ?? 0,
    );
  }

  static double _parseCoordinate(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'description': description,
      'photo_path': photoPath,
      'video_path': videoPath,
      'barangay': barangay,
      'location_details': locationDetails,
      'latitude': coordinates.latitude,
      'longitude': coordinates.longitude,
      'address': address,
      'status': status,
      'reported_at': reportedAt.toIso8601String(),
      'reporterName': reporterName,
      'assigned_to': assignedToId,
      'responder_notes': responderNotes,
      'escalation_reason': escalationReason,
      'escalated_by': escalatedBy,
      'escalated_at': escalatedAt,
      'resolution_notes': resolutionNotes,
      'resolved_at': resolvedAt?.toIso8601String(),
      'is_potential_duplicate': isPotentialDuplicate,
      'potential_duplicate_count': potentialDuplicateCount,
    };
  }

  IncidentReport copyWith({
    String? id,
    String? type,
    String? description,
    String? photoPath,
    String? videoPath,
    String? barangay,
    String? locationDetails,
    LatLng? coordinates,
    String? address,
    String? status,
    DateTime? reportedAt,
    String? reporterName,
    String? assignedToId,
    String? responderNotes,
    String? escalationReason,
    String? escalatedBy,
    String? escalatedAt,
    String? resolutionNotes,
    DateTime? resolvedAt,
    bool? isPotentialDuplicate,
    int? potentialDuplicateCount,
  }) {
    return IncidentReport(
      id: id ?? this.id,
      type: type ?? this.type,
      description: description ?? this.description,
      photoPath: photoPath ?? this.photoPath,
      videoPath: videoPath ?? this.videoPath,
      barangay: barangay ?? this.barangay,
      locationDetails: locationDetails ?? this.locationDetails,
      coordinates: coordinates ?? this.coordinates,
      address: address ?? this.address,
      status: status ?? this.status,
      reportedAt: reportedAt ?? this.reportedAt,
      reporterName: reporterName ?? this.reporterName,
      assignedToId: assignedToId ?? this.assignedToId,
      responderNotes: responderNotes ?? this.responderNotes,
      escalationReason: escalationReason ?? this.escalationReason,
      escalatedBy: escalatedBy ?? this.escalatedBy,
      escalatedAt: escalatedAt ?? this.escalatedAt,
      resolutionNotes: resolutionNotes ?? this.resolutionNotes,
      resolvedAt: resolvedAt ?? this.resolvedAt,
      isPotentialDuplicate: isPotentialDuplicate ?? this.isPotentialDuplicate,
      potentialDuplicateCount:
          potentialDuplicateCount ?? this.potentialDuplicateCount,
    );
  }
}
