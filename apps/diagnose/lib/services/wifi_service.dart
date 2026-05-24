import 'package:network_info_plus/network_info_plus.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:wifi_iot/wifi_iot.dart';

class WifiService {
  final NetworkInfo _info = NetworkInfo();

  /// Returns null if WiFi info isn't accessible (permission denied / not connected).
  Future<Map<String, dynamic>?> snapshot() async {
    // Location permission is REQUIRED on Android 9+ to read SSID/BSSID.
    final perm = await Permission.locationWhenInUse.request();
    if (!perm.isGranted) return null;

    try {
      final ssid = await _info.getWifiName();      // returns with surrounding quotes
      final bssid = await _info.getWifiBSSID();
      final ip = await _info.getWifiIP();
      final gateway = await _info.getWifiGatewayIP();
      int? rssi;
      int? frequency;
      try {
        rssi = await WiFiForIoTPlugin.getCurrentSignalStrength();
        frequency = await WiFiForIoTPlugin.getFrequency();
      } catch (_) {
        // wifi_iot can throw on some devices; ignore
      }
      return {
        'ssid':       (ssid ?? '').replaceAll('"', ''),
        'bssid':      bssid,
        'local_ip':   ip,
        'gateway_ip': gateway,
        'rssi':       rssi,        // dBm
        'frequency':  frequency,   // MHz
      };
    } catch (e) {
      return {'error': e.toString()};
    }
  }
}
