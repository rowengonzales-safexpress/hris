import 'package:flutter/material.dart';
import 'calendar_filter_page.dart';

class AttendancePage extends StatefulWidget {
  final DateTimeRange? filterRange;

  const AttendancePage({super.key, this.filterRange});

  @override
  State<AttendancePage> createState() => _AttendancePageState();
}

class _AttendancePageState extends State<AttendancePage> {
  bool _isLoading = false;
  DateTimeRange? _currentRange;

  // Mock data
  final List<Map<String, dynamic>> _allLogs = [
    {
      'date': DateTime(2026, 2, 24),
      'day': '24',
      'weekday': 'TUE',
      'logs': [
        {
          'type': 'OUT',
          'time': '05:07:43 PM',
          'location':
              'C. M. Cabahug Street, Ouano Compound, Looc, Mandaue, Central Visayas, 3359, Philippines',
        },
        {
          'type': 'IN',
          'time': '07:05:41 AM',
          'location':
              'C. M. Cabahug Street, Ouano Compound, Looc, Mandaue, Central Visayas, 3359, Philippines',
        },
      ],
    },
    {
      'date': DateTime(2026, 2, 23),
      'day': '23',
      'weekday': 'MON',
      'logs': [
        {
          'type': 'OUT',
          'time': '05:08:40 PM',
          'location':
              'C. M. Cabahug Street, Ouano Compound, Looc, Mandaue, Central Visayas, 3359, Philippines',
        },
        {
          'type': 'IN',
          'time': '07:47:49 AM',
          'location':
              'Ouano Compound, Looc, Mandaue, Central Visayas, 3359, Philippines',
        },
      ],
    },
    {
      'date': DateTime(2026, 2, 20),
      'day': '20',
      'weekday': 'FRI',
      'logs': [
        {
          'type': 'OUT',
          'time': '05:05:09 PM',
          'location':
              'Ouano Compound, Looc, Mandaue, Central Visayas, 3359, Philippines',
        },
        {
          'type': 'IN',
          'time': '07:49:22 AM',
          'location':
              'C. M. Cabahug Street, Ouano Compound, Looc, Mandaue, Central Visayas, 3359, Philippines',
        },
      ],
    },
    // Add logs for the filter range (Feb 2-7)
    {
      'date': DateTime(2026, 2, 6),
      'day': '6',
      'weekday': 'FRI',
      'logs': [
        {'type': 'OUT', 'time': '05:00:00 PM', 'location': 'Office Location'},
        {'type': 'IN', 'time': '08:00:00 AM', 'location': 'Office Location'},
      ],
    },
    {
      'date': DateTime(2026, 2, 5),
      'day': '5',
      'weekday': 'THU',
      'logs': [
        {'type': 'OUT', 'time': '05:00:00 PM', 'location': 'Office Location'},
        {'type': 'IN', 'time': '08:00:00 AM', 'location': 'Office Location'},
      ],
    },
    {
      'date': DateTime(2026, 2, 4),
      'day': '4',
      'weekday': 'WED',
      'logs': [
        {'type': 'OUT', 'time': '05:00:00 PM', 'location': 'Office Location'},
        {'type': 'IN', 'time': '08:00:00 AM', 'location': 'Office Location'},
      ],
    },
    {
      'date': DateTime(2026, 2, 3),
      'day': '3',
      'weekday': 'TUE',
      'logs': [
        {'type': 'OUT', 'time': '05:00:00 PM', 'location': 'Office Location'},
        {'type': 'IN', 'time': '08:00:00 AM', 'location': 'Office Location'},
      ],
    },
    {
      'date': DateTime(2026, 2, 2),
      'day': '2',
      'weekday': 'MON',
      'logs': [
        {'type': 'OUT', 'time': '05:00:00 PM', 'location': 'Office Location'},
        {'type': 'IN', 'time': '08:00:00 AM', 'location': 'Office Location'},
      ],
    },
  ];

  @override
  void initState() {
    super.initState();
    _currentRange = widget.filterRange;
  }

