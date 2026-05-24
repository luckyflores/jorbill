import 'package:flutter/material.dart';
import '../api/api_client.dart';

class CustomerPickerScreen extends StatefulWidget {
  const CustomerPickerScreen({super.key});
  @override
  State<CustomerPickerScreen> createState() => _CustomerPickerScreenState();
}

class _CustomerPickerScreenState extends State<CustomerPickerScreen> {
  final _api = ApiClient();
  final _ctrl = TextEditingController();
  List<Map<String, dynamic>> _results = [];
  bool _searching = false;

  Future<void> _doSearch(String q) async {
    if (q.length < 2) { setState(() => _results = []); return; }
    setState(() => _searching = true);
    try {
      final r = await _api.searchCustomers(q);
      setState(() => _results = r);
    } finally {
      setState(() => _searching = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pick a customer')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _ctrl,
              decoration: InputDecoration(
                prefixIcon: const Icon(Icons.search),
                hintText: 'Name, code, phone, or email',
                border: const OutlineInputBorder(),
                suffixIcon: _searching
                    ? const Padding(padding: EdgeInsets.all(12),
                        child: SizedBox(height: 16, width: 16,
                          child: CircularProgressIndicator(strokeWidth: 2)))
                    : null,
              ),
              onChanged: _doSearch,
            ),
          ),
          Expanded(
            child: ListView.builder(
              itemCount: _results.length,
              itemBuilder: (_, i) {
                final c = _results[i];
                return ListTile(
                  leading: const Icon(Icons.person_outline),
                  title: Text(c['name'] ?? '?'),
                  subtitle: Text('${c['customer_code'] ?? ''} · ${c['phone'] ?? ''}'),
                  trailing: Chip(label: Text(c['status'] ?? '?')),
                  onTap: () => Navigator.pop(context, c),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
