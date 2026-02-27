import 'dart:async';
import 'package:flutter/material.dart';

class AttendanceLoadingPage extends StatefulWidget {
  const AttendanceLoadingPage({super.key});

  @override
  State<AttendanceLoadingPage> createState() => _AttendanceLoadingPageState();
}

class _AttendanceLoadingPageState extends State<AttendanceLoadingPage> {
  @override
  void initState() {
    super.initState();
    Timer(const Duration(seconds: 2), () {
      if (mounted) {
        Navigator.of(context).pushReplacementNamed('/attendance');
      }
    });
  }

  Widget _buildSkeletonItem() {
    return Column(
      children: [
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Date column skeleton
              Column(
                children: [
                  Container(width: 24, height: 16, color: Colors.grey.shade200),
                  const SizedBox(height: 4),
                  Container(width: 32, height: 12, color: Colors.grey.shade200),
                ],
              ),
              const SizedBox(width: 16),
              // Logs column skeleton
              Expanded(
                child: Column(
                  children: [
                    // OUT log
                    Row(
                      children: [
                        Container(
                          width: 40,
                          height: 20,
                          decoration: BoxDecoration(
                            color: Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Container(
                          width: 100,
                          height: 16,
                          color: Colors.grey.shade200,
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      height: 12,
                      color: Colors.grey.shade200,
                    ),
                    const SizedBox(height: 16),
                    // IN log
                    Row(
                      children: [
                        Container(
                          width: 40,
                          height: 20,
                          decoration: BoxDecoration(
                            color: Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Container(
                          width: 100,
                          height: 16,
                          color: Colors.grey.shade200,
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      height: 12,
                      color: Colors.grey.shade200,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const Divider(),
      ],
    );
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
          'My Attendance Logs',
          style: TextStyle(
            color: Colors.black,
            fontWeight: FontWeight.w600,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.more_horiz, color: Colors.green),
            onPressed: () {},
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Header skeletons
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    width: 120,
                    height: 20,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                  Container(
                    width: 120,
                    height: 20,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Container(
                  width: 80,
                  height: 16,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
            ),
            Expanded(
              child: ListView.builder(
                itemCount: 5,
                itemBuilder: (context, index) => _buildSkeletonItem(),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
