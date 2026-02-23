<?php

namespace Database\Seeders\Sqdcm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sqdcm\SqdcmAttendPerfEmpDaily;
use Carbon\Carbon;

class SqdcmAttendPerfEmpDailySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['HR', 'IT', 'FINANCE', 'OPERATIONS', 'LOGISTICS', 'SALES', 'MARKETING'];
        $sites = ['SITE001', 'SITE002', 'SITE003', 'SITE004', 'SITE005'];
        $employees = [];

        // Generate employee codes
        for ($i = 1; $i <= 500; $i++) {
            $employees[] = 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT);
        }

        // Generate data for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);

            // Generate data for random employees each day
            $dailyEmployees = array_slice($employees, 0, rand(200, 400));

            foreach ($dailyEmployees as $empCode) {
                $isPresent = rand(0, 100) > 15; // 85% attendance rate
                $deptCode = $departments[array_rand($departments)];
                $siteCode = $sites[array_rand($sites)];

                // Generate employee name based on code
                $empName = 'Employee ' . str_replace('EMP', '', $empCode);

                $checkInTime = null;
                $checkOutTime = null;
                $totalHours = 0;
                $overtimeHours = 0;

                if ($isPresent) {
                    $checkInHour = rand(8, 10);
                    $checkInMinute = rand(0, 59);
                    $checkInTime = $date->copy()->setTime($checkInHour, $checkInMinute);

                    $workHours = rand(7, 12);
                    $checkOutTime = $checkInTime->copy()->addHours($workHours);
                    $totalHours = $workHours + (rand(0, 59) / 60);
                    $overtimeHours = max(0, $totalHours - 8);
                }

                $targetAssigned = $isPresent ? rand(50, 200) : 0;
                $targetAchieved = $isPresent ? rand(30, $targetAssigned + 20) : 0;
                $performancePerc = $targetAssigned > 0 ? ($targetAchieved / $targetAssigned) * 100 : 0;
                $productivityScore = $isPresent ? rand(60, 100) : 0;
                $qualityRating = $isPresent ? rand(3, 5) + (rand(0, 99) / 100) : 0;

                $remarks = '';
                if (!$isPresent) {
                    $reasons = ['Sick Leave', 'Personal Leave', 'Emergency', 'Training', 'Meeting'];
                    $remarks = $reasons[array_rand($reasons)];
                } elseif ($performancePerc < 70) {
                    $remarks = 'Below target performance';
                } elseif ($performancePerc > 110) {
                    $remarks = 'Exceeded expectations';
                }

                SqdcmAttendPerfEmpDaily::create([
                    'date' => $date,
                    'emp_code' => $empCode,
                    'emp_name' => $empName,
                    'dept_code' => $deptCode,
                    'site_code' => $siteCode,
                    'is_present' => $isPresent,
                    'in_time' => $isPresent ? $checkInTime : null,
                    'out_time' => $isPresent ? $checkOutTime : null,
                    'working_hours' => $isPresent ? round($totalHours, 2) : 0,
                    'target_value' => $targetAssigned,
                    'achieved_value' => $targetAchieved,
                    'performance_perc' => round($performancePerc, 2),
                    'attendance_score' => $isPresent ? 100 : 0,
                    'performance_score' => round($performancePerc, 2),
                    'overall_score' => $isPresent ? round(($performancePerc + round($productivityScore, 2)) / 2, 2) : 0,
                    'grade' => $isPresent ? (round(($performancePerc + round($productivityScore, 2)) / 2, 2) >= 90 ? 'A' : (round(($performancePerc + round($productivityScore, 2)) / 2, 2) >= 80 ? 'B' : (round(($performancePerc + round($productivityScore, 2)) / 2, 2) >= 70 ? 'C' : 'D'))) : 'F',
                    'remarks' => $remarks,
                ]);
            }
        }
    }
}
