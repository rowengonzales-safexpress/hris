import 'dart:convert';
import 'dart:io';

import 'package:app/models/receipt_model.dart';
import 'package:app/utils/constant.dart';
import 'package:app/utils/fetch_api.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class Received with ChangeNotifier {
  final List<ReceivedShipment> _items = [];

  List<ReceivedShipment> get receivedShipment {
    return [..._items];
  }

  int get itemCount {
    return _items.length;
  }

  Future<void> itemClear() async {
    _items.clear();
    notifyListeners();
  }

  Future<List<ReceivedShipment>> fetchReceivedShipment() async {
    final fetchApi = FetchApi();
    final prefs = await SharedPreferences.getInstance();
    var getUserData = prefs.getString(USER_DATA);
    if (getUserData != null) {
      final userData = json.decode(getUserData);
      int warehouseId = userData['warehouseId'];
      final response =
          await fetchApi.get('/mReceived?warehouseId=$warehouseId');

      final List<dynamic> data = json.decode(response.body);
      _items.clear();
      _items
          .addAll(data.map((item) => ReceivedShipment.fromJson(item)).toList());
      notifyListeners();
      return _items;
    } else {
      throw Exception('Failed to load received shipments');
    }
  }

  Future<ReceivedShipment?> postReceivedShipment(
      Map<String, dynamic> payload) async {
    final fetchApi = FetchApi();
    final prefs = await SharedPreferences.getInstance();
    var getUserData = prefs.getString(USER_DATA);
    if (getUserData != null) {
      final userData = json.decode(getUserData);
      int warehouseId = userData['warehouseId'];
      final response = await fetchApi.post(
        '/mReceived?warehouseId=$warehouseId',
        json.encode(payload),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        final receivedShipment = ReceivedShipment.fromJson(data);
        _items.add(receivedShipment);
        notifyListeners();
        return receivedShipment;
      } else {
        throw Exception('Failed to post received shipment');
      }
    } else {
      throw Exception('User data not found');
    }
  }

  Future<ReceivedShipment?> putReceivedShipment(
      int id, Map<String, dynamic> payload) async {
    final fetchApi = FetchApi();
    final prefs = await SharedPreferences.getInstance();
    var getUserData = prefs.getString(USER_DATA);
    if (getUserData != null) {
      final userData = json.decode(getUserData);
      int warehouseId = userData['warehouseId'];
      final response = await fetchApi.put(
        '/mReceived/$id?warehouseId=$warehouseId',
        json.encode(payload),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        final updatedShipment = ReceivedShipment.fromJson(data);

        // Update the item in the list
        final index = _items.indexWhere((item) => item.id == id);
        if (index != -1) {
          _items[index] = updatedShipment;
          notifyListeners();
        }
        return updatedShipment;
      } else {
        throw Exception('Failed to update received shipment');
      }
    } else {
      throw Exception('User data not found');
    }
  }
}
