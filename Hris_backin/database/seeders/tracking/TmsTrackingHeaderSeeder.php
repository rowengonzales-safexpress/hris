<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headers = [
            [
                'branch_id' => 1,
                'tracking_number' => 'TRK001',
                'tracking_type_id' => 1,
                'current_status_id' => 1,
                'estimated_delivery_date' => '2024-01-20 00:00:00',
                'priority_level' => 'NORMAL',
                'package_count' => 1,
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'tracking_number' => 'TRK002',
                'tracking_type_id' => 1,
                'current_status_id' => 1,
                'estimated_delivery_date' => '2024-01-21 00:00:00',
                'priority_level' => 'HIGH',
                'package_count' => 2,
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'tracking_number' => 'TRK003',
                'tracking_type_id' => 1,
                'current_status_id' => 1,
                'estimated_delivery_date' => '2024-01-22 00:00:00',
                'priority_level' => 'NORMAL',
                'package_count' => 1,
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_header')->insert($headers);
    }
}
