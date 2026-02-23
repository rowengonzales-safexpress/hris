import 'package:app/db/database_helper.dart';
import 'package:app/screens/username_screen.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';


class DomainScreen extends StatefulWidget {
  static const String routeName = '/domain'; 
  const DomainScreen({super.key});

  @override
  State<DomainScreen> createState() => _DomainScreenState();
}

class _DomainScreenState extends State<DomainScreen> {
 bool _isLoading = false;
  bool _dbInitialized = false;
  final TextEditingController _domainController = TextEditingController();
  final DatabaseHelper _dbHelper = DatabaseHelper.instance;
  List<String> _savedDomains = [];


  @override
  void initState() {
    super.initState();
     _initializeDatabase();
  }

   Future<void> _initializeDatabase() async {
    setState(() => _isLoading = true);
    try {
      await DatabaseHelper.instance.database; // Initialize database
      setState(() => _dbInitialized = true);
      await _loadSavedDomains();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to initialize database: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadSavedDomains() async {
    setState(() => _isLoading = true);
    _savedDomains = await _dbHelper.getDomains();
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Login to your domain')),
      body: _buildDomainForm(),
    );
  }

  Widget _buildDomainForm() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Form(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (_savedDomains.isNotEmpty) ...[
              const Text('Saved Domains:',
                  style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 10),
              SizedBox(
                height: 100,
                child: ListView.builder(
                  itemCount: _savedDomains.length,
                  itemBuilder: (context, index) => Card(
                    child: ListTile(
                      title: Text(_savedDomains[index]),
                      trailing: IconButton(
                        icon: const Icon(Icons.delete),
                        onPressed: () => _deleteDomain(_savedDomains[index]),
                      ),
                      onTap: () {
                        _domainController.text = _savedDomains[index];
                        _submit();
                      },
                    ),
                  ),
                ),
              ),
              const Divider(),
            ],
            TextFormField(
              controller: _domainController,
              decoration: const InputDecoration(
                labelText: 'Domain Name or IP Address',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.dns),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter domain name or IP address';
                }
                return null;
              },
            ),
            const SizedBox(height: 20),
            Row(
              children: [
           
                Expanded(
                  child:  ElevatedButton.icon(
                          onPressed: _submit,
                          label: const Text('Next'),
                          icon: const Icon(Icons.arrow_forward),
                        ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }



  Future<void> _submit() async {
    if (_domainController.text.isEmpty) return;
  final prefs = await SharedPreferences.getInstance();
        await prefs.setString('address', _domainController.text);
    setState(() => _isLoading = true);
    
    // Save the domain to SQLite
    await _dbHelper.insertDomain(_domainController.text);
    
    // Update AppConfig with the new domain
    // You'll need to modify your navigation to pass this to the next screen
    // or update a shared state/provider
    
    setState(() => _isLoading = false);
    
    // Navigate to UsernameScreen with the domain
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => UsernameScreen(domain: _domainController.text),
      ),
    );
  }

  Future<void> _deleteDomain(String domain) async {
    await _dbHelper.deleteDomain(domain);
    await _loadSavedDomains();
  }

  @override
  void dispose() {
    _domainController.dispose();
    super.dispose();
  }
}