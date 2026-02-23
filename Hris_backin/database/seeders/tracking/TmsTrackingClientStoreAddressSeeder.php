<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingClientStoreAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses = [
            [
                'client_id' => 1,
                'store_name' => 'ABC Main Store',
                'store_code' => 'ABC-MAIN',
                'phone' => '09171111111',
                'address' => '123 Main Street',
                'city' => 'Makati City',
                'state_province' => 'Metro Manila',
                'zip_code' => '1200',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'client_id' => 2,
                'store_name' => 'XYZ Branch Store',
                'store_code' => 'XYZ-BRANCH',
                'phone' => '09182222222',
                'address' => '456 Branch Avenue',
                'city' => 'Quezon City',
                'state_province' => 'Metro Manila',
                'zip_code' => '1100',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'client_id' => 3,
                'store_name' => 'DEF Outlet',
                'store_code' => 'DEF-OUTLET',
                'phone' => '09193333333',
                'address' => '789 Outlet Road',
                'city' => 'Pasig City',
                'state_province' => 'Metro Manila',
                'zip_code' => '1600',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_client_store_address')->insert($addresses);
    }
}
