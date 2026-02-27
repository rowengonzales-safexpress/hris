import 'package:flutter/material.dart';
import 'clock_in_page.dart';

class ClockInLoadingPage extends StatefulWidget {
  const ClockInLoadingPage({super.key});

  @override
  State<ClockInLoadingPage> createState() => _ClockInLoadingPageState();
}

class _ClockInLoadingPageState extends State<ClockInLoadingPage> {
  @override
  void initState() {
    super.initState();
    // Simulate loading delay before navigating to the actual ClockInPage
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        final args =
            ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => ClockInPage(type: args?['type'] ?? 'IN'),
          ),
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            // Header with Back Button
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                children: [
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () => Navigator.of(context).pop(),
                        borderRadius: BorderRadius.circular(20),
                        child: const Padding(
                          padding: EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                          child: Row(
                            children: [
                              Icon(
                                Icons.arrow_back,
                                size: 20,
                                color: Colors.black54,
                              ),
                              SizedBox(width: 4),
                              Text(
                                "Back",
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.black54,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Map Skeleton Placeholder
            Expanded(
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 0),
                color: Colors.grey.shade200,
              ),
            ),

            // Bottom Sheet Skeleton
            Container(
              padding: const EdgeInsets.all(24),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Time Skeleton
                  Container(
                    width: 200,
                    height: 40,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  const SizedBox(height: 16),
                  // Date Skeleton
                  Container(
                    width: 250,
                    height: 20,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  const SizedBox(height: 32),
                  // Button Skeleton
                  Container(
                    width: double.infinity,
                    height: 56,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
