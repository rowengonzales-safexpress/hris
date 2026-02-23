<?php

namespace Database\Seeders\Sqdcm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sqdcm\SqdcmKpiValuesDaily;
use Carbon\Carbon;

class SqdcmKpiValuesDailySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = ['SITE001', 'SITE002', 'SITE003', 'SITE004', 'SITE005'];
        $departments = ['HR', 'IT', 'FINANCE', 'OPERATIONS', 'LOGISTICS', 'SALES', 'MARKETING'];

        $kpis = [
            ['code' => 'PROD001', 'name' => 'Orders Processed', 'unit' => 'count'],
            ['code' => 'PROD002', 'name' => 'Tasks Completed', 'unit' => 'count'],
            ['code' => 'QUAL001', 'name' => 'Error Rate', 'unit' => '%'],
            ['code' => 'QUAL002', 'name' => 'Customer Satisfaction', 'unit' => '%'],
            ['code' => 'SAFE001', 'name' => 'Incident Rate', 'unit' => 'count'],
            ['code' => 'FINA001', 'name' => 'Cost per Unit', 'unit' => 'INR'],
        ];

        // Generate data for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);

            foreach ($sites as $siteCode) {
                foreach ($kpis as $kpi) {
                    // Site-level KPIs
                    $targetValue = rand(50, 1000);
                    $achievedValue = rand(40, $targetValue * 1.2);
                    $performancePerc = ($achievedValue / $targetValue) * 100;
                    $weightage = rand(10, 30);
                    $weightedScore = ($performancePerc * $weightage) / 100;

                    $remarks = '';
                    if ($performancePerc < 70) {
                        $remarks = 'Below target - requires attention';
                    } elseif ($performancePerc > 120) {
                        $remarks = 'Exceeding expectations';
                    }

                    SqdcmKpiValuesDaily::create([
                        'date' => $date,
                        'kpi_name' => $kpi['name'],
                        'site_code' => $siteCode,
                        'target_value' => $targetValue,
                        'actual_value' => $achievedValue,
                        'achievement_perc' => $performancePerc,
                        'remarks' => $remarks,
                    ]);

                    // Department-level KPIs (subset)
                    if (rand(0, 100) < 30) { // 30% chance for department KPI
                        $deptCode = $departments[array_rand($departments)];
                        $targetValue = rand(20, 500);
                        $achievedValue = rand(15, $targetValue * 1.15);
                        $performancePerc = ($achievedValue / $targetValue) * 100;
                        $weightage = rand(5, 20);
                        $weightedScore = ($performancePerc * $weightage) / 100;

                        SqdcmKpiValuesDaily::create([
                            'date' => $date,
                            'kpi_name' => $kpi['name'],
                            'site_code' => $siteCode,
                            'target_value' => $targetValue,
                            'actual_value' => $achievedValue,
                            'achievement_perc' => $performancePerc,
                            'remarks' => $performancePerc < 75 ? 'Needs improvement' : '',
                        ]);
                    }
                }
            }
        }
    }
}
