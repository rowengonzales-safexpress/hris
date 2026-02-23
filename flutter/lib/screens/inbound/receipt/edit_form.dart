import 'package:app/models/receipt_model.dart';
import 'package:app/providers/received_shipment.dart';
import 'package:flutter/material.dart';
import 'package:flutter_form_builder/flutter_form_builder.dart';
import 'package:form_builder_validators/form_builder_validators.dart';
import 'package:http/http.dart' as http;

class EditForm extends StatefulWidget {
  final ReceivedShipment receipt;
  const EditForm({super.key, required this.receipt});

  @override
  State<EditForm> createState() => _EditFormState();
}

class _EditFormState extends State<EditForm> {
  final _formKey = GlobalKey<FormBuilderState>();
  Received receiptHandler = Received();
  late http.Response response;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Received Shipment'),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
         bottomNavigationBar: MaterialButton(
        color: Colors.teal,
        textColor: Colors.white,
        padding: const EdgeInsets.all(20),
        onPressed: () {
         
        },
        child: const Text('Update'),
      ),
        body: Padding(
          padding: const EdgeInsets.all(10),
          child: FormBuilder(
              key: _formKey,
              initialValue: {
                'receiptno': widget.receipt.receiptno,
                'supname': widget.receipt.supname,
              },
              child: Column(
                children: [
                  FormBuilderTextField(
                    name: 'receiptno',
                    decoration: const InputDecoration(labelText: 'Receipt No'),
                    validator: FormBuilderValidators.compose([
                      FormBuilderValidators.required(),
                    ]),
                  ),
                  const SizedBox(height: 10),
                  FormBuilderTextField(
                    name: 'supname',
                    decoration: const InputDecoration(labelText: 'Supplier Name'),
                    validator: FormBuilderValidators.compose([
                      FormBuilderValidators.required(),
                    ]),
                  ),
                ],
              ))),
    );
  }
}
