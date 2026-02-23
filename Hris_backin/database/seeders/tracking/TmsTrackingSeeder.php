<?php

namespace Database\Seeders\tracking;

use Database\Seeders\tracking\TmsTrackingStatusSeeder;
use Database\Seeders\tracking\TmsTrackingTypeSeeder;
use Database\Seeders\tracking\TmsTrackingVehicleSeeder;
use Database\Seeders\tracking\TmsTrackingAssignmentSeeder;
use Database\Seeders\tracking\TmsTrackingClientSeeder;
use Database\Seeders\tracking\TmsTrackingClientStoreAddressSeeder;
use Database\Seeders\tracking\TmsTrackingDriverSeeder;
use Database\Seeders\tracking\TmsTrackingDroptripSeeder;
use Database\Seeders\tracking\TmsTrackingHeaderSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TmsTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Run seeders in order to handle dependencies
        $this->call([
            TmsTrackingStatusSeeder::class,
            TmsTrackingTypeSeeder::class,
            TmsTrackingDriverSeeder::class,
            TmsTrackingVehicleSeeder::class,
            TmsTrackingAssignmentSeeder::class,
            TmsTrackingHeaderSeeder::class,
            TmsTrackingClientSeeder::class,
            TmsTrackingClientStoreAddressSeeder::class,
            TmsTrackingDroptripSeeder::class,

        ]);
    }
}
