<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingVehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'branch_id' => 1,
                'vehicle_code' => 'VEH001',
                'plate_no' => 'ABC-1234',
                'vehicle_name' => 'Delivery Truck 1',
                'vehicle_size' => '4W',
                'vehicle_type' => 'TRUCK_MEDIUM',
                'vehicle_category' => 'DELIVERY',
                'brand' => 'Isuzu',
                'model' => 'ELF',
                'year' => 2020,
                'color' => 'White',
                'max_weight_kg' => 5000.00,
                'max_volume_cbm' => 25.00,
                'fuel_type' => 'DIESEL',
                'current_status' => 'AVAILABLE',
                'registration_expiry' => '2025-01-01 00:00:00',
                'insurance_expiry' => '2024-12-31 00:00:00',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'vehicle_code' => 'VEH002',
                'plate_no' => 'DEF-5678',
                'vehicle_name' => 'Delivery Van 1',
                'vehicle_size' => '8W',
                'vehicle_type' => 'TRUCK_LARGE',
                'vehicle_category' => 'DELIVERY',
                'brand' => 'Mitsubishi',
                'model' => 'L300',
                'year' => 2019,
                'color' => 'Blue',
                'max_weight_kg' => 1500.00,
                'max_volume_cbm' => 8.00,
                'fuel_type' => 'GASOLINE',
                'current_status' => 'AVAILABLE',
                'registration_expiry' => '2024-06-01 00:00:00',
                'insurance_expiry' => '2024-11-30 00:00:00',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'vehicle_code' => 'VEH003',
                'plate_no' => 'GHI-9012',
                'vehicle_name' => 'Pickup Truck 1',
                'vehicle_size' => '4W',
                'vehicle_type' => 'TRUCK_SMALL',
                'vehicle_category' => 'PICKUP',
                'brand' => 'Toyota',
                'model' => 'Hilux',
                'year' => 2021,
                'color' => 'Silver',
                'max_weight_kg' => 1000.00,
                'max_volume_cbm' => 5.00,
                'fuel_type' => 'DIESEL',
                'current_status' => 'AVAILABLE',
                'registration_expiry' => '2026-03-01 00:00:00',
                'insurance_expiry' => '2025-02-28 00:00:00',
                'is_active' => 1,
               'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_vehicle')->insert($vehicles);
    }
}
