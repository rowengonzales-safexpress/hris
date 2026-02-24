import 'package:flutter/material.dart';
import 'login_page.dart';
import 'loading_page.dart';
import 'home_page.dart';
import 'profile_page.dart';
import 'my_requests_page.dart';
import 'notifications_page.dart';
import 'notification_detail_page.dart';
import 'welcome_page.dart';
import 'leave_loading_page.dart';
import 'leave_page.dart';

void main() {
  runApp(const HrisApp());
}

class HrisApp extends StatelessWidget {
  const HrisApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'HRIS',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.green,
        scaffoldBackgroundColor: Colors.white,
        fontFamily: 'Roboto',
      ),
      initialRoute: '/login',
      routes: {
        '/login': (context) => const LoginPage(),
        '/loading': (context) => const LoadingPage(),
        '/home': (context) => const HomePage(),
        '/profile': (context) => const ProfilePage(),
        '/requests': (context) => const MyRequestsPage(),
        '/notifications': (context) => const NotificationsPage(),
        '/notificationDetail': (context) => const NotificationDetailPage(),
        '/welcome': (context) => const WelcomePage(),
        '/leaveLoading': (context) => const LeaveLoadingPage(),
        '/leave': (context) => const LeavePage(),
      },
    );
  }
}
