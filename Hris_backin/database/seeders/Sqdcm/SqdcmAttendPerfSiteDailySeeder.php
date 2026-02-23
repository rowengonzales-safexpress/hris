<?php

namespace Database\Seeders\Sqdcm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sqdcm\SqdcmAttendPerfSiteDaily;
use Carbon\Carbon;

class SqdcmAttendPerfSiteDailySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = [
            'SITE001' => ['name' => 'SYSU', 'district' => 'CBU'],
            'SITE002' => ['name' => 'JSU', 'district' => 'CBU'],
            'SITE003' => ['name' => 'MOBILE', 'district' => 'CBU'],
            'SITE004' => ['name' => 'KUYA J', 'district' => 'CBU'],
            'SITE005' => ['name' => 'SPORTFIT', 'district' => 'CBU']
        ];

        // Generate data for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);

            foreach ($sites as $siteCode => $siteInfo) {
                $totalEmp = rand(100, 500);
                $presentEmp = rand(80, $totalEmp);
                $attendancePerc = ($presentEmp / $totalEmp) * 100;

                $totalTarget = rand(10000, 50000);
                $totalAchieved = rand(8000, $totalTarget + 5000);
                $performancePerc = ($totalAchieved / $totalTarget) * 100;

                $overallRating = ($attendancePerc + $performancePerc) / 2;

                SqdcmAttendPerfSiteDaily::create([
                    'date' => $date,
                    'site_code' => $siteCode,
                    'site_name' => $siteInfo['name'],
                    'district_code' => $siteInfo['district'],
                    'total_emp' => $totalEmp,
                    'present_emp' => $presentEmp,
                    'attendance_perc' => $attendancePerc,
                    'total_target' => $totalTarget,
                    'total_achieved' => $totalAchieved,
                    'performance_perc' => $performancePerc,
                    'overall_score' => $overallRating,
                    'grade' => $overallRating >= 90 ? 'A' : ($overallRating >= 80 ? 'B' : ($overallRating >= 70 ? 'C' : 'D')),
                ]);
            }
        }
    }
}
