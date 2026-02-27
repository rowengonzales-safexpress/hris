import 'package:flutter/material.dart';

class CalendarFilterPage extends StatefulWidget {
  const CalendarFilterPage({super.key});

  @override
  State<CalendarFilterPage> createState() => _CalendarFilterPageState();
}

class _CalendarFilterPageState extends State<CalendarFilterPage> {
  DateTime? _startDate;
  DateTime? _endDate;
  final DateTime _currentMonth = DateTime(2026, 2);

  // Helper to get days in month
  int get _daysInMonth => DateUtils.getDaysInMonth(_currentMonth.year, _currentMonth.month);
  
  // Helper to get first weekday of month (1 = Mon, 7 = Sun)
  int get _firstWeekday => DateTime(_currentMonth.year, _currentMonth.month, 1).weekday;

  void _onDayTapped(int day) {
    final date = DateTime(_currentMonth.year, _currentMonth.month, day);
    
    setState(() {
      if (_startDate == null) {
        _startDate = date;
      } else if (_endDate == null) {
        if (date.isBefore(_startDate!)) {
          _startDate = date;
        } else {
          _endDate = date;
        }
      } else {
        _startDate = date;
        _endDate = null;
      }
    });
  }

  bool _isSelected(int day) {
    if (_startDate == null) return false;
    final date = DateTime(_currentMonth.year, _currentMonth.month, day);
    
    if (_endDate == null) {
      return DateUtils.isSameDay(date, _startDate);
    }
    
    return (date.isAfter(_startDate!) || DateUtils.isSameDay(date, _startDate)) &&
           (date.isBefore(_endDate!) || DateUtils.isSameDay(date, _endDate));
  }

  bool _isStartOrEnd(int day) {
    if (_startDate == null) return false;
    final date = DateTime(_currentMonth.year, _currentMonth.month, day);
    return DateUtils.isSameDay(date, _startDate) || 
           (_endDate != null && DateUtils.isSameDay(date, _endDate));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: const Icon(Icons.calendar_today, color: Colors.green),
        title: const Text(
          'View Past Logs',
          style: TextStyle(
            color: Colors.black,
            fontWeight: FontWeight.w600,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text(
              'Cancel',
              style: TextStyle(
                color: Colors.black,
                fontSize: 16,
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Month Navigation
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'February 2026',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black54,
                  ),
                ),
                Row(
                  children: const [
                    Icon(Icons.chevron_left, color: Colors.green),
                    SizedBox(width: 16),
                    Icon(Icons.chevron_right, color: Colors.green),
                  ],
                ),
              ],
            ),
          ),
          // Days Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: const [
                Text('SUN', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('MON', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('TUE', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('WED', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('THU', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('FRI', style: TextStyle(fontWeight: FontWeight.bold)),
                Text('SAT', style: TextStyle(fontWeight: FontWeight.bold)),
              ],
            ),
          ),
          const SizedBox(height: 8),
          // Calendar Grid
          Expanded(
            child: GridView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 7,
                mainAxisSpacing: 8,
                crossAxisSpacing: 8,
              ),
              itemCount: 42, // 6 weeks
              itemBuilder: (context, index) {
                // Adjust index based on first weekday
                // DateTime.weekday returns 1 for Mon, 7 for Sun.
                // We want Sun to be index 0.
                // So if 1st is Mon (1), offset should be 1.
                // If 1st is Sun (7), offset should be 0.
                final int firstDayOffset = _firstWeekday == 7 ? 0 : _firstWeekday;
                final int day = index - firstDayOffset + 1;
                
                if (day < 1 || day > _daysInMonth) {
                  return const SizedBox();
                }

                final isSelected = _isSelected(day);
                final isStartOrEnd = _isStartOrEnd(day);
                final isCurrentDay = day == 25; // Hardcoded "today"

                return GestureDetector(
                  onTap: () => _onDayTapped(day),
                  child: Container(
                    decoration: BoxDecoration(
                      color: isStartOrEnd ? Colors.green : (isSelected ? Colors.green.shade50 : Colors.white),
                      borderRadius: isStartOrEnd ? BorderRadius.circular(4) : null,
                      border: (!isSelected && !isStartOrEnd) ? Border.all(color: Colors.grey.shade200) : null,
                    ),
                    child: Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            '$day',
                            style: TextStyle(
                              color: isStartOrEnd ? Colors.white : Colors.black87,
                              fontWeight: isStartOrEnd ? FontWeight.bold : FontWeight.normal,
                            ),
                          ),
                          if (isCurrentDay && !isSelected)
                            Container(
                              margin: const EdgeInsets.only(top: 4),
                              width: 4,
                              height: 4,
                              decoration: const BoxDecoration(
                                color: Colors.green,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          // Footer
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  offset: const Offset(0, -2),
                  blurRadius: 4,
                ),
              ],
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                        const SizedBox(width: 8),
                        Text(
                          _startDate != null 
                              ? '${_startDate!.month.toString().padLeft(2, '0')}/${_startDate!.day.toString().padLeft(2, '0')}/${_startDate!.year} - ${_endDate?.month.toString().padLeft(2, '0')}/${_endDate?.day.toString().padLeft(2, '0')}/${_endDate?.year ?? "..."}'
                              : 'Select Date Range',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                    TextButton(
                      onPressed: () {
                        setState(() {
                          _startDate = null;
                          _endDate = null;
                        });
                      },
                      child: const Text(
                        'Clear',
                        style: TextStyle(
                          color: Colors.green,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: (_startDate != null && _endDate != null) 
                        ? () {
                            Navigator.of(context).pop(DateTimeRange(start: _startDate!, end: _endDate!));
                          }
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text(
                      'Search Log(s)',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
