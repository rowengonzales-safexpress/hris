<?php

namespace App\Http\Controllers\Sqdcm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sqdcm\SqdcmAttendPerfSiteDaily;
use App\Models\Sqdcm\SqdcmAttendPerfDeptDaily;
use App\Models\Sqdcm\SqdcmAttendPerfEmpDaily;
use App\Models\Sqdcm\SqdcmKpiValuesDaily;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{

    public function index(): Response
    {
        // Get sites data for the dropdown
        $sites = SqdcmAttendPerfSiteDaily::select('site_code')
            ->distinct()
            ->orderBy('site_code')
            ->get()
            ->map(function ($site) {
                return [
                    'site_code' => $site->site_code,
                    'site_name' => $this->getSiteName($site->site_code),
                    'is_default' => false
                ];
            });

        return Inertia::render('Sqdcm/Dashboard/index', [
            'sites' => $sites
        ]);
    }

    /**
     * Get dashboard data for PISM homepage
     */
       public function getDashboardData(Request $request)
    {
        try {
            $siteCode = $request->input('site_code');
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            $data = [
                'kpi_utilization' => $this->getKpiUtilization($siteCode, $startDate, $endDate),
                'total_entries' => $this->getTotalEntries($siteCode, $startDate, $endDate),
                'total_site_kpis' => $this->getTotalSiteKpis($siteCode, $startDate, $endDate),
                'kpi_utilization_chart' => $this->getKpiUtilizationChart($siteCode, $startDate, $endDate),
                'kpi_standing_chart' => $this->getKpiStandingChart($siteCode, $startDate, $endDate),
                'attendance_summary' => $this->getAttendanceSummary($siteCode, $startDate, $endDate),
                'performance_summary' => $this->getPerformanceSummary($siteCode, $startDate, $endDate),
                'kpi_statistics' => $this->getKpiStatistics($siteCode, $startDate, $endDate),
                'monthly_entries' => $this->getMonthlyEntries($siteCode, $startDate, $endDate),
                'weekly_entries' => $this->getWeeklyEntries($siteCode, $startDate, $endDate),
                'daily_entries' => $this->getDailyEntries($siteCode, $startDate, $endDate)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }


    // Private helper methods

    private function getKpiUtilization($siteCode, $startDate, $endDate)
    {
        $query = SqdcmKpiValuesDaily::query();

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        $totalKpis = $query->whereBetween('date', [$startDate, $endDate])->count();
        $achievedKpis = $query->whereBetween('date', [$startDate, $endDate])
            ->where('achievement_perc', '>=', 100)
            ->count();

        return $totalKpis > 0 ? round(($achievedKpis / $totalKpis) * 100, 2) : 0;
    }


     private function getTotalEntries($siteCode, $startDate, $endDate)
    {
        $siteEntries = SqdcmAttendPerfSiteDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$startDate, $endDate])->count();

        $deptEntries = SqdcmAttendPerfDeptDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$startDate, $endDate])->count();

        $empEntries = SqdcmAttendPerfEmpDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$startDate, $endDate])->count();

        return [
            'site_entries' => $siteEntries,
            'department_entries' => $deptEntries,
            'employee_entries' => $empEntries,
            'total' => $siteEntries + $deptEntries + $empEntries
        ];
    }

    private function getTotalSiteKpis($siteCode, $startDate, $endDate)
    {
        $query = SqdcmKpiValuesDaily::query();

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        return $query->whereBetween('date', [$startDate, $endDate])
            ->count();
    }

    private function getKpiUtilizationChart($siteCode, $startDate, $endDate)
    {
        $query = SqdcmKpiValuesDaily::select(
            DB::raw('DATE(date) as chart_date'),
            DB::raw('COUNT(*) as total_kpis'),
            DB::raw('SUM(CASE WHEN achievement_perc >= 100 THEN 1 ELSE 0 END) as achieved_kpis')
        );

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        $data = $query->whereBetween('date', [$startDate, $endDate])
            ->groupBy('chart_date')
            ->orderBy('chart_date')
            ->get()
            ->map(function ($item) {
                $utilization = $item->total_kpis > 0 ?
                    round(($item->achieved_kpis / $item->total_kpis) * 100, 2) : 0;
                return [
                    'date' => $item->chart_date,
                    'utilization' => $utilization,
                    'total_kpis' => $item->total_kpis,
                    'achieved_kpis' => $item->achieved_kpis
                ];
            });

        return $data;
    }


    private function getKpiStandingChart($siteCode, $startDate, $endDate)
    {
        $query = SqdcmKpiValuesDaily::select(
            'kpi_name',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN achievement_perc >= 100 THEN 1 ELSE 0 END) as hit'),
            DB::raw('SUM(CASE WHEN achievement_perc < 100 THEN 1 ELSE 0 END) as miss')
        );

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        return $query->whereBetween('date', [$startDate, $endDate])
            ->groupBy('kpi_name')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->kpi_name,
                    'total' => $item->total,
                    'hit' => $item->hit,
                    'miss' => $item->miss,
                    'hit_percentage' => $item->total > 0 ? round(($item->hit / $item->total) * 100, 2) : 0
                ];
            });
    }


   private function getAttendanceSummary($siteCode, $startDate, $endDate)
    {
        $query = SqdcmAttendPerfSiteDaily::query();

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        $data = $query->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                AVG(attendance_perc) as avg_attendance,
                AVG(total_emp) as avg_total_emp,
                AVG(present_emp) as avg_present_emp,
                MAX(attendance_perc) as max_attendance,
                MIN(attendance_perc) as min_attendance
            ')
            ->first();

        return [
            'average_attendance' => round($data->avg_attendance ?? 0, 2),
            'average_total_employees' => round($data->avg_total_emp ?? 0),
            'average_present_employees' => round($data->avg_present_emp ?? 0),
            'max_attendance' => round($data->max_attendance ?? 0, 2),
            'min_attendance' => round($data->min_attendance ?? 0, 2)
        ];
    }


    private function getPerformanceSummary($siteCode, $startDate, $endDate)
    {
        $query = SqdcmAttendPerfSiteDaily::query();

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        $data = $query->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                AVG(performance_perc) as avg_performance,
                AVG(overall_score) as avg_rating,
                SUM(total_target) as total_targets,
                SUM(total_achieved) as total_achieved,
                MAX(performance_perc) as max_performance,
                MIN(performance_perc) as min_performance



            ')
            ->first();

        $overallAchievement = $data->total_targets > 0 ?
            round(($data->total_achieved / $data->total_targets) * 100, 2) : 0;

        return [
            'average_performance' => round($data->avg_performance ?? 0, 2),
            'average_rating' => round($data->avg_rating ?? 0, 2),
            'overall_achievement' => $overallAchievement,
            'total_targets' => $data->total_targets ?? 0,
            'total_achieved' => $data->total_achieved ?? 0,
            'max_performance' => round($data->max_performance ?? 0, 2),
            'min_performance' => round($data->min_performance ?? 0, 2)
        ];
    }

    private function getSiteName($siteCode)
    {
        $siteNames = [
             'SITE001' => 'SYSU',
            'SITE002' => 'JSU',
            'SITE003' => 'MOBILE',
            'SITE004' => 'KUYA J',
            'SITE005' => 'SPORTFIT'
        ];

        return $siteNames[$siteCode] ?? $siteCode;
    }

    private function getKpiStatistics($siteCode, $startDate, $endDate)
    {
        $query = SqdcmKpiValuesDaily::query();

        if ($siteCode) {
            $query->where('site_code', $siteCode);
        }

        $data = $query->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_kpis,
                SUM(CASE WHEN achievement_perc >= 100 THEN 1 ELSE 0 END) as achieved_kpis,
                SUM(CASE WHEN achievement_perc < 50 THEN 1 ELSE 0 END) as poor_kpis,
                AVG(achievement_perc) as avg_achievement
            ')
            ->first();

        $utilization = $data->total_kpis > 0 ?
            round(($data->achieved_kpis / $data->total_kpis) * 100, 2) : 0;

        return [
            'current_utilization' => $utilization,
            'total_site_kpi' => $data->total_kpis ?? 0,
            'kpi_with_data' => $data->achieved_kpis ?? 0,
            'kpi_without_data' => ($data->total_kpis ?? 0) - ($data->achieved_kpis ?? 0),
            'avg_achievement' => round($data->avg_achievement ?? 0, 2)
        ];
    }

    private function getMonthlyEntries($siteCode, $startDate, $endDate)
    {
        $siteEntries = SqdcmAttendPerfSiteDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$startDate, $endDate])->count();

        $deptEntries = SqdcmAttendPerfDeptDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$startDate, $endDate])->count();

        $empEntries = SqdcmAttendPerfEmpDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$startDate, $endDate])->count();

        return $siteEntries + $deptEntries + $empEntries;
    }

    private function getWeeklyEntries($siteCode, $startDate, $endDate)
    {
        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $weekEnd = Carbon::now()->endOfWeek()->format('Y-m-d');

        $siteEntries = SqdcmAttendPerfSiteDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$weekStart, $weekEnd])->count();

        $deptEntries = SqdcmAttendPerfDeptDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$weekStart, $weekEnd])->count();

        $empEntries = SqdcmAttendPerfEmpDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereBetween('date', [$weekStart, $weekEnd])->count();

        return $siteEntries + $deptEntries + $empEntries;
    }

    private function getDailyEntries($siteCode, $startDate, $endDate)
    {
        $today = Carbon::now()->format('Y-m-d');

        $siteEntries = SqdcmAttendPerfSiteDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereDate('date', $today)->count();

        $deptEntries = SqdcmAttendPerfDeptDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereDate('date', $today)->count();

        $empEntries = SqdcmAttendPerfEmpDaily::when($siteCode, function ($query) use ($siteCode) {
            return $query->where('site_code', $siteCode);
        })->whereDate('date', $today)->count();

        return $siteEntries + $deptEntries + $empEntries;
    }
}
