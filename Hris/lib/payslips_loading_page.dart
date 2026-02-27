import 'package:flutter/material.dart';
import 'dart:async';

class PayslipsLoadingPage extends StatefulWidget {
  const PayslipsLoadingPage({super.key});

  @override
  State<PayslipsLoadingPage> createState() => _PayslipsLoadingPageState();
}

class _PayslipsLoadingPageState extends State<PayslipsLoadingPage> {
  @override
  void initState() {
    super.initState();
    // Simulate loading delay then navigate to actual Payslips page
    Timer(const Duration(seconds: 2), () {
      if (mounted) {
        Navigator.of(context).pushReplacementNamed('/payslips');
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.green),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Payslips',
          style: TextStyle(
            color: Colors.black,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: 8, // Simulate multiple loading items
        itemBuilder: (context, index) {
          return Padding(
            padding: const EdgeInsets.only(bottom: 16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Section Header Skeleton (occasional)
                if (index % 3 == 0) ...[
                  Container(
                    width: 150,
                    height: 16,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                  const SizedBox(height: 8),
                ],
                // Card Skeleton
                Container(
                  height: 80,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