  List<Map<String, dynamic>> get _filteredLogs {
    if (_currentRange == null) {
      // Default: show latest logs (first 3)
      return _allLogs
          .where(
            (log) => (log['date'] as DateTime).isAfter(DateTime(2026, 2, 19)),
          )
          .toList();
    }
    return _allLogs.where((log) {
      final date = log['date'] as DateTime;
      return date.isAfter(
            _currentRange!.start.subtract(const Duration(days: 1)),
          ) &&
          date.isBefore(_currentRange!.end.add(const Duration(days: 1)));
    }).toList();
  }

  Future<void> _handleRefresh() async {
    setState(() => _isLoading = true);
    await Future.delayed(const Duration(seconds: 2));
    if (mounted) {
      setState(() => _isLoading = false);
    }
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
      body: Stack(
        children: [
          Column(
            children: [
              // Header
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                color: Colors.white,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Text(
                          'Latest Logs',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.purple.shade100,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '${_filteredLogs.length}',
                            style: TextStyle(
                              color: Colors.purple.shade700,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: const [
                        Text(
                          'as of Feb. 25, 2026',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: Colors.black87,
                          ),
                        ),
                        Text(
                          '05:56:12 AM', // Static time from image
                          style: TextStyle(fontSize: 12, color: Colors.grey),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              // Month Header
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                color: Colors.grey.shade50,
                child: const Text(
                  'February 2026',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey,
                  ),
                ),
              ),
              Expanded(
                child: RefreshIndicator(
                  onRefresh: _handleRefresh,
                  color: Colors.green,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _filteredLogs.length,
                    separatorBuilder: (context, index) =>
                        const Divider(height: 24),
                    itemBuilder: (context, index) {
                      final log = _filteredLogs[index];
                      final logs = log['logs'] as List<Map<String, dynamic>>;

                      return Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Date Column
                          SizedBox(
                            width: 50,
                            child: Column(
                              children: [
                                Text(
                                  log['day'],
                                  style: const TextStyle(
                                    fontSize: 20,
                                    color: Colors.grey,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                                Text(
                                  log['weekday'],
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 16),
                          // Logs Column
                          Expanded(
                            child: Column(
                              children: logs
                                  .map((l) => _LogItem(log: l))
                                  .toList(),
                            ),
                          ),
                        ],
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
          if (_isLoading)
            Container(
              color: Colors.white.withOpacity(0.5),
              child: const Center(
                child: CircularProgressIndicator(color: Colors.green),
              ),
            ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.of(
            context,
          ).pushNamed('/calendarFilter');
          if (result != null && result is DateTimeRange) {
            setState(() {
              _currentRange = result;
            });
          }
        },
        backgroundColor: Colors.green,
        child: const Icon(Icons.calendar_today_outlined),
      ),
    );
  }
}

class _LogItem extends StatefulWidget {
  final Map<String, dynamic> log;

  const _LogItem({required this.log});

  @override
  State<_LogItem> createState() => _LogItemState();
}

class _LogItemState extends State<_LogItem> {
  bool _isExpanded = false;

  @override
  Widget build(BuildContext context) {
    final l = widget.log;
    final isOut = l['type'] == 'OUT';

    return GestureDetector(
      onTap: () {
        setState(() {
          _isExpanded = !_isExpanded;
        });
      },
      child: Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Column(
              children: [
                Text(
                  l['type'],
                  style: TextStyle(
                    color: isOut ? Colors.orange : Colors.blue.shade700,
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 4,
                    vertical: 2,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text(
                    'APP',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: Colors.black54,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    l['time'],
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    l['location'],
                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                    maxLines: _isExpanded ? null : 1,
                    overflow: _isExpanded
                        ? TextOverflow.visible
                        : TextOverflow.ellipsis,
                  ),
                  if (_isExpanded) ...[
                    const SizedBox(height: 8),
                    Text(
                      'Attendance Logged ${isOut ? 'Out' : 'In'} With Geotag',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.grey,
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
