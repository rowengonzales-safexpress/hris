<?php

namespace Database\Seeders\Sqdcm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sqdcm\SqdcmAttendPerfDeptDaily;
use Carbon\Carbon;

class SqdcmAttendPerfDeptDailySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['HR', 'IT', 'FINANCE', 'OPERATIONS', 'LOGISTICS', 'SALES', 'MARKETING'];
        $sites = ['SITE001', 'SITE002', 'SITE003', 'SITE004', 'SITE005'];
        $grades = ['A', 'B', 'C', 'D', 'F'];

        // Generate data for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);

            foreach ($sites as $siteCode) {
                foreach ($departments as $deptCode) {
                    $totalEmp = rand(20, 100);
                    $presentEmp = rand(15, $totalEmp);
                    $attendancePerc = ($presentEmp / $totalEmp) * 100;

                    $totalTarget = rand(1000, 5000);
                    $totalAchieved = rand(800, $totalTarget + 500);
                    $achievementPerc = ($totalAchieved / $totalTarget) * 100;

                    $overallScore = ($attendancePerc + $achievementPerc) / 2;

                    // Determine grade based on overall score
                    if ($overallScore >= 90) $grade = 'A';
                    elseif ($overallScore >= 80) $grade = 'B';
                    elseif ($overallScore >= 70) $grade = 'C';
                    elseif ($overallScore >= 60) $grade = 'D';
                    else $grade = 'F';

                    SqdcmAttendPerfDeptDaily::create([
                        'date' => $date,
                        'dept_code' => $deptCode,
                        'site_code' => $siteCode,
                        'total_emp' => $totalEmp,
                        'present_emp' => $presentEmp,
                        'attendance_perc' => $attendancePerc,
                        'total_target' => $totalTarget,
                        'total_achieved' => $totalAchieved,
                        'achievement_perc' => $achievementPerc,
                        'overall_score' => $overallScore,
                        'grade' => $overallScore >= 90 ? 'A' : ($overallScore >= 80 ? 'B' : ($overallScore >= 70 ? 'C' : 'D')),
                    ]);
                }
            }
        }
    }
}
