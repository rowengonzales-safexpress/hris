<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = [
            [
                'driver_id' => 1,
                'vehicle_id' => 1,
                'assignment_date' => Carbon::now(),
                'start_date' => Carbon::now(),
                'assignment_type' => 'REGULAR',
                'assignment_status' => 'ACTIVE',
                'assigned_by' => 'SYSTEM',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'driver_id' => 2,
                'vehicle_id' => 2,
                'assignment_date' => Carbon::now(),
                'start_date' => Carbon::now(),
                'assignment_type' => 'REGULAR',
                'assignment_status' => 'ACTIVE',
                'assigned_by' => 'SYSTEM',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'driver_id' => 3,
                'vehicle_id' => 3,
                'assignment_date' => Carbon::now(),
                'start_date' => Carbon::now(),
                'assignment_type' => 'REGULAR',
                'assignment_status' => 'ACTIVE',
                'assigned_by' => 'SYSTEM',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_assignment')->insert($assignments);

        
    }
}
