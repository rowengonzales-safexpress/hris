import 'package:flutter/material.dart';

class WelcomePage extends StatelessWidget {
  const WelcomePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Text(
          'sprout\nSOLUTIONS',
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 32,
            color: Colors.green.shade700,
            fontWeight: FontWeight.w700,
            letterSpacing: 2,
          ),
        ),
      ),
    );
  }
}

