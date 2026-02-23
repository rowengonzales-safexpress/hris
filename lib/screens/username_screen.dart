import 'package:app/screens/domain_screen.dart';
import 'package:app/screens/password_screen.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:app/providers/auth_provider.dart';

class UsernameScreen extends StatefulWidget {
  //const UsernameScreen({super.key});
  final String domain;
  const UsernameScreen({Key? key, required this.domain}) : super(key: key);
  @override
  _UsernameScreenState createState() => _UsernameScreenState();
}

class _UsernameScreenState extends State<UsernameScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();

  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Login'),
      actions: [
        IconButton(
          icon: const Icon(Icons.connect_without_contact_rounded),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const DomainScreen(),
              ),
            );
          },
        ),
      ],),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                'Domain: ${widget.domain}',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              TextFormField(
                controller: _usernameController,
                decoration: const InputDecoration(
                  labelText: 'Username or Email',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter username or email';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 20),
              _isLoading
                  ? const CircularProgressIndicator()
                  : ElevatedButton(
                      onPressed: _submit,
                      child: const Text('Proceed'),
                    ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submit() async {
  if (!_formKey.currentState!.validate()) return;
  
  setState(() => _isLoading = true);
  
  final authProvider = Provider.of<AuthProvider>(context, listen: false);
  final isVerified = await authProvider.verifyUser(_usernameController.text);
  
  setState(() => _isLoading = false);
  
  if (isVerified && mounted) {
    // Verification successful - navigate to password screen
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const PasswordScreen()),
    );
  } else if (mounted) {
    // Show error message
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('User not found')),
    );
  }
}

  @override
  void dispose() {
    _usernameController.dispose();
    super.dispose();
  }
}
