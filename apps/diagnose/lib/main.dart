import 'package:flutter/material.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'services/storage.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const DiagnoseApp());
}

class DiagnoseApp extends StatelessWidget {
  const DiagnoseApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'FourLeaf Diagnose',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF10B981), // emerald (matches portal)
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
      ),
      home: FutureBuilder<bool>(
        future: SecureStorage.hasToken(),
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Scaffold(body: Center(child: CircularProgressIndicator()));
          }
          return snap.data == true ? const DashboardScreen() : const LoginScreen();
        },
      ),
    );
  }
}
