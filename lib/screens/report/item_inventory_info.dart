import 'package:flutter/material.dart';

class ItemInventoryInfo extends StatefulWidget {
  const ItemInventoryInfo({super.key});

  @override
  State<ItemInventoryInfo> createState() => _ItemInventoryInfoState();
}

class _ItemInventoryInfoState extends State<ItemInventoryInfo> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Item Inventory Info'),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: Text(
          'Item inventory information will be displayed here.',
          style: Theme.of(context).textTheme.titleLarge,
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Action to add new item inventory info
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}