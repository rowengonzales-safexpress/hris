<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'type_code' => 'SHIPMENT',
                'type_name' => 'Shipment Tracking',
                'description' => 'Standard shipment tracking',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'type_code' => 'DELIVERY',
                'type_name' => 'Delivery Tracking',
                'description' => 'Delivery tracking',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'type_code' => 'PICKUP',
                'type_name' => 'Pickup Tracking',
                'description' => 'Pickup request tracking',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'type_code' => 'TRANSFER',
                'type_name' => 'Transfer Tracking',
                'description' => 'Internal transfer tracking',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'type_code' => 'RETURN',
                'type_name' => 'Return Tracking',
                'description' => 'Return shipment tracking',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_type')->insert($types);
    }
}
