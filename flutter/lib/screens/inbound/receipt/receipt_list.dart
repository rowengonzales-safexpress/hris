import 'package:app/models/receipt_model.dart';
import 'package:app/providers/received_shipment.dart';
import 'package:app/screens/inbound/receipt/edit_form.dart';
import 'package:app/screens/inbound/receipt/receipt_form.dart';
import 'package:flutter/material.dart';
import 'package:skeletonizer/skeletonizer.dart';

class ReceiptList extends StatefulWidget {
  const ReceiptList({super.key});

  @override
  State<ReceiptList> createState() => _ReceiptListState();
}

class _ReceiptListState extends State<ReceiptList> {
  Received receiptHandler = Received();
  late List<ReceivedShipment> data = [];
  bool _isLoading = true;

  void getData() async {
    data = await receiptHandler.fetchReceivedShipment();
    setState(() {
      _isLoading = false;
    });
  }

  @override
  void initState() {
    super.initState();
    getData();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Receipt List'),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
      floatingActionButton: Column(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          const SizedBox(height: 10),
          FloatingActionButton(
            backgroundColor: Colors.teal,
            foregroundColor: Colors.white,
            child: const Icon(Icons.add),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const ReceiptForm()),
              );
            },
          ),
        ],
      ),
      body: Skeletonizer(
        enabled: _isLoading,
        containersColor: Colors.grey[200],
        // Customize the skeleton appearance
        effect: ShimmerEffect(
          baseColor: Colors.grey[300]!,
          highlightColor: Colors.grey[100]!,
        ),
        child: ListView.builder(
          padding: const EdgeInsets.all(8),
          itemCount: _isLoading ? 6 : data.length,
          itemBuilder: (BuildContext context, int index) {
            if (_isLoading) {
              return Container(
                margin: const EdgeInsets.symmetric(vertical: 8),
                child: const ListTile(
                  leading: CircleAvatar(radius: 20),
                  title: Text('Loading receipt number',
                      style: TextStyle(height: 1.5)),
                  subtitle: Text('Loading document number',
                      style: TextStyle(height: 1.5)),
                ),
              );
            } else {
             
              return Dismissible(
                key: ValueKey(data[index].id),
                background: Container(
                  color: Colors.green,
                  alignment: Alignment.centerRight,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child:
                      const Icon(Icons.save, color: Colors.white, size: 32),
                ),
                secondaryBackground: Container(
                  color: Colors.red,

                  alignment: Alignment.centerLeft,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: const Icon(Icons.delete, color: Colors.white, size: 32),
                ),
                confirmDismiss: (direction) async {
                  if (direction == DismissDirection.endToStart) {
                    // Swiped left: green trash
                    return await showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Delete?'),
                        content: const Text('Do you want to delete this item?'),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.of(ctx).pop(false),
                            child: const Text('Cancel'),
                          ),
                          TextButton(
                            onPressed: () => Navigator.of(ctx).pop(true),
                            child: const Text('Yes'),
                          ),
                        ],
                      ),
                    );
                  } else if (direction == DismissDirection.startToEnd) {
                    // Swiped right: red X
                    return await showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Complete?'),
                        content: const Text('Do you want to mark this item as complete?'),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.of(ctx).pop(false),
                            child: const Text('Cancel'),
                          ),
                          TextButton(
                            onPressed: () => Navigator.of(ctx).pop(true),
                            child: const Text('Delete'),
                          ),
                        ],
                      ),
                    );
                  }
                  return false;
                },
                onDismissed: (direction) {
                  setState(() {
                    data.removeAt(index);
                  });
                  // Add your logic for mark as complete or delete here if needed
                },
                child: Card(
                  margin:
                      const EdgeInsets.symmetric(vertical: 4, horizontal: 8),
                  elevation: 2,
                  child: ListTile(
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => EditForm(receipt: data[index]),
                      ),
                    ),
                    leading: CircleAvatar(
                      backgroundColor: Colors.teal[100],
                      child: Text("${data[index].id}",
                          style: const TextStyle(color: Colors.teal)),
                    ),
                    title: Text(
                      data[index].receiptno,
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    subtitle: Text('Supplier: ${data[index].supname}'
                        '\nDocument No: ${data[index].documentno}'),
                    trailing: const Icon(Icons.chevron_right),
                  ),
                ),
              );
            }
          },
        ),
      ),
    );
  }
}
