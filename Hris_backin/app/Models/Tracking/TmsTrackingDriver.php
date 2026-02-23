<?php

namespace App\Models\Tracking;

use App\Models\MainModel;

class TmsTrackingDriver extends MainModel
{
    protected $table = 'tms_tracking_driver';

    protected $fillable = [
        'branch_id',
        'driver_code',
        'first_name',
        'last_name',
        'full_name',
        'mobile_no',
        'email',
        'license_no',
        'license_type',
        'license_expiry',
        'current_status',
        'current_location',
        'last_location_update',
        'current_vehicle_id',
        'total_deliveries',
        'successfull_deliveries',
        'average_rating',
        'emergency_contact_no',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
