import 'package:flutter/material.dart';

class BasicInfoPage extends StatelessWidget {
  const BasicInfoPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 1,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.green),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Basic Information',
          style: TextStyle(
            color: Colors.black, // Dark green/black
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            _InfoItem(label: 'EMPLOYEE ID', value: 'SLI-579'),
            _InfoItem(label: 'FIRST NAME', value: 'ROWEN'),
            _InfoItem(label: 'MIDDLE NAME', value: 'JAIME'),
            _InfoItem(label: 'LAST NAME', value: 'GONZALES'),
            _InfoItem(label: 'GENDER', value: 'Male'),
            _InfoItem(label: 'CIVIL STATUS', value: 'Married'),
            _InfoItem(
              label: 'DATE OF BIRTH',
              value: '06/13/1989',
              isLast: true,
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoItem extends StatelessWidget {
  final String label;
  final String value;
  final bool isLast;

  const _InfoItem({
    required this.label,
    required this.value,
    this.isLast = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.bold,
            color: Colors.blueGrey.shade700,
            letterSpacing: 0.5,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          value,
          style: const TextStyle(
            fontSize: 16,
            color: Colors.black87,
            fontWeight: FontWeight.w400,
          ),
        ),
        if (!isLast) ...[
          const SizedBox(height: 16),
          Divider(color: Colors.grey.shade200),
          const SizedBox(height: 16),
        ],
      ],
    );
  }
}
