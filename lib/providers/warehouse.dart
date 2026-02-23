// import 'dart:convert';

// import 'package:flutter/foundation.dart';
// import 'package:app/providers/auth.dart';
// import 'package:app/utils/localdb.dart';

// import '../utils/constant.dart';

// class Warehouse with ChangeNotifier {
//   final List<Warehouses> _items = [];
//   late String _selectedWarehouse = '';

//   List<Warehouses> get items {
//     return [..._items];
//   }

//   int get itemCount {
//     return _items.length;
//   }

//   String get selectedWarehouse {
//     return _selectedWarehouse;
//   }

//   Future<bool> checkSelectedWarehouseInLocalDB() async {
//     bool isSave = false;
//     await LocalDB.get(SELECTED_WAREHOUSE).then((value) {
//       if (value != 'null') {
//         isSave = true;
//       } else {
//         isSave = false;
//       }
//     });

//     return isSave;
//   }

//   Future<void> initItem() async {
//     _items.clear();
//     String _strUserInfo = '';

//     await LocalDB.get(USER_DATA).then((value) => _strUserInfo = value);
//     if (_strUserInfo.isNotEmpty) {
//       UserInfo userInfo =
//           UserInfo.fromJson(jsonDecode(_strUserInfo)['userInfo']);
//       userInfo.warehouses?.forEach((element) => _items.add(element));
//       notifyListeners();
//     }
//   }

//   Future<List<Warehouses>?> getItems() async {
//     _items.clear();
//     String _strUserInfo = '';

//     await LocalDB.get(USER_DATA).then((value) => _strUserInfo = value);
//     if (_strUserInfo.isNotEmpty) {
//       UserInfo userInfo =
//           UserInfo.fromJson(jsonDecode(_strUserInfo)['userInfo']);
//       userInfo.warehouses?.forEach((element) => _items.add(element));

//       return userInfo.warehouses;
//     }
//     return null;
//   }

//   Future<void> addItem(Warehouses warehouse) async {
//     _items.add(warehouse);
//     notifyListeners();
//   }

//   Future<void> setWarehouse(Warehouses warehouseCode) async {
//     _selectedWarehouse = warehouseCode.description.toString();
//     await LocalDB.set(SELECTED_WAREHOUSE, jsonEncode(warehouseCode));
//   }

//   Future<void> setSelectedWarehouse() async {
//     await LocalDB.get(SELECTED_WAREHOUSE).then((value) {
//       if (value != 'null') {
//         _selectedWarehouse = value;
//       }
//     });
//   }

//   Future<void> getWarehouse() async {
//     String strResult = "";
//     await LocalDB.get(SELECTED_WAREHOUSE).then((wrcde) async {
//       if (wrcde != 'null') {
//         _selectedWarehouse =
//             Warehouses.fromJson(jsonDecode(wrcde)).description.toString();
//         strResult =
//             Warehouses.fromJson(jsonDecode(wrcde)).description.toString();
//       }
//     });

//     notifyListeners();
//   }

//   static Future<String>  getWarehouseid() async {
//     String strResult = "";
//     await LocalDB.get(SELECTED_WAREHOUSE).then((wrdata) async {
//       if (wrdata != 'null') {
//         strResult = Warehouses.fromJson(jsonDecode(wrdata)).id.toString();
//       }
//     });

//     return strResult;
//   }
// }
