import 'package:flutter/material.dart';

class WorkSchedulePage extends StatelessWidget {
  const WorkSchedulePage({super.key});

  @override
  Widget build(BuildContext context) {
    // Get current day of week (1=Monday, 7=Sunday)
    final int currentWeekday = DateTime.now().weekday;

    // Define schedule data
    final List<Map<String, dynamic>> scheduleData = [
      {
        'day': 'SUNDAY',
        'weekday': DateTime.sunday,
        'isRestDay': true,
      },
      {
        'day': 'MONDAY',
        'weekday': DateTime.monday,
        'shift': '08:00 AM - 05:00 PM',
        'break': '12:00 PM - 01:00 PM',
      },
      {
        'day': 'TUESDAY',
        'weekday': DateTime.tuesday,
        'shift': '08:00 AM - 05:00 PM',
        'break': '12:00 PM - 01:00 PM',
      },
      {
        'day': 'WEDNESDAY',
        'weekday': DateTime.wednesday,
        'shift': '08:00 AM - 05:00 PM',
        'break': '12:00 PM - 01:00 PM',
      },
      {
        'day': 'THURSDAY',
        'weekday': DateTime.thursday,
        'shift': '08:00 AM - 05:00 PM',
        'break': '12:00 PM - 01:00 PM',
      },
      {
        'day': 'FRIDAY',
        'weekday': DateTime.friday,
        'shift': '08:00 AM - 05:00 PM',
        'break': '12:00 PM - 01:00 PM',
      },
      {
        'day': 'SATURDAY',
        'weekday': DateTime.saturday,
        'isRestDay': true, // Assuming Saturday is rest day based on typical work week, or add as work day if needed
      },
    ];

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
          'Work Schedule',
          style: TextStyle(
            color: Colors.black, // Dark green/black
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'DEFAULT SCHEDULE',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.black87,
              ),
            ),
            const SizedBox(height: 16),
            _buildInfoRow('Schedule Type', 'Normal Shift'),
            const SizedBox(height: 8),
            _buildInfoRow('Working Hours Including\nBreak', '9 Per Day'),
            const SizedBox(height: 16),
            const Divider(),
            const SizedBox(height: 16),
            ...scheduleData.map((data) {
              return _ScheduleCard(
                day: data['day'],
                isRestDay: data['isRestDay'] ?? false,
                shift: data['shift'],
                breakTime: data['break'],
                isCurrentDay: data['weekday'] == currentWeekday,
              );
            }).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            color: Colors.black87,
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Colors.blueGrey, // Dark green/blue grey
          ),
        ),
      ],
    );
  }
}

class _ScheduleCard extends StatelessWidget {
  final String day;
  final bool isRestDay;
  final String? shift;
  final String? breakTime;
  final bool isCurrentDay;

  const _ScheduleCard({
    required this.day,
    this.isRestDay = false,
    this.shift,
    this.breakTime,
    required this.isCurrentDay,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isRestDay ? Colors.grey.shade100 : Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isCurrentDay ? Colors.green : Colors.grey.shade300,
          width: isCurrentDay ? 1.5 : 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                day,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isCurrentDay ? Colors.green : Colors.grey,
                  letterSpacing: 1,
                ),
              ),
              if (isRestDay)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.blueGrey),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text(
                    'REST DAY',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: Colors.blueGrey,
                    ),
                  ),
                ),
            ],
          ),
          if (!isRestDay) ...[
            const SizedBox(height: 12),
            _buildTimeRow('Shift', shift!),
            const SizedBox(height: 8),
            _buildTimeRow('Break', breakTime!),
          ],
        ],
      ),
    );
  }

  Widget _buildTimeRow(String label, String time) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            color: Colors.black87,
          ),
        ),
        Text(
          time,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Colors.black87,
          ),
        ),
      ],
    );
  }
}
