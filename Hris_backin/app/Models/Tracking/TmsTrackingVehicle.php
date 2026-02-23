<?php

namespace App\Models\Tracking;

use App\Models\MainModel;

class TmsTrackingVehicle extends MainModel
{
    protected $table = 'tms_tracking_vehicle';

    protected $fillable = [
        'branch_id',
        'vehicle_code',
        'plate_no',
        'vehicle_size',
        'vehicle_type',
        'current_status',
        'is_active'
    ];
}
