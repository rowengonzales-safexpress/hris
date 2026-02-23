import 'dart:io';
import 'package:app/providers/app_config.dart';
import 'package:app/utils/constant.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:http/io_client.dart';
import 'package:path/path.dart';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class Access with ChangeNotifier {
  String? uri;

  Access({this.uri});

  Access.fromJson(Map<String, dynamic> json) {
    uri = json['uri'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['uri'] = uri;
    return data;
  }
}

class AuthProvider with ChangeNotifier {
  static final IOClient _ioClient = _createHttpClient();

  static IOClient _createHttpClient() {
    final ioc = HttpClient();
    if (AppConfig.allowBadCertificates) {
      ioc.badCertificateCallback =
          (X509Certificate cert, String host, int port) => true;
    }
    return IOClient(ioc);
  }

  // This now shows localhost for Android/iOS and actual address for Windows
  Future<String> get serverAddress async {
    if (Platform.isWindows) {
      return '${AppConfig.defaultWindowsAddress}:${await AppConfig.getServerPort()}';
    }
    return '${AppConfig.defaultWindowsAddress}:${await AppConfig.getServerPort()}';
  }

  // This always uses the real address for connections
  Future<String> get _connectionUrl async {
    final address = await AppConfig.getServerAddress();
    return "$address/api";
  }

  int? _userId;
  String? _username;
  String? _email;
  String? _token;
  int? _warehouseId;
  String? _warehouseName;

  int? get userId => _userId;
  String? get username => _username;
  String? get email => _email;
  String? get token => _token;
  int? get warehouseId => _warehouseId;
  String? get warehouseName => _warehouseName;

  void resetUserId() {
    _userId = null;
    notifyListeners();
  }

  void setUserData(int userId, String username, String email) {
    _userId = userId;
    _username = username;
    _email = email;
    notifyListeners();
  }

  Future<void> autoLogin() async {
    final prefs = await SharedPreferences.getInstance();
    var getUserData = prefs.getString(USER_DATA);
    if (getUserData != null) {
      final userData = json.decode(getUserData);
      _userId = userData['userId'];
      _username = userData['username'];
      _email = userData['email'];
      _token = userData['token'];
      _warehouseId = userData['warehouseId'];
      _warehouseName = userData['warehouseName'];
      notifyListeners();
    }
  }

  Future<bool> verifyUser(String usernameOrEmail) async {
    try {
      final uri = Uri.parse(await _connectionUrl);
      final response = await _ioClient.post(
        Uri.parse('$uri/mAuth/verify-user'),
        headers: {'Content-Type': 'application/json; charset=UTF-8'},
        body: json.encode({'usernameOrEmail': usernameOrEmail}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        _userId = data['userId'];
        _username = data['username']; // Make sure to set username if available
        _email = data['email']; // Make sure to set email if available
        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  Future<bool> login(String password) async {
    if (_userId == null) return false;
    final prefs = await SharedPreferences.getInstance();
    try {
      final uri = Uri.parse(await _connectionUrl); // Use connection URL
      final response = await _ioClient.post(
        Uri.parse('$uri/mAuth/login'),
        headers: {'Content-Type': 'application/json; charset=UTF-8'},
        body: json.encode({'userId': _userId, 'password': password}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        _userId = data['uuid'];
        _token = data['token'];
        _username = data['userName'];
        _email = data['email'];
        _warehouseId = data['warehouseId'];
        _warehouseName = data['warehouseName'];

        final userDetail = json.encode({
          'userId': _userId,
          'username': _username,
          'email': _email,
          'warehouseId': _warehouseId,
          'warehouseName': _warehouseName,
          'token': _token,
        });

        await prefs.setString(USER_DATA, userDetail);
        await prefs.setString(ACCESS_DB, uri.toString());

        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  void logout() async {
    _userId = null;
    _username = null;
    _email = null;
    _token = null;

    final prefs = await SharedPreferences.getInstance();

    // Remove the entire USER_DATA entry (more thorough)
    await prefs.remove(USER_DATA);
    await prefs.remove(ACCESS_DB);
    notifyListeners();
  }

  Future<String?> getWarehouseName() async {
    final prefs = await SharedPreferences.getInstance();
    final userDataStr = prefs.getString(USER_DATA);
    if (userDataStr != null) {
      final userData = json.decode(userDataStr);
      return userData['warehouseName'] as String?;
    }
    return null;
  }
}
