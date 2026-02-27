import 'package:flutter/material.dart';

class PayslipsPage extends StatelessWidget {
  const PayslipsPage({super.key});

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
          'Payslips',
          style: TextStyle(
            color: Colors.black,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildMonthSection(
            'FEBRUARY 2026',
            [
              _buildPayslipCard(
                'Payroll for 2/1/2026 - 2/15/2026',
                isLatest: true,
              ),
              _buildPayslipCard('Payroll for 1/16/2026 - 1/31/2026'),
            ],
          ),
          const SizedBox(height: 24),
          _buildMonthSection(
            'JANUARY 2026',
            [
              _buildPayslipCard('Payroll for 1/1/2026 - 1/15/2026'),
              _buildPayslipCard('Payroll for 12/16/2025 - 12/31/2025'),
            ],
          ),
          const SizedBox(height: 24),
          _buildMonthSection(
            'DECEMBER 2025',
            [
              _buildPayslipCard('Payroll for 12/1/2025 - 12/15/2025'),
              _buildPayslipCard('Payroll for 11/16/2025 - 11/30/2025'),
            ],
          ),
          const SizedBox(height: 24),
          _buildMonthSection(
            'NOVEMBER 2025',
            [
              _buildPayslipCard('Payroll for 11/1/2025 - 11/15/2025'),
              _buildPayslipCard('Payroll for 10/16/2025 - 10/31/2025'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMonthSection(String title, List<Widget> children) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: Colors.grey,
            fontWeight: FontWeight.bold,
            fontSize: 12,
            letterSpacing: 1.0,
          ),
        ),
        const SizedBox(height: 12),
        ...children.map((child) => Padding(
              padding: const EdgeInsets.only(bottom: 12.0),
              child: child,
            )),
      ],
    );
  }

  Widget _buildPayslipCard(String title, {bool isLatest = false}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (isLatest) ...[
                  Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.green.shade100,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Text(
                      'LATEST',
                      style: TextStyle(
                        color: Colors.green,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          const Icon(Icons.chevron_right, color: Colors.grey),
        ],
      ),
    );
  }
}
