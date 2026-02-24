import 'package:flutter/material.dart';
import 'leave_summary_page.dart';

class LeavePage extends StatelessWidget {
  const LeavePage({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Leave'),
          centerTitle: true,
          elevation: 0.5,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed: () {
              Navigator.of(context).pop();
            },
          ),
          bottom: const TabBar(
            indicatorColor: Colors.green,
            labelColor: Colors.green,
            unselectedLabelColor: Colors.grey,
            tabs: [
              Tab(text: 'Pending'),
              Tab(text: 'Approved'),
              Tab(text: 'Rejected'),
            ],
          ),
        ),
        body: SafeArea(
          child: TabBarView(
            children: [
              _PendingTab(),
              _ApprovedTab(),
              _RejectedTab(),
            ],
          ),
        ),
        floatingActionButton: FloatingActionButton(
          onPressed: () {},
          backgroundColor: Colors.green,
          child: const Icon(Icons.add),
        ),
      ),
    );
  }
}

class _PendingTab extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: const [
        _LeaveCard(
          statusLabel: 'PENDING',
          dateRange: 'FEB. 25, 2026 - FEB. 25, 2026',
          daySummary: '1 whole day',
          type: 'Vacation',
          filedDateLabel: 'Filed on 02/20/2026',
          daysWithPay: '1 Day With Pay',
          note: 'family outing',
        ),
      ],
    );
  }
}

class _ApprovedTab extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _LeaveCard(
          statusLabel: 'APPROVED',
          dateRange: 'FEB. 19, 2026 - FEB. 19, 2026',
          daySummary: '1 whole day',
          type: 'Sick',
          filedDateLabel: 'Filed on 02/21/2026',
          daysWithPay: '1 Day With Pay',
          note: 'my son sick need medical attention',
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => const LeaveSummaryPage(),
              ),
            );
          },
        ),
        const _LeaveCard(
          statusLabel: 'APPROVED',
          dateRange: 'JAN. 30, 2026 - JAN. 30, 2026',
          daySummary: '1 whole day',
          type: 'Sick',
          filedDateLabel: 'Filed on 01/31/2026',
          daysWithPay: '1 Day With Pay',
          note: 'My son is sick, need to take him to the doctor.',
        ),
        const _LeaveCard(
          statusLabel: 'APPROVED',
          dateRange: 'FEB. 03, 2026 - FEB. 05, 2026',
          daySummary: '3 whole days',
          type: 'Vacation',
          filedDateLabel: 'Filed on 01/26/2026',
          daysWithPay: '3 Days With Pay',
          note: 'personal reasons',
        ),
      ],
    );
  }
}

class _RejectedTab extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: const [
        _LeaveCard(
          statusLabel: 'CANCELLED',
          dateRange: 'FEB. 04, 2025 - FEB. 11, 2025',
          daySummary: '7 whole days',
          type: 'Paternity',
          filedDateLabel: 'Filed on 02/05/2025',
          daysWithPay: '7 Days Without Pay',
          note:
              'I am requesting paternity leave to bond with my newborn child and support my partner during this time.',
        ),
        _LeaveCard(
          statusLabel: 'REJECTED',
          dateRange: 'JAN. 10, 2024 - JAN. 10, 2024',
          daySummary: '1 whole day',
          type: 'Sick',
          filedDateLabel: 'Filed on 01/11/2024',
          daysWithPay: '1 Day Without Pay',
          note: 'due to tonsillitis and fever',
        ),
      ],
    );
  }
}

class _LeaveCard extends StatelessWidget {
  final String statusLabel;
  final String dateRange;
  final String daySummary;
  final String type;
  final String filedDateLabel;
  final String daysWithPay;
  final String note;
  final VoidCallback? onTap;

  const _LeaveCard({
    super.key,
    required this.statusLabel,
    required this.dateRange,
    required this.daySummary,
    required this.type,
    required this.filedDateLabel,
    required this.daysWithPay,
    required this.note,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 8),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'REQUEST FOR',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      dateRange,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      daySummary,
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.grey,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      type,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: statusLabel == 'REJECTED'
                        ? Colors.red.shade50
                        : statusLabel == 'CANCELLED'
                            ? Colors.grey.shade200
                            : Colors.green.shade50,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    statusLabel,
                    style: TextStyle(
                      fontSize: 11,
                      color: statusLabel == 'REJECTED'
                          ? Colors.red
                          : statusLabel == 'CANCELLED'
                              ? Colors.grey.shade700
                              : Colors.green,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(
                  Icons.calendar_today_outlined,
                  size: 16,
                  color: Colors.grey,
                ),
                const SizedBox(width: 6),
                Text(
                  filedDateLabel,
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(
                  Icons.payments_outlined,
                  size: 16,
                  color: Colors.grey,
                ),
                const SizedBox(width: 6),
                Text(
                  daysWithPay,
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(
                  Icons.notes_outlined,
                  size: 16,
                  color: Colors.grey,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    note,
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.grey,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

