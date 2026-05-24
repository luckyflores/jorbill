import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Wraps flutter_secure_storage for the bits of state we keep across launches.
class SecureStorage {
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static const _kToken = 'jorbill_auth_token';
  static const _kActor = 'jorbill_actor';   // 'tech' | 'customer'
  static const _kName  = 'jorbill_user_name';
  static const _kId    = 'jorbill_user_id';

  static Future<void> saveAuth({
    required String token,
    required String actor,
    required String name,
    required int userId,
  }) async {
    await _storage.write(key: _kToken, value: token);
    await _storage.write(key: _kActor, value: actor);
    await _storage.write(key: _kName,  value: name);
    await _storage.write(key: _kId,    value: userId.toString());
  }

  static Future<String?> token() => _storage.read(key: _kToken);
  static Future<String?> actor() => _storage.read(key: _kActor);
  static Future<String?> name()  => _storage.read(key: _kName);
  static Future<int?>    userId() async {
    final s = await _storage.read(key: _kId);
    return s == null ? null : int.tryParse(s);
  }

  static Future<bool> hasToken() async => (await token()) != null;

  static Future<void> clear() async {
    await _storage.deleteAll();
  }
}
