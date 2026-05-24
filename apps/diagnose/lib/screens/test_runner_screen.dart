import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../api/api_client.dart';
import '../services/ping_service.dart';
import '../services/wifi_service.dart';

class TestRunnerScreen extends StatefulWidget {
  final Map<String, dynamic>? customer;
  const TestRunnerScreen({super.key, this.customer});

  @override
  State<TestRunnerScreen> createState() => _TestRunnerScreenState();
}

class _TestRunnerScreenState extends State<TestRunnerScreen> {
  final _api = ApiClient();
  final _wifi = WifiService();
  final _ping = PingService();
  final _notes = TextEditingController();

  bool _running = false;
  bool _submitting = false;
  int? _savedId;

  Map<String, dynamic>? _wifiInfo;
  final List<Map<String, dynamic>> _pingResults = [];
  String? _publicIp;
  Map<String, dynamic>? _config;
  String? _status;

  @override
  void initState() {
    super.initState();
    _loadConfig();
  }

  Future<void> _loadConfig() async {
    final cfg = await _api.getConfig();
    setState(() => _config = cfg ?? {
      'fourleaf_gateway': '8.8.8.8',
      'ping_targets': ['google.com', 'cloudflare.com'],
      'speedtest_url': 'https://www.speedtest.net',
    });
  }

  Future<void> _runAll() async {
    setState(() {
      _running = true;
      _savedId = null;
      _wifiInfo = null;
      _pingResults.clear();
      _publicIp = null;
    });

    try {
      setState(() => _status = 'Detecting WiFi…');
      _wifiInfo = await _wifi.snapshot();
      setState(() {});

      final gateway = _wifiInfo?['gateway_ip'];
      if (gateway != null) {
        setState(() => _status = 'Pinging your router ($gateway)…');
        _pingResults.add(await _ping.ping(gateway, count: 5));
        setState(() {});
      }

      final fourleaf = _config?['fourleaf_gateway'];
      if (fourleaf != null && fourleaf.toString().isNotEmpty) {
        setState(() => _status = 'Pinging FourLeaf ($fourleaf)…');
        _pingResults.add(await _ping.ping(fourleaf.toString(), count: 5));
        setState(() {});
      }

      final targets = (_config?['ping_targets'] as List?) ?? [];
      for (final t in targets) {
        setState(() => _status = 'Pinging $t…');
        _pingResults.add(await _ping.ping(t.toString(), count: 4));
        setState(() {});
      }

      setState(() => _status = 'Detecting public IP…');
      try {
        final r = await _api.get('/me');
        _publicIp = r.headers.value('x-forwarded-for') ?? r.data['ip'] ?? '';
      } catch (_) { /* ignore */ }
    } finally {
      setState(() {
        _running = false;
        _status = 'Tests complete';
      });
    }
  }

  Future<void> _openSpeedtest() async {
    final url = _config?['speedtest_url']?.toString();
    if (url == null) return;
    await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
  }

