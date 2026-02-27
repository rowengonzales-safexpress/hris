import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class EmployeeDetailPage extends StatelessWidget {
  const EmployeeDetailPage({super.key});

  @override
  Widget build(BuildContext context) {
    final employee =
        ModalRoute.of(context)!.settings.arguments as Map<String, String>;

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leadingWidth: 100, // Allow more width for "Back" text
        leading: GestureDetector(
          onTap: () => Navigator.of(context).pop(),
          child: Row(
            children: const [
              SizedBox(width: 8),
              Icon(Icons.arrow_back, color: Colors.black54),
              SizedBox(width: 4),
              Text(
                "Back",
                style: TextStyle(
                  color: Colors.black54,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
        child: Column(
          children: [
            const SizedBox(height: 20),
            // Avatar
            CircleAvatar(
              radius: 40,
              backgroundColor: Colors.grey.shade200,
              child: Text(
                employee['initials']!,
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.green.shade900,
                ),
              ),
            ),
            const SizedBox(height: 24),
            // Name
            Text(
              employee['name']!,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.green.shade900,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            // Role/Department (Subtitle under name)
            Text(
              employee['role']!,
              style: const TextStyle(
                fontSize: 14,
                color: Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            // Action Buttons
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildActionButton(
                  context,
                  icon: Icons.call,
                  label: "Call",
                  color: Colors.green,
                  onTap: () {
                    // Implement call functionality
                  },
                ),
                _buildActionButton(
                  context,
                  icon: Icons.send,
                  label: "Message",
                  color: Colors.blue,
                  onTap: () {
                    // Implement message functionality
                  },
                ),
                _buildActionButton(
                  context,
                  icon: Icons.email,
                  label: "Email",
                  color: Colors.purple.shade400,
                  onTap: () {
                    // Implement email functionality
                  },
                ),
              ],
            ),
            const SizedBox(height: 40),
            // Details List
            _buildDetailItem(
              context,
              icon: Icons.phone,
              value: employee['mobile']!,
              label: "Mobile Number",
              showCopy: true,
            ),
            _buildDetailItem(
              context,
              icon: Icons.email_outlined,
              value: employee['email']!,
              label: "Email Address",
              showCopy: true,
            ),
            _buildDetailItem(
              context,
              icon: Icons.location_on,
              value: employee['company']!,
              label: "Company",
              showCopy: false,
            ),
            _buildDetailItem(
              context,
              icon: Icons.people_outline,
              value: employee['department']!,
              label: "Department",
              showCopy: false,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButton(
    BuildContext context, {
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Column(
      children: [
        InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(30),
          child: Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: Colors.white, size: 24),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          label,
          style: const TextStyle(
            color: Colors.black54,
            fontSize: 13,
          ),
        ),
      ],
    );
  }

  Widget _buildDetailItem(
    BuildContext context, {
    required IconData icon,
    required String value,
    required String label,
    required bool showCopy,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 24),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: Colors.blueGrey, size: 20),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 15,
                    color: Colors.green.shade900,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          if (showCopy)
            InkWell(
              onTap: () {
                Clipboard.setData(ClipboardData(text: value));
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('$label copied to clipboard')),
                );
              },
              borderRadius: BorderRadius.circular(4),
              child: const Padding(
                padding: EdgeInsets.all(4.0),
                child: Icon(
                  Icons.content_copy, // Use content_copy icon
                  color: Colors.grey,
                  size: 18,
                ),
              ),
            ),
        ],
      ),
    );
  }
}
