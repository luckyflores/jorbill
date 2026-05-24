import 'package:dart_ping/dart_ping.dart';

class PingService {
  /// Pings `host` `count` times. Returns averaged stats.
  Future<Map<String, dynamic>> ping(String host, {int count = 4, int timeoutSec = 3}) async {
    final p = Ping(host, count: count, timeout: timeoutSec);
    int sent = 0;
    int received = 0;
    final List<double> rtts = [];
    String? errorMessage;

    try {
      await for (final event in p.stream) {
        if (event.response != null) {
          sent++;
          final r = event.response!;
          if (r.time != null) {
            received++;
            rtts.add(r.time!.inMicroseconds / 1000.0);
          }
        }
        if (event.error != null) {
          errorMessage = event.error.toString();
        }
        if (event.summary != null) {
          break;
        }
      }
    } catch (e) {
      errorMessage = e.toString();
    }

    double? avg;
    double? min;
    double? max;
    double? jitter;
    if (rtts.isNotEmpty) {
      avg = rtts.reduce((a, b) => a + b) / rtts.length;
      min = rtts.reduce((a, b) => a < b ? a : b);
      max = rtts.reduce((a, b) => a > b ? a : b);
      if (rtts.length > 1) {
        // Simple jitter = stddev
        final mean = avg!;
        final variance = rtts.map((r) => (r - mean) * (r - mean)).reduce((a, b) => a + b) / rtts.length;
        jitter = variance > 0 ? variance.toDouble() : 0;
      }
    }

    return {
      'target':   host,
      'sent':     sent == 0 ? count : sent,
      'received': received,
      'loss_pct': sent == 0 ? 100 : ((sent - received) / sent * 100).round(),
      'avg_ms':   avg,
      'min_ms':   min,
      'max_ms':   max,
      'jitter_ms': jitter,
      if (errorMessage != null) 'error': errorMessage,
    };
  }
}
