import 'dart:async';

/// A tiny in-memory broadcast so screens can react to an incident status
/// change the instant a push arrives, instead of waiting for the user to
/// tap the system notification or pull-to-refresh.
///
/// Usage in a screen that shows an incident or a list of them:
///
/// ```dart
/// StreamSubscription<IncidentUpdate>? _sub;
///
/// @override
/// void initState() {
///   super.initState();
///   _sub = IncidentUpdateBus.instance.stream.listen((update) {
///     // Either refetch the whole list, or — if you're tracking the
///     // uuid already — just patch that one item's status locally.
///     _loadIncidents();
///   });
/// }
///
/// @override
/// void dispose() {
///   _sub?.cancel();
///   super.dispose();
/// }
/// ```
class IncidentUpdateBus {
  IncidentUpdateBus._();
  static final IncidentUpdateBus instance = IncidentUpdateBus._();

  final StreamController<IncidentUpdate> _controller =
      StreamController<IncidentUpdate>.broadcast();

  Stream<IncidentUpdate> get stream => _controller.stream;

  void notify({required String incidentUuid, String? status}) {
    _controller.add(IncidentUpdate(incidentUuid: incidentUuid, status: status));
  }
}

class IncidentUpdate {
  final String incidentUuid;
  final String? status;

  const IncidentUpdate({required this.incidentUuid, this.status});
}