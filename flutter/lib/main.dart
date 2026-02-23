import 'package:app/screens/domain_screen.dart';
import 'package:app/screens/home_screen.dart';
import 'package:app/screens/password_screen.dart';
import 'package:app/screens/username_screen.dart';
import 'package:flutter/material.dart';
import 'package:app/screens/auth_screen.dart';
import 'package:app/providers/auth_provider.dart';
import 'package:provider/provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    ChangeNotifierProvider(
      create: (context) => AuthProvider(),
      child: const MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'My App',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: const AuthScreen(),
      // Define all your routes
      routes: {
        '/auth': (context) => const AuthScreen(),
        '/domain': (context) => const DomainScreen(),
        '/username': (context) => const UsernameScreen(domain: ''),
        '/password': (context) => const PasswordScreen(),
        '/home': (context) => const HomeScreen(),
      },
    );
  }
}