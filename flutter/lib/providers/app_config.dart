import 'dart:io';
import 'package:shared_preferences/shared_preferences.dart';

class AppConfig {
  static const String defaultWindowsAddress = "localhost"; 
  static const int _defaultPort = 5001;
  static const bool allowBadCertificates = true;

  static Future<String> get baseUrl async {
    final address = await getServerAddress();
    return "https://$address/api";
  }

  static Future<String> getServerAddress() async {
    final prefs = await SharedPreferences.getInstance();

    if (Platform.isAndroid || Platform.isIOS) {
      return prefs.getString('serverAddress') ?? prefs.getString('address') ?? defaultWindowsAddress;
    } else {
      return defaultWindowsAddress; // Always use localhost for Windows
    }
  }

  static Future<int> getServerPort() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt('serverPort') ?? _defaultPort;
  }

  static Future<void> setServerAddress(String address) async {
    if (Platform.isWindows) return; // Don't save address for Windows
    
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('serverAddress', address);
  }

  static Future<void> setServerPort(int port) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('serverPort', port);
  }
}