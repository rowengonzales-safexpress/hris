import 'package:flutter/material.dart';

class DirectoryPage extends StatelessWidget {
  const DirectoryPage({super.key});

  @override
  Widget build(BuildContext context) {
    // Dummy data matching the image
    final List<Map<String, String>> employees = [
      {
        'name': 'ROWEN GONZALES',
        'role': 'INFORMATION TECHNOLOGY',
        'department': 'OPERATIONS',
        'mobile': '09172510499',
        'email': 'rowen.gonzales@safexpress.com.ph',
        'company': 'Safexpress Logistics Inc.',
        'initials': 'RG',
      },
      // Adding a few more for demonstration
      {
        'name': 'JUAN DELA CRUZ',
        'role': 'HUMAN RESOURCES',
        'department': 'HR',
        'mobile': '09171234567',
        'email': 'juan.delacruz@safexpress.com.ph',
        'company': 'Safexpress Logistics Inc.',
        'initials': 'JD',
      },
      {
        'name': 'MARIA CLARA',
        'role': 'ACCOUNTING',
        'department': 'FINANCE',
        'mobile': '09179876543',
        'email': 'maria.clara@safexpress.com.ph',
        'company': 'Safexpress Logistics Inc.',
        'initials': 'MC',
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
          'Employee Directory',
          style: TextStyle(
            color: Colors.black, // Dark green/black in image
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search, color: Colors.green),
            onPressed: () {},
          ),
        ],
      ),
      body: ListView.builder(
        padding: const EdgeInsets.only(top: 8),
        itemCount: employees.length,
        itemBuilder: (context, index) {
          final employee = employees[index];
          return InkWell(
            onTap: () {
              Navigator.of(context).pushNamed(
                '/employeeDetail',
                arguments: employee,
              );
            },
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: Colors.grey.shade200,
                    child: Text(
                      employee['initials']!,
                      style: const TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          employee['name']!,
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Colors.green.shade900,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          employee['role']!,
                          style: const TextStyle(
                            fontSize: 12,
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
