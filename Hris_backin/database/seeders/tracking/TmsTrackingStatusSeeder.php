<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'status_code' => 'CREATED',
                'status_name' => 'Created',
                'description' => 'Tracking record created',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'PICKED_UP',
                'status_name' => 'Picked Up',
                'description' => 'Package picked up from origin',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'IN_TRANSIT',
                'status_name' => 'In Transit',
                'description' => 'Package is in transit',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'OUT_FOR_DELIVERY',
                'status_name' => 'Out for Delivery',
                'description' => 'Package is out for delivery',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'DELIVERED',
                'status_name' => 'Delivered',
                'description' => 'Package delivered successfully',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'DELAYED',
                'status_name' => 'Delayed',
                'description' => 'Package delivery delayed',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'RETURNED',
                'status_name' => 'Returned',
                'description' => 'Package returned to sender',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'CANCELLED',
                'status_name' => 'Cancelled',
                'description' => 'Tracking cancelled',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'status_code' => 'EXCEPTION',
                'status_name' => 'Exception',
                'description' => 'Exception occurred during transit',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_status')->insert($statuses);
    }
}
