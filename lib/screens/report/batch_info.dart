import 'package:flutter/material.dart';

class BatchInfo extends StatefulWidget {
  const BatchInfo({super.key});

  @override
  State<BatchInfo> createState() => _BatchInfoState();
}

class _BatchInfoState extends State<BatchInfo> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Batch Information'),
         backgroundColor: const Color.fromARGB(255, 15, 148, 20),
          foregroundColor: Colors.white,
      ),
      body: Center(
        child: Text(
          'Batch information will be displayed here.',
          style: Theme.of(context).textTheme.titleLarge,
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Action to add new batch info
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
