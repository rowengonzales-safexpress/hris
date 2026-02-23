<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'branch_id' => 1,
                'driver_code' => 'TRK001',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'mobile_no' => '09171234567',
                'email' => 'juan.delacruz@company.com',
                'license_no' => 'N01-12-123456',
                'license_type' => 'Professional',
                'license_expiry' => '2025-12-31 00:00:00',
                'current_status' => 'AVAILABLE',
                'current_vehicle_id'=> 1,
                'emergency_contact_name' => 'Maria Dela Cruz',
                'emergency_contact_no' => '09177654321',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'driver_code' => 'TRK002',
                'first_name' => 'Pedro',
                'last_name' => 'Santos',
                'mobile_no' => '09181234567',
                'email' => 'pedro.santos@company.com',
                'license_no' => 'N01-12-789012',
                'license_type' => 'Professional',
                'license_expiry' => '2024-06-30 00:00:00',
                'current_status' => 'AVAILABLE',
                'current_vehicle_id'=> 2,
                'emergency_contact_name' => 'Ana Santos',
                'emergency_contact_no' => '09187654321',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'driver_code' => 'TRK003',
                'first_name' => 'Jose',
                'last_name' => 'Reyes',
                'mobile_no' => '09191234567',
                'email' => 'jose.reyes@company.com',
                'license_no' => 'N01-12-345678',
                'license_type' => 'Professional',
                'license_expiry' => '2025-03-15 00:00:00',
                'current_status' => 'AVAILABLE',
                'current_vehicle_id'=> 3,
                'emergency_contact_name' => 'Carmen Reyes',
                'emergency_contact_no' => '09197654321',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_driver')->insert($drivers);
    }
}
