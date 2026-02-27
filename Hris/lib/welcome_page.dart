import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';

class WelcomePage extends StatefulWidget {
  const WelcomePage({super.key});

  @override
  State<WelcomePage> createState() => _WelcomePageState();
}

class _WelcomePageState extends State<WelcomePage> {
  @override
  void initState() {
    super.initState();
    _checkPermissionsAndNavigate();
  }

  Future<void> _checkPermissionsAndNavigate() async {
    // Simulate loading/splash time
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;

    final status = await Permission.location.status;
    if (status.isGranted) {
      // If permission is already granted, skip the permission page
      Navigator.of(context).pushReplacementNamed('/login');
    } else {
      // If permission is not granted (denied, restricted, permanentlyDenied), show the permission page
      Navigator.of(context).pushReplacementNamed('/permission');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'sprout\nSOLUTIONS',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 32,
                color: Colors.green.shade700,
                fontWeight: FontWeight.w700,
                letterSpacing: 2,
              ),
            ),
            const SizedBox(height: 24),
            const CircularProgressIndicator(color: Colors.green),
          ],
        ),
      ),
    );
  }
}
