import 'dart:convert';
import 'dart:io';
import 'package:app/providers/app_config.dart';
import 'package:app/providers/auth_provider.dart';
import 'package:app/utils/constant.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'package:http/io_client.dart';

class FetchApi {
  static final IOClient _ioClient = _createHttpClient();

  static IOClient _createHttpClient() {
    final ioc = HttpClient();
    if (AppConfig.allowBadCertificates) {
      ioc.badCertificateCallback =
          (X509Certificate cert, String host, int port) => true;
    }
    return IOClient(ioc);
  }

  Future<String> getUrl() async {
    final prefs = await SharedPreferences.getInstance();
    var getAccessData = prefs.getString(ACCESS_DB);
    Access access = Access.fromJson(jsonDecode(getAccessData!));
    String strUrl = access.uri.toString();

    return strUrl;
  }

  Future<dynamic> post(String? queryString, var data) async {
    final prefs = await SharedPreferences.getInstance();

    var getAccessData = prefs.getString(ACCESS_DB);
    var getUserData = prefs.getString(USER_DATA);

    final userData = json.decode(getUserData!);

    Access access = Access.fromJson(jsonDecode(getAccessData!));
    String strUrl = access.uri.toString();
    if (queryString.toString().isNotEmpty) {
      strUrl = strUrl + queryString.toString();
    }

    final url = Uri.parse(strUrl);
    final response = await _ioClient.post(url,
        headers: {
          "Content-Type": "application/json",
          'Authorization': "Bearer " + userData['token'],
        },
        body: data);

    if (response.statusCode == 403 && response.body.toString() == 'Forbidden') {
      //renewToken().then((_) => post(queryString, data));
    }

    return response;
  }

  Future<dynamic> put(String? queryString, var data) async {
    final prefs = await SharedPreferences.getInstance();

    var getAccessData = prefs.getString(ACCESS_DB);
    var getUserData = prefs.getString(USER_DATA);

    final userData = json.decode(getUserData!);

    Access access = Access.fromJson(jsonDecode(getAccessData!));
    String strUrl = access.uri.toString();
    if (queryString.toString().isNotEmpty) {
      strUrl = strUrl + queryString.toString();
    }

    final url = Uri.parse(strUrl);
    final response = await _ioClient.put(url,
        headers: {
          "Content-Type": "application/json",
          'Authorization': "Bearer " + userData['token'],
        },
        body: data);

    if (response.statusCode == 403 && response.body.toString() == 'Forbidden') {
      //renewToken().then((_) => put(queryString, data));
    }

    return response;
  }

  Future<dynamic> customPost(String? strUrl, var data) async {
    final url = Uri.parse(strUrl.toString());
    final response = await _ioClient.post(url,
        headers: {
          "Content-Type": "application/json",
          'Authorization': "Bearer ",
        },
        body: data);

    if (response.statusCode == 403 && response.body.toString() == 'Forbidden') {
      //renewToken().then((_) => customPost(strUrl, data));
    }

    return response;
  }

  Future<dynamic> get(String? queryString) async {
    try {
      final prefs = await SharedPreferences.getInstance();

      var getAccessData = prefs.getString(ACCESS_DB);
      var getUserData = prefs.getString(USER_DATA);

      if (getAccessData == null || getUserData == null) {
        throw Exception('Access or user data not found in SharedPreferences');
      }

      final userData = json.decode(getUserData);

      //Access access = Access.fromJson(jsonDecode(getAccessData));

      String strUrl = getAccessData.toString();
      if (queryString != null && queryString.isNotEmpty) {
        strUrl = strUrl + queryString;
      }

      final url = Uri.parse(strUrl);

      final response = await _ioClient.get(url, headers: {
        "Content-Type": "application/json; charset=UTF-8",
        'Authorization': "Bearer " + userData['token'],
      });

      if (response.statusCode == 403 &&
          response.body.toString() == 'Forbidden') {
        await renewToken().then((_) => get(queryString));
      }

      return response;
    } catch (error) {
      rethrow;
    }
  }

  Future<void> renewToken() async {
    final prefs = await SharedPreferences.getInstance();

    var getAccessData = prefs.getString(ACCESS_DB);
    var getUserData = prefs.getString(USER_DATA);

    final userData = json.decode(getUserData!);

    Access access = Access.fromJson(jsonDecode(getAccessData!));

    String strUrl = access.uri.toString() + '/api/renew-token';

    final url = Uri.parse(strUrl);
    Map data = {"token": userData['refreshToken']};
    final response = await _ioClient.post(url, body: data);

    final responseData = json.decode(response.body);

    final userAccess = json.encode(
      {
        'accessToken': responseData['accessToken'],
        'refreshToken': responseData['refreshToken'],
        'userInfo': userData['userInfo'],
      },
    );
    prefs.setString(USER_DATA, userAccess);
  }
}
