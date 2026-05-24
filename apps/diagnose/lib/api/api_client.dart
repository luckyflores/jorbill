import 'package:dio/dio.dart';
import '../services/storage.dart';

class ApiClient {
  static const String _baseUrl = 'https://jorbill.maltixtech.xyz/api';

  late final Dio _dio;

  ApiClient() {
    _dio = Dio(BaseOptions(
      baseUrl: _baseUrl,
      connectTimeout: const Duration(seconds: 12),
      receiveTimeout: const Duration(seconds: 25),
      headers: {'Accept': 'application/json'},
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final t = await SecureStorage.token();
        if (t != null) {
          options.headers['Authorization'] = 'Bearer $t';
        }
        handler.next(options);
      },
    ));
  }

  Future<Response<dynamic>> post(String path, [Object? data]) =>
      _dio.post(path, data: data);

  Future<Response<dynamic>> get(String path, [Map<String, dynamic>? query]) =>
      _dio.get(path, queryParameters: query);

  // ─── Convenience wrappers ────────────────────────────────────────

  Future<Map<String, dynamic>?> getConfig() async {
    try {
      final r = await get('/config/diagnostics');
      return Map<String, dynamic>.from(r.data);
    } catch (_) {
      return null;
    }
  }

  Future<({String token, String actor, String name, int id})?> login(
      String email, String password, String actor) async {
    try {
      final r = await post('/auth/token', {
        'email': email,
        'password': password,
        'actor': actor,
        'device': 'flutter-diagnose',
      });
      final d = r.data as Map<String, dynamic>;
      return (
        token: d['token'] as String,
        actor: d['actor'] as String,
        name:  d['name']  as String,
        id:    d['user_id'] as int,
      );
    } catch (_) {
      return null;
    }
  }

  Future<List<Map<String, dynamic>>> searchCustomers(String q) async {
    final r = await get('/customers/search', {'q': q});
    return List<Map<String, dynamic>>.from(r.data['data'] ?? []);
  }

  Future<int?> submitDiagnostic(Map<String, dynamic> payload) async {
    try {
      final r = await post('/diagnostics', payload);
      return (r.data['id'] as int?);
    } catch (e) {
      return null;
    }
  }

  Future<List<Map<String, dynamic>>> myRecent() async {
    try {
      final r = await get('/diagnostics/mine');
      return List<Map<String, dynamic>>.from(r.data['data'] ?? []);
    } catch (_) {
      return [];
    }
  }
}
