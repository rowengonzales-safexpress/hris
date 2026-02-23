import 'package:app/screens/inbound/receipt/receipt_list.dart';
import 'package:app/screens/report/batch_info.dart';
import 'package:app/screens/report/item_info.dart';
import 'package:app/screens/report/item_inventory_info.dart';
import 'package:app/screens/report/item_inventory_report.dart';
import 'package:app/screens/username_screen.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:app/providers/auth_provider.dart';

class HomeScreen extends StatelessWidget {
    static const String routeName = '/home'; 
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: FutureBuilder<String?>(
          future: Provider.of<AuthProvider>(context, listen: false)
              .getWarehouseName(),
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Text('Loading...');
            }
            return Text(snapshot.data ?? 'Home');
          },
        ),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            DrawerHeader(
              decoration: BoxDecoration(
                color: Theme.of(context).primaryColor,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    authProvider.username ?? 'User',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                    ),
                  ),
                  Text(
                    authProvider.email ?? '',
                    style: const TextStyle(
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
            const ListTile(
              leading: Icon(Icons.home),
              title: Text('Dashboard'),
            ),
            const Divider(),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.0),
              child: Text(
                'Inbound Management',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            _buildDrawerItem(context, Icons.receipt, 'Receipts', onTap: () {
              Navigator.push(context,
                  MaterialPageRoute(builder: (context) => const ReceiptList()));
            }),
            _buildDrawerItem(context, Icons.label, 'Batch Labeling Actual'),
            _buildDrawerItem(
                context, Icons.verified, 'Batch Labeling Validated'),
            const Divider(),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.0),
              child: Text(
                'Stock Management',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            _buildDrawerItem(context, Icons.pallet, 'Stock Transfer by pallet'),
            _buildDrawerItem(
                context, Icons.location_on, 'Stock transfer by location'),
            _buildDrawerItem(context, Icons.calculate, 'Cycle Count'),
            _buildDrawerItem(context, Icons.move_to_inbox, 'Stage Movement'),
            _buildDrawerItem(context, Icons.inventory, 'Stage Putaway'),
            _buildDrawerItem(
                context, Icons.repeat, 'Stock Replenishment-System'),
            _buildDrawerItem(context, Icons.person, 'Stock Replenishment-User'),
            const Divider(),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.0),
              child: Text(
                'Movement Confirmation',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            _buildDrawerItem(context, Icons.warehouse, 'Putaway'),
            _buildDrawerItem(
                context, Icons.transfer_within_a_station, 'Stock Transfer'),
            _buildDrawerItem(context, Icons.repeat_one, 'Stock Replenishment'),
            _buildDrawerItem(context, Icons.exit_to_app, 'Stock Issuance'),
            const Divider(),
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.0),
              child: Text(
                'Reports & Inquiry',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            _buildDrawerItem(context, Icons.info, 'Batch Info', onTap: () {
              Navigator.push(context,
                  MaterialPageRoute(builder: (context) => const BatchInfo()));
            }),
            _buildDrawerItem(context, Icons.inventory_2, 'Item Info',
                onTap: () {
              Navigator.push(context,
                  MaterialPageRoute(builder: (context) => const ItemInfo()));
            }),
            _buildDrawerItem(context, Icons.list_alt, 'Item Inventory Info',
                onTap: () {
              Navigator.push(
                  context,
                  MaterialPageRoute(
                      builder: (context) => const ItemInventoryInfo()));
            }),
            _buildDrawerItem(context, Icons.assignment, 'Item Inventory Report',
                onTap: () {
              Navigator.push(
                  context,
                  MaterialPageRoute(
                      builder: (context) => const ItemInventoryReport()));
            }),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () async {
                Navigator.of(context).pop(); // Close the drawer first
                authProvider.logout();
                // Navigate to username screen after logout completes
                Navigator.of(context).pushNamedAndRemoveUntil(
                  '/domain',
                  (Route<dynamic> route) => false,
                );
              },
            ),
          ],
        ),
      ),
      body: const DashboardView(),
      bottomNavigationBar: BottomAppBar(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            IconButton(
              icon: const Icon(Icons.home),
              onPressed: () {},
            ),
            IconButton(
              icon: const Icon(Icons.dashboard),
              onPressed: () {},
            ),
            IconButton(
              icon: const Icon(Icons.logout),
              onPressed: () {
                authProvider.logout();
                Navigator.of(context).pop();
                Navigator.of(context).pushNamedAndRemoveUntil(
                  '/domain',
                  (Route<dynamic> route) => false,
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerItem(BuildContext context, IconData icon, String title,
      {VoidCallback? onTap}) {
    return ListTile(
      leading: Icon(icon),
      title: Text(title),
      onTap: onTap ??
          () {
            // Handle navigation to each section
            Navigator.pop(context); // Close the drawer
          },
    );
  }
}

class DashboardView extends StatelessWidget {
  const DashboardView({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Dashboard',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 20),
          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisCount: 2,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
            children: [
              _buildDashboardCard(Icons.receipt, 'Receipts'),
              _buildDashboardCard(Icons.label, 'Batch Labeling'),
              _buildDashboardCard(Icons.pallet, 'Stock Transfer'),
              _buildDashboardCard(Icons.location_on, 'Stock by Location'),
              _buildDashboardCard(Icons.calculate, 'Cycle Count'),
              _buildDashboardCard(Icons.warehouse, 'Putaway'),
              _buildDashboardCard(Icons.repeat, 'Replenishment'),
              _buildDashboardCard(Icons.assignment, 'Reports'),
            ],
          ),
          const SizedBox(height: 20),
          const Text(
            'Recent Activities',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 10),
          // Add recent activities list here
        ],
      ),
    );
  }

  Widget _buildDashboardCard(IconData icon, String title) {
    return Card(
      elevation: 4,
      child: InkWell(
        onTap: () {
          // Handle card tap
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 40),
              const SizedBox(height: 10),
              Text(
                title,
                style: const TextStyle(fontSize: 16),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
