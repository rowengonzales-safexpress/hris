<?php

namespace Database\Seeders\Sqdcm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SqdcmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SqdcmAttendPerfSiteDailySeeder::class,
            SqdcmAttendPerfDeptDailySeeder::class,
            SqdcmAttendPerfEmpDailySeeder::class,
            SqdcmKpiValuesDailySeeder::class,
        ]);
    }
}
