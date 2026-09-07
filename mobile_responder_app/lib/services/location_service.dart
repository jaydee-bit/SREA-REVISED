import 'dart:async';
import 'package:geolocator/geolocator.dart';
import 'api_service.dart';

class LocationService {
  static final LocationService _instance = LocationService._internal();
  factory LocationService() => _instance;
  LocationService._internal();

  Timer? _timer;

  Future<void> start() async {
    print('LocationService.start() called');

    // Always start clean — don't trust old timer state, which can get
    // stuck if stop() was ever missed (e.g. app killed mid-response).
    _timer?.cancel();
    _timer = null;

    final hasPermission = await _ensurePermission();
    print('Location permission granted: $hasPermission');
    if (!hasPermission) return;

    _sendLocation();
    _timer = Timer.periodic(const Duration(seconds: 30), (_) => _sendLocation());
  }

  void stop() {
    print('LocationService.stop() called');
    _timer?.cancel();
    _timer = null;
  }

  Future<bool> _ensurePermission() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    print('Location service enabled: $serviceEnabled');
    if (!serviceEnabled) return false;

    var permission = await Geolocator.checkPermission();
    print('Current permission status: $permission');
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      print('Permission after request: $permission');
    }
    return permission == LocationPermission.always ||
        permission == LocationPermission.whileInUse;
  }

  Future<void> _sendLocation() async {
    try {
      print('Attempting to get current position...');
      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );
      print('Got position: ${position.latitude}, ${position.longitude}');
      await ApiService().updateLocation(position.latitude, position.longitude);
      print('Location sent to backend successfully');
    } catch (e) {
      print('Location send failed: $e');
    }
  }
}