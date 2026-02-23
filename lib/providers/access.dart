import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:app/utils/localdb.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../utils/constant.dart';

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

class AccessItems with ChangeNotifier {
  final List<Access> _items = [];
  late Access _defaultAccess = Access(uri: '');

  List<Access> get items {
    return [..._items];
  }

  int get itemCount {
    return _items.length;
  }

  Access get defaultAccess {
    return _defaultAccess;
  }

  Future<void> removeItem(Access access) async {
    final prefs = await SharedPreferences.getInstance();
    _items.removeWhere((item) => item.uri == access.uri);
    prefs.setString(ACCESS_LIST, jsonEncode(_items));
    notifyListeners();
  }

  Future<void> initItem() async {
    final prefs = await SharedPreferences.getInstance();
    _items.clear();
    var getSharedAccessList = prefs.getString(ACCESS_LIST);
    if (getSharedAccessList != null) {
      Iterable l = jsonDecode(getSharedAccessList);
      List<Access> access =
          List<Access>.from(l.map((model) => Access.fromJson(model)));
      _items.addAll(access);
      notifyListeners();
    }
  }

  Future<void> addItem(Access access) async {
    final prefs = await SharedPreferences.getInstance();

    var getSharedAccessList = prefs.getString(ACCESS_LIST);
    if (getSharedAccessList != null) {
      var getJsonAccessList = jsonDecode(getSharedAccessList);
      final length = getJsonAccessList.length;
        if (_items.where((ii) {return ii.uri.toString() == access.uri.toString();}).isEmpty) {
          _items.add(access);
        }

    } else {
      _items.add(access);
    }
    prefs.setString(ACCESS_LIST, jsonEncode(_items));
    notifyListeners();
  }

  void setAccessDta(String accessData)  async {
    _defaultAccess = Access.fromJson(jsonDecode(accessData));
    await LocalDB.set(ACCESS_DB, accessData);
    notifyListeners();
  }

  Future<void> getDefaultAccess() async {
    String? accessData;
    await LocalDB.get(ACCESS_DB).then((value) => accessData = value);
    if(accessData.toString() !="null") {
      _defaultAccess = Access.fromJson(jsonDecode(accessData!));
      notifyListeners();
    }else{
      accessData = null;
      notifyListeners();
    }
  }
}
