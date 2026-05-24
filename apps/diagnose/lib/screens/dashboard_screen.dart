import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../api/api_client.dart';
import '../services/storage.dart';
import 'customer_picker_screen.dart';
import 'test_runner_screen.dart';
import 'login_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});
  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _api = ApiClient();
  String? _name;
  String? _actor;
  List<Map<String, dynamic>> _recent = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final n = await SecureStorage.name();
    final a = await SecureStorage.actor();
    final recent = await _api.myRecent();
    setState(() { _name = n; _actor = a; _recent = recent; _loading = false; });
  }

  Future<void> _startTest() async {
    if (_actor == 'tech') {
      final picked = await Navigator.push<Map<String, dynamic>>(context,
          MaterialPageRoute(builder: (_) => const CustomerPickerScreen()));
      if (!mounted || picked == null) return;
      await Navigator.push(context,
          MaterialPageRoute(builder: (_) => TestRunnerScreen(customer: picked)));
    } else {
      await Navigator.push(context,
          MaterialPageRoute(builder: (_) => const TestRunnerScreen()));
    }
    _load();
  }

  Future<void> _logout() async {
    await SecureStorage.clear();
    if (!mounted) return;
    Navigator.pushReplacement(context,
        MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('FourLeaf Diagnose'),
        actions: [
          IconButton(icon: const Icon(Icons.logout), onPressed: _logout),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.person, size: 36),
                      title: Text('Hi, ${_name ?? '?'}'),
                      subtitle: Text(_actor == 'tech' ? 'Tech account' : 'Customer account'),
                    ),
                  ),
                  const SizedBox(height: 16),
                  FilledButton.icon(
                    style: FilledButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    icon: const Icon(Icons.network_check, size: 26),
                    label: const Text('Run new test', style: TextStyle(fontSize: 16)),
                    onPressed: _startTest,
                  ),
                  const SizedBox(height: 24),
                  Text('Recent tests', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  if (_recent.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Text('No tests yet. Tap "Run new test" to begin.',
                          style: TextStyle(color: Colors.grey)),
                    )
                  else
                    ..._recent.map((r) => Card(
                          child: ListTile(
                            leading: const Icon(Icons.signal_cellular_alt),
                            title: Text(_formatTitle(r)),
                            subtitle: Text(_formatSubtitle(r)),
                            trailing: Text(_formatDate(r['ran_at'])),
                          ),
                        )),
                ],
              ),
            ),
    );
  }

  String _formatTitle(Map<String, dynamic> r) {
    final wifi = r['wifi'] as Map?;
    final ssid = wifi?['ssid'] as String?;
    return ssid?.isNotEmpty == true ? ssid! : 'Diagnostic #${r['id']}';
  }

  String _formatSubtitle(Map<String, dynamic> r) {
    final wifi = r['wifi'] as Map?;
    final rssi = wifi?['rssi'];
    final speed = (r['speedtest'] as Map?)?['download_mbps'];
    final parts = <String>[];
    if (rssi != null) parts.add('${rssi} dBm');
    if (speed != null) parts.add('↓ ${(speed as num).toStringAsFixed(1)} Mbps');
    return parts.join(' · ');
  }

  String _formatDate(dynamic v) {
    if (v == null) return '';
    try {
      return DateFormat.MMMd().add_jm().format(DateTime.parse(v.toString()).toLocal());
    } catch (_) {
      return v.toString();
    }
  }
}
