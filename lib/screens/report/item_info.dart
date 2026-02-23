import 'package:flutter/material.dart';

class ItemInfo extends StatefulWidget {
  const ItemInfo({super.key});

  @override
  State<ItemInfo> createState() => _ItemInfoState();
}

class _ItemInfoState extends State<ItemInfo> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Item Information'),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: Text(
          'Item information will be displayed here.',
          style: Theme.of(context).textTheme.titleLarge,
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Action to add new item info
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
