import 'package:dio/dio.dart';
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:path_provider/path_provider.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:cross_file/cross_file.dart';

class ApiService {
  static const String baseImageUrl = 'http://localhost:8080';
  static const String baseUrl = '$baseImageUrl/api';

  final Dio _dio = Dio(
    BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
    ),
  );

  ApiService() {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          // No token needed – public API
          options.headers['Accept'] = 'application/json';
          return handler.next(options);
        },
        onError: (error, handler) {
          return handler.next(error);
        },
      ),
    );
  }

  // ─── Public data ──────────────────────────────────────────────────────

  Future<List<dynamic>> getAlerts() async {
    final response = await _dio.get('/user/alerts');
    return response.data;
  }

  Future<List<dynamic>> getAnnouncements() async {
    final response = await _dio.get('/user/announcements');
    return response.data;
  }

  Future<List<dynamic>> getTrafficAdvisories() async {
    final response = await _dio.get('/user/traffic');
    return response.data;
  }

  // ─── Emergency report (public) ────────────────────────────────────────

  Future<Map<String, dynamic>> createEmergencyReport(
    Map<String, dynamic> data,
  ) async {
    final response = await _dio.post('/public/incidents', data: data);
    return response.data;
  }

  // ─── Fetch a single incident by UUID (public) ──────────────────────

  Future<Map<String, dynamic>> getIncidentById(String id) async {
    final response = await _dio.get('/public/incidents/$id');
    return response.data;
  }

  // ─── Media uploads (public) ─────────────────────────────────────────

  Future<String> uploadReporterMedia(File file) async {
    String fileName =
        'reporter_${DateTime.now().millisecondsSinceEpoch}.${file.path.split('.').last}';
    FormData formData = FormData.fromMap({
      'media': await MultipartFile.fromFile(file.path, filename: fileName),
    });
    final response = await _dio.post(
      '/public/upload-reporter-media',
      data: formData,
    );
    return response.data['media_path'];
  }

  // ─── Push notifications (anonymous device registration) ───────────────

  Future<void> registerDeviceToken() async {
    try {
      final messaging = FirebaseMessaging.instance;

      final settings = await messaging.requestPermission();
      print('FCM permission status: ${settings.authorizationStatus}');
      if (settings.authorizationStatus != AuthorizationStatus.authorized) {
        print('FCM permission not granted, stopping.');
        return;
      }

      final token = await messaging.getToken();
      print('FCM token: $token');
      if (token == null) return;

      final response = await _dio.post('/public/devices/register', data: {'fcm_token': token});
      print('Device registration response: ${response.statusCode}');
    } catch (e) {
      print('FCM registration error: $e');
      // Don't let a notification-registration failure block the app
    }
  }

  // ─── Helpers ──────────────────────────────────────────────────────────

  String? getFullImageUrl(String? path) {
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http')) return path;
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    return '$baseImageUrl$normalizedPath';
  }

  Future<File> compressImage(File file) async {
    try {
      final dir = await getTemporaryDirectory();
      final targetPath =
          '${dir.path}/compressed_${DateTime.now().millisecondsSinceEpoch}.jpg';
      final XFile? result = await FlutterImageCompress.compressAndGetFile(
        file.path,
        targetPath,
        quality: 70,
      );
      if (result == null) return file;
      return File(result.path);
    } catch (e) {
      return file;
    }
  }
}