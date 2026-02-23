import 'package:shared_preferences/shared_preferences.dart';


class LocalDB {
 static Future<void> set(String key, String value) async {
  final prefs = await SharedPreferences.getInstance();
  prefs.setString(key, value);
}

static Future<String> get(String key) async {
  final prefs = await SharedPreferences.getInstance();
  var result = prefs.getString(key);
  return result.toString();
}
}

