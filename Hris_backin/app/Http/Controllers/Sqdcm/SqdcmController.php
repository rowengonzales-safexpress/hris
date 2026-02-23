<?php

namespace App\Http\Controllers\Sqdcm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sqdcm\SqdcmAttendPerfSiteDaily;
use App\Models\Sqdcm\SqdcmAttendPerfDeptDaily;
use App\Models\Sqdcm\SqdcmAttendPerfEmpDaily;
use App\Models\Sqdcm\SqdcmKpiValuesDaily;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SqdcmController extends Controller
{
    /**
     * Get sites data for the authenticated user
     */
    public function getSites(Request $request)
    {
        try {
            $sites = SqdcmAttendPerfSiteDaily::select('site_code')
                ->distinct()
                ->orderBy('site_code')
                ->get()
                ->map(function ($site) {
                    return [
                        'site_code' => $site->site_codeid,
                        'site_name' => $this->getSiteName($site->site_code),
                        'is_default' => false // You can implement user preferences later
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching sites: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get attendance and performance data
     */
    public function getAttendancePerformance(Request $request)
    {
        try {
            $siteCode = $request->input('site_code');
            $deptCode = $request->input('dept_code');
            $startDate = $request->input('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            $level = $request->input('level', 'site'); // site, department, employee

            $query = null;

            switch ($level) {
                case 'site':
                    $query = SqdcmAttendPerfSiteDaily::query();
                    break;
                case 'department':
                    $query = SqdcmAttendPerfDeptDaily::query();
                    break;
                case 'employee':
                    $query = SqdcmAttendPerfEmpDaily::query();
                    break;
                default:
                    $query = SqdcmAttendPerfSiteDaily::query();
            }

            if ($siteCode) {
                $query->where('site_code', $siteCode);
            }

            if ($deptCode && in_array($level, ['department', 'employee'])) {
                $query->where('dept_code', $deptCode);
            }

            $data = $query->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance performance data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get KPI values data
     */
    public function getKpiValues(Request $request)
    {
        try {
            $siteCode = $request->input('site_code');
            $deptCode = $request->input('dept_code');
            $empCode = $request->input('emp_code');
            $category = $request->input('category');
            $startDate = $request->input('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            $criticalOnly = $request->boolean('critical_only', false);

            $query = SqdcmKpiValuesDaily::query();

            if ($siteCode) {
                $query->where('site_code', $siteCode);
            }

            if ($deptCode) {
                $query->where('dept_code', $deptCode);
            }

            if ($empCode) {
                $query->where('emp_code', $empCode);
            }

            if ($category) {
                $query->where('kpi_category', $category);
            }

            if ($criticalOnly) {
                $query->where('is_critical', true);
            }

            $data = $query->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->orderBy('alert_level', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching KPI values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user's default site
     */
    public function updateDefaultSite(Request $request)
    {
        try {
            $siteCode = $request->input('site_code');

            // Here you would typically update user preferences in database
            // For now, just return success

            return response()->json([
                'success' => true,
                'message' => 'Default site updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating default site: ' . $e->getMessage()
            ], 500);
        }
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
}
