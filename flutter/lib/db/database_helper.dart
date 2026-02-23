import 'package:app/db/database_service.dart';
import 'package:sqflite/sqflite.dart';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await DatabaseService.initializeDatabase();
    return _database!;
  }

  Future<int> insertDomain(String address) async {
    final db = await database;
    return await db.insert(
      'domains',
      {'address': address},
      conflictAlgorithm: ConflictAlgorithm.ignore,
    );
  }

  Future<List<String>> getDomains() async {
    final db = await database;
    final maps = await db.query(
      'domains',
      orderBy: 'created_at DESC',
    );
    return maps.map((map) => map['address'] as String).toList();
  }

  Future<int> deleteDomain(String address) async {
    final db = await database;
    return await db.delete(
      'domains',
      where: 'address = ?',
      whereArgs: [address],
    );
  }

  Future close() async {
    final db = await database;
    db.close();
  }
}