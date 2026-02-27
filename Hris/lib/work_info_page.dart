import 'package:flutter/material.dart';

class WorkInfoPage extends StatelessWidget {
  const WorkInfoPage({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 4,
      child: Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 1,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.green),
            onPressed: () => Navigator.of(context).pop(),
          ),
          title: const Text(
            'Work Information',
            style: TextStyle(
              color: Colors.black, // Dark green/black
              fontWeight: FontWeight.bold,
              fontSize: 18,
            ),
          ),
          bottom: const TabBar(
            isScrollable: true,
            labelColor: Colors.black,
            unselectedLabelColor: Colors.grey,
            indicatorColor: Colors.green,
            tabs: [
              Tab(text: 'Basic'),
              Tab(text: 'Employment'),
              Tab(text: 'Approvals'),
              Tab(text: 'Other'),
            ],
          ),
        ),
        body: const TabBarView(
          children: [
            _BasicTab(),
            _EmploymentTab(),
            Center(child: Text("No approvals information available")), // Placeholder
            _OtherTab(),
          ],
        ),
      ),
    );
  }
}

class _BasicTab extends StatelessWidget {
  const _BasicTab();

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: const [
          _InfoItem(label: 'COMPANY', value: 'Safexpress Logistics Inc.'),
          _InfoItem(label: 'DEPARTMENT', value: 'OPERATIONS'),
          _InfoItem(label: 'JOB TITLE', value: 'INFORMATION TECHNOLOGY'),
          _InfoItem(label: 'EMPLOYEE TYPE', value: 'rank and file'),
          _InfoItem(label: 'IMMEDIATE SUPERVISOR', value: 'MELCHOR MENDOZA'),
          _InfoItem(
            label: 'DESIGNATED WORKPLACE',
            value: 'CEBU SYSU',
            isLast: true,
          ),
        ],
      ),
    );
  }
}

class _EmploymentTab extends StatelessWidget {
  const _EmploymentTab();

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: const [
          _InfoItem(label: 'EMPLOYMENT STATUS', value: 'Regular'),
          _InfoItem(label: 'USER TYPE', value: 'Employee'),
          _InfoItem(label: 'JOB CODE', value: '-'),
          _InfoItem(label: 'JOB GRADE', value: '0'),
          _InfoItem(label: 'CLIENT NAME', value: '-'),
          _InfoItem(label: 'BILLABILITY', value: '-'),
          _InfoItem(label: 'EXPECTED REGULARIZATION DATE', value: '08/15/2023'),
          _InfoItem(label: 'REGULARIZATION DATE', value: '08/16/2023'),
          _InfoItem(label: 'EMPLOYMENT REMARKS', value: '-', isLast: true),
        ],
      ),
    );
  }
}

class _OtherTab extends StatelessWidget {
  const _OtherTab();

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: const [
          _InfoItem(label: 'BIOMETRIC ID', value: '522'),
          _InfoItem(label: 'PAYROLL RUN TYPE', value: 'Monthly', isLast: true),
        ],
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
