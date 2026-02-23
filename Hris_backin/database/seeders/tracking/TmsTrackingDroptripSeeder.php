<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingDroptripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $droptrips = [
            [
                'trackingheader_id' => 1,
                'trackingclient_id' => 1,
                'trackingclient_store_id' => 1,
                'sqno' => 1,
                'drsino' => 'DRS001',
                'store_time_in' => '2024-01-15 08:00:00',
                'unloading_start' => '2024-01-15 08:15:00',
                'unloading_end' => '2024-01-15 09:00:00',
                'store_time_out' => '2024-01-15 09:15:00',
                'receiver_name' => 'John Doe',
                'delivery_status' => 'COMPLETED',
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'trackingheader_id' => 2,
                'trackingclient_id' => 2,
                'trackingclient_store_id' => 2,
                'sqno' => 2,
                'drsino' => 'DRS002',
                'store_time_in' => '2024-01-15 09:00:00',
                'unloading_start' => '2024-01-15 09:10:00',
                'unloading_end' => '2024-01-15 10:00:00',
                'store_time_out' => '2024-01-15 10:15:00',
                'receiver_name' => 'Jane Smith',
                'delivery_status' => 'COMPLETED',
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'trackingheader_id' => 3,
                'trackingclient_id' => 3,
                'trackingclient_store_id' => 3,
                'sqno' => 3,
                'drsino' => 'DRS003',
                'store_time_in' => '2024-01-15 10:00:00',
                'unloading_start' => '2024-01-15 10:05:00',
                'unloading_end' => null,
                'store_time_out' => null,
                'receiver_name' => 'Mike Johnson',
                'delivery_status' => 'IN_PROGRESS',
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_droptrip')->insert($droptrips);
    }
}
