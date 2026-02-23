import 'package:flutter/material.dart';

class ItemInventoryReport extends StatefulWidget {
  const ItemInventoryReport({super.key});

  @override
  State<ItemInventoryReport> createState() => _ItemInventoryReportState();
}

class _ItemInventoryReportState extends State<ItemInventoryReport> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Item Inventory Report'),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: Text(
          'Item inventory report will be displayed here.',
          style: Theme.of(context).textTheme.titleLarge,
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Action to generate new report
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}