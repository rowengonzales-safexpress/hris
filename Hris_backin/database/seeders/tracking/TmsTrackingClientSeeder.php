<?php

namespace Database\Seeders\tracking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TmsTrackingClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'branch_id' => 1,
                'client_code' => 'CLI001',
                'client_name' => 'ABC Corporation',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'client_code' => 'CLI002',
                'client_name' => 'XYZ Trading',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
            [
                'branch_id' => 1,
                'client_code' => 'CLI003',
                'client_name' => 'DEF Enterprises',
                'is_active' => 1,
                'created_by' => 0,
                'created_at' => Carbon::now(),
            ],
        ];

        DB::table('tms_tracking_client')->insert($clients);
    }
}
