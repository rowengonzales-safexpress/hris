import 'package:flutter/material.dart';

class MyRequestsPage extends StatelessWidget {
  const MyRequestsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Requests'),
        centerTitle: true,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            Navigator.of(context).pop();
          },
        ),
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(
              'SELECT AN ATTENDANCE REQUEST',
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey.shade700,
                fontWeight: FontWeight.w600,
                letterSpacing: 1,
              ),
            ),
            const SizedBox(height: 12),
            _RequestCard(
              icon: Icons.work_outline,
              title: 'Leaves',
              subtitle:
                  'View or Apply for a vacation leave, sick leave, etc.',
              onTap: () {
                Navigator.of(context).pushNamed('/leaveLoading');
              },
            ),
            const _RequestCard(
              icon: Icons.article_outlined,
              title: 'Certificate of Attendance',
              subtitle: 'View or Create a missed attendance log',
            ),
            const _RequestCard(
              icon: Icons.schedule_outlined,
              title: 'Schedule Adjustment',
              subtitle: 'View or Temporarily change working time',
            ),
            const _RequestCard(
              icon: Icons.av_timer_outlined,
              title: 'Overtime',
              subtitle: 'View or Log work in excess of your shift hours',
            ),
            const _RequestCard(
              icon: Icons.timelapse_outlined,
              title: 'Undertime',
              subtitle: 'View or Request to work less hours than your shift',
              trailingLabel: 'NEW!',
            ),
          ],
        ),
      ),
    );
  }
}

class _RequestCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final String? trailingLabel;
  final VoidCallback? onTap;

  const _RequestCard({
    super.key,
    required this.icon,
    required this.title,
    required this.subtitle,
    this.trailingLabel,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 6),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Row(
          children: [
            Container(
              height: 40,
              width: 40,
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                icon,
                color: Colors.green,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.grey,
                    ),
                  ),
                ],
              ),
            ),
            if (trailingLabel != null)
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: Colors.green,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  trailingLabel!,
                  style: const TextStyle(
                    fontSize: 10,
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
