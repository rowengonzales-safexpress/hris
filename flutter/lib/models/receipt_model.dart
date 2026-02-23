import 'package:flutter/foundation.dart';

class ReceivedShipment {
  final int id;
  final String receiptno;
  final int warehouse_id;
  final int poheader_id;
  final DateTime receiptdate;
  final String receipttype;
  final String? referenceno;
  final String? documentno;
  final DateTime? documentdate;
  final int supplier_id;
  final String? supname;
  final String? deliveryno;
  final String? trucker;
  final String? plateno;
  final String? cvno;
  final String? driver;
  final DateTime? arrivaldate;
  final DateTime? unloadingstart;
  final DateTime? unloadingfinish;
  final String? remarks;
  final String recordsource;
  final String status;
  final String createdby;
  final String? updatedby;
  final DateTime createdate;
  final DateTime? updatedate;

  const ReceivedShipment(
      {required this.id,
      required this.receiptno,
      required this.warehouse_id,
      required this.poheader_id,
      required this.receiptdate,
      required this.receipttype,
      this.referenceno,
      this.documentno,
      this.documentdate,
      required this.supplier_id,
      this.supname,
      this.deliveryno,
      this.trucker,
      this.plateno,
      this.cvno,
      this.driver,
      this.arrivaldate,
      this.unloadingstart,
      this.unloadingfinish,
      this.remarks,
      required this.recordsource,
      required this.status,
      required this.createdby,
      this.updatedby,
      required this.createdate,
      this.updatedate});

  const ReceivedShipment.empty(
      {this.id = 0,
      this.receiptno = '',
      this.warehouse_id = 0,
      this.poheader_id = 0,
      required this.receiptdate,
      this.receipttype = 'R01',
      this.referenceno,
      this.documentno,
      this.documentdate,
      required this.supplier_id,
      this.supname,
      this.deliveryno,
      this.trucker,
      this.plateno,
      this.cvno,
      this.driver,
      this.arrivaldate,
      this.unloadingstart,
      this.unloadingfinish,
      this.remarks,
      required this.recordsource,
      this.status = 'G',
      this.createdby = '',
      this.updatedby = '',
      required this.createdate,
      this.updatedate});

  factory ReceivedShipment.fromJson(Map<String, dynamic> json) {
    return ReceivedShipment(
      id: json['id'],
      receiptno: json['receiptno'],
      warehouse_id: json['warehouse_id'],
      poheader_id: json['poheader_id'],
      receiptdate: DateTime.parse(json['receiptdate']),
      receipttype: json['receipttype'],
      referenceno: json['referenceno'],
      documentno: json['documentno'],
      documentdate: json['documentdate'] != null
          ? DateTime.parse(json['documentdate'])
          : null,
      supplier_id: json['supplier_id'],
      supname: json['supname'],
      deliveryno: json['deliveryno'],
      trucker: json['trucker'],
      plateno: json['plateno'],
      cvno: json['cvno'],
      driver: json['driver'],
      arrivaldate: json['arrivaldate'] != null
          ? DateTime.parse(json['arrivaldate'])
          : null,
      unloadingstart: json['unloadingstart'] != null
          ? DateTime.parse(json['unloadingstart'])
          : null,
      unloadingfinish: json['unloadingfinish'] != null
          ? DateTime.parse(json['unloadingfinish'])
          : null,
      remarks: json['remarks'],
      recordsource: json['recordsource'],
      status: json['status'],
      createdby: json['createdby'],
      updatedby: json['updatedby'],
      createdate: DateTime.parse(json['createdate']),
      updatedate: json['updatedate'] != null
          ? DateTime.parse(json['updatedate'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'receiptno': receiptno,
      'warehouse_id': warehouse_id,
      'poheader_id': poheader_id,
      'receiptdate': receiptdate.toIso8601String(),
      'receipttype': receipttype,
      'referenceno': referenceno,
      'documentno': documentno,
      'documentdate': documentdate?.toIso8601String(),
      'supplier_id': supplier_id,
      'supname': supname,
      'deliveryno': deliveryno,
      'trucker': trucker,
      'plateno': plateno,
      'cvno': cvno,
      'driver': driver,
      'arrivaldate': arrivaldate?.toIso8601String(),
      'unloadingstart': unloadingstart?.toIso8601String(),
      'unloadingfinish': unloadingfinish?.toIso8601String(),
      'remarks': remarks,
      'recordsource': recordsource,
      'status': status,
      'createdby': createdby,
      'updatedby': updatedby,
      'createdate': createdate.toIso8601String(),
      'updatedate': updatedate?.toIso8601String(),
    };
  }
}
