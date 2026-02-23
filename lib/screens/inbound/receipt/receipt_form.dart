import 'package:app/models/receipt_model.dart';
import 'package:flutter/material.dart';
import 'package:flutter_form_builder/flutter_form_builder.dart';
import 'package:form_builder_validators/form_builder_validators.dart';

class ReceiptForm extends StatefulWidget {
  const ReceiptForm({super.key});

  @override
  State<ReceiptForm> createState() => _ReceiptFormState();
}

class _ReceiptFormState extends State<ReceiptForm> {
  final _formKey = GlobalKey<FormBuilderState>();

  // Controllers for each field
  final TextEditingController _receiptnoController = TextEditingController();

  DateTime? _receiptdate;
  final TextEditingController _receipttypeController = TextEditingController();
  final TextEditingController _referencenoController = TextEditingController();
  final TextEditingController _documentnoController = TextEditingController();
  DateTime? _documentdate;
  final TextEditingController _supplierIdController = TextEditingController();
  final TextEditingController _supnameController = TextEditingController();
  final TextEditingController _deliverynoController = TextEditingController();
  final TextEditingController _truckerController = TextEditingController();
  final TextEditingController _platenoController = TextEditingController();
  final TextEditingController _cvnoController = TextEditingController();
  final TextEditingController _driverController = TextEditingController();
  DateTime? _arrivaldate;
  DateTime? _unloadingstart;
  DateTime? _unloadingfinish;
  final TextEditingController _remarksController = TextEditingController();

  Future<void> _pickDate(BuildContext context, DateTime? initial,
      Function(DateTime) onPicked) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: initial ?? DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
    );
    if (picked != null) onPicked(picked);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Receipt Form'),
        backgroundColor: const Color.fromARGB(255, 15, 148, 20),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: FormBuilder(
          key: _formKey,
          child: Column(
            children: [
              FormBuilderTextField(
                name: 'receiptno',
                controller: _receiptnoController,
                decoration: const InputDecoration(labelText: 'Receipt No'),
                validator: FormBuilderValidators.compose([
                  FormBuilderValidators.required(),
                ]),
              ),
              ListTile(
                title: Text(_receiptdate == null
                    ? 'Select Receipt Date'
                    : _receiptdate.toString()),
                trailing: const Icon(Icons.calendar_today),
                onTap: () => _pickDate(context, _receiptdate,
                    (d) => setState(() => _receiptdate = d)),
              ),
              FormBuilderTextField(
                name: 'receipttype',
                controller: _receipttypeController,
                decoration: const InputDecoration(labelText: 'Receipt Type'),
              ),
              FormBuilderTextField(
                name: 'referenceno',
                controller: _referencenoController,
                decoration: const InputDecoration(labelText: 'Reference No'),
              ),
              FormBuilderTextField(
                name: 'documentno',
                controller: _documentnoController,
                decoration: const InputDecoration(labelText: 'Document No'),
              ),
              ListTile(
                title: Text(_documentdate == null
                    ? 'Select Document Date'
                    : _documentdate.toString()),
                trailing: const Icon(Icons.calendar_today),
                onTap: () => _pickDate(context, _documentdate,
                    (d) => setState(() => _documentdate = d)),
              ),
              FormBuilderTextField(
                  name: 'supplierId',
                  controller: _supplierIdController,
                  decoration: const InputDecoration(labelText: 'Supplier ID'),
                  keyboardType: TextInputType.number),
              FormBuilderTextField(
                  name: 'supname',
                  controller: _supnameController,
                  decoration:
                      const InputDecoration(labelText: 'Supplier Name')),
              FormBuilderTextField(
                  name: 'deliveryno',
                  controller: _deliverynoController,
                  decoration: const InputDecoration(labelText: 'Delivery No')),
              FormBuilderTextField(
                  name: 'trucker',
                  controller: _truckerController,
                  decoration: const InputDecoration(labelText: 'Trucker')),
              FormBuilderTextField(
                  name: 'plateno',
                  controller: _platenoController,
                  decoration: const InputDecoration(labelText: 'Plate No')),
              FormBuilderTextField(
                  name: 'cvno',
                  controller: _cvnoController,
                  decoration: const InputDecoration(labelText: 'CV No')),
              FormBuilderTextField(
                  name: 'driver',
                  controller: _driverController,
                  decoration: const InputDecoration(labelText: 'Driver')),
              ListTile(
                title: Text(_arrivaldate == null
                    ? 'Select Arrival Date'
                    : _arrivaldate.toString()),
                trailing: const Icon(Icons.calendar_today),
                onTap: () => _pickDate(context, _arrivaldate,
                    (d) => setState(() => _arrivaldate = d)),
              ),
              ListTile(
                title: Text(_unloadingstart == null
                    ? 'Select Unloading Start'
                    : _unloadingstart.toString()),
                trailing: const Icon(Icons.calendar_today),
                onTap: () => _pickDate(context, _unloadingstart,
                    (d) => setState(() => _unloadingstart = d)),
              ),
              ListTile(
                title: Text(_unloadingfinish == null
                    ? 'Select Unloading Finish'
                    : _unloadingfinish.toString()),
                trailing: const Icon(Icons.calendar_today),
                onTap: () => _pickDate(context, _unloadingfinish,
                    (d) => setState(() => _unloadingfinish = d)),
              ),
              FormBuilderTextField(
                  name: 'remarks',
                  controller: _remarksController,
                  decoration: const InputDecoration(labelText: 'Remarks')),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  if (_formKey.currentState!.saveAndValidate()) {
                    // Save logic here
                  }
                },
                child: const Text('Save'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
