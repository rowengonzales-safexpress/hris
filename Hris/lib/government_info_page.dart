import 'package:flutter/material.dart';

class GovernmentInfoPage extends StatelessWidget {
  const GovernmentInfoPage({super.key});

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
          'Government Information',
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
            _InfoItem(label: 'SSS', value: '0637272256'),
            _InfoItem(label: 'TIN', value: '779854049000'),
            _InfoItem(label: 'PHIL HEALTH', value: '120259821431'),
            _InfoItem(label: 'PAG IBIG', value: '121283614421'),
            _InfoItem(label: 'PRC LICENSE', value: '-'),
            _InfoItem(label: 'PASSPORT', value: '-'),
            _InfoItem(label: 'RDO', value: '-'),
            _InfoItem(label: 'OTHER IDS', value: '-', isLast: true),
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