  Future<void> _submit() async {
    setState(() => _submitting = true);

    final pkg = await PackageInfo.fromPlatform();
    final devInfo = DeviceInfoPlugin();
    Map<String, dynamic> device = {};
    try {
      final android = await devInfo.androidInfo;
      device = {
        'model':       android.model,
        'manufacturer':android.manufacturer,
        'os':          'Android ${android.version.release}',
        'sdk':         android.version.sdkInt,
      };
    } catch (_) {}

    final payload = {
      'customer_id': widget.customer?['id'],
      'public_ip':   _publicIp,
      'wifi':        _wifiInfo,
      'ping_results': _pingResults,
      'notes':       _notes.text.trim().isEmpty ? null : _notes.text.trim(),
      'app_version': '${pkg.version}+${pkg.buildNumber}',
      'device_info': device,
    };

    final id = await _api.submitDiagnostic(payload);
    setState(() {
      _submitting = false;
      _savedId = id;
    });

    if (id != null && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Saved as diagnostic #$id ✓'), backgroundColor: Colors.green),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Submit failed — check connection'), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final customer = widget.customer;
    return Scaffold(
      appBar: AppBar(title: Text(customer == null ? 'My diagnostic' : 'Diagnostic for ${customer['name']}')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (customer != null)
            Card(
              child: ListTile(
                leading: const Icon(Icons.person_pin),
                title: Text(customer['name'] ?? ''),
                subtitle: Text('${customer['customer_code'] ?? ''} · ${customer['phone'] ?? ''}'),
              ),
            ),
          const SizedBox(height: 8),
          FilledButton.icon(
            icon: const Icon(Icons.play_arrow),
            label: Text(_running ? 'Running…' : 'Run diagnostics'),
            onPressed: _running ? null : _runAll,
          ),
          if (_status != null) ...[
            const SizedBox(height: 8),
            Text(_status!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.grey)),
          ],
          const Divider(height: 32),
          if (_wifiInfo != null) _buildWifiCard(),
          ..._pingResults.map(_buildPingCard),
          if (_publicIp != null && _publicIp!.isNotEmpty)
            Card(
              child: ListTile(
                leading: const Icon(Icons.public),
                title: const Text('Public IP'),
                subtitle: Text(_publicIp!),
              ),
            ),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            icon: const Icon(Icons.speed),
            label: const Text('Open FourLeaf Speedtest'),
            onPressed: _openSpeedtest,
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _notes,
            maxLines: 3,
            decoration: const InputDecoration(
              labelText: 'Notes (optional)',
              border: OutlineInputBorder(),
              hintText: 'e.g. master bedroom, weak signal at far end of house',
            ),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            icon: const Icon(Icons.cloud_upload),
            label: Text(_submitting ? 'Submitting…' : 'Submit to JorBill'),
            onPressed: (_running || _submitting) ? null : _submit,
          ),
          if (_savedId != null) ...[
            const SizedBox(height: 8),
            Text('Saved as diagnostic #$_savedId',
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.green)),
          ],
        ],
      ),
    );
  }

  Widget _buildWifiCard() {
    final w = _wifiInfo!;
    final rssi = w['rssi'];
    Color signalColor() {
      if (rssi == null) return Colors.grey;
      if (rssi >= -55) return Colors.green;
      if (rssi >= -70) return Colors.amber;
      return Colors.red;
    }
    return Card(
      child: ListTile(
        leading: Icon(Icons.wifi, color: signalColor(), size: 32),
        title: Text(w['ssid']?.toString().isNotEmpty == true ? w['ssid'] : '(no SSID)'),
        subtitle: Text(
          [
            if (rssi != null) '$rssi dBm',
            if (w['frequency'] != null) '${(w['frequency'] / 1000).toStringAsFixed(1)} GHz',
            if (w['gateway_ip'] != null) 'gw ${w['gateway_ip']}',
          ].join(' · '),
        ),
      ),
    );
  }

  Widget _buildPingCard(Map<String, dynamic> p) {
    final avg = p['avg_ms'];
    final loss = p['loss_pct'];
    Color color = Colors.green;
    if (loss != null && (loss as num) > 5) color = Colors.red;
    else if (avg != null && (avg as num) > 100) color = Colors.amber;
    return Card(
      child: ListTile(
        leading: Icon(Icons.network_ping, color: color),
        title: Text(p['target']?.toString() ?? '?'),
        subtitle: Text(
          [
            if (avg != null) '${(avg as num).toStringAsFixed(1)} ms avg',
            if (p['min_ms'] != null) 'min ${(p['min_ms'] as num).toStringAsFixed(1)}',
            if (p['max_ms'] != null) 'max ${(p['max_ms'] as num).toStringAsFixed(1)}',
            'loss ${p['loss_pct']}%',
          ].join(' · '),
        ),
      ),
    );
  }
}
