<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use App\Models\Hris\Position;
use App\Models\Hris\Attendance;
use App\Models\Hris\LeaveRequest;
use App\Models\Hris\LeaveBalance;
use App\Models\Hris\Payroll;
use App\Models\Hris\PerformanceReview;
use App\Models\Hris\PerformanceGoal;
use App\Models\Hris\JobOpening;
use App\Models\Hris\JobApplication;
use App\Models\Hris\Interview;
use App\Models\Hris\OnboardingChecklist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // Employee Statistics
        $employeeStats = [
            'total_employees' => Employee::active()->count(),
            'new_hires_this_month' => Employee::whereYear('hire_date', $currentYear)
                ->whereMonth('hire_date', $currentMonth)->count(),
            'employees_by_department' => Department::withCount(['employees' => function ($query) {
                $query->where('status', 'Active');
            }])->get()->map(function ($dept) {
                return [
                    'name' => $dept->name,
                    'count' => $dept->employees_count
                ];
            }),
            'employees_by_position' => Position::withCount(['employees' => function ($query) {
                $query->where('status', 'Active');
            }])->get()->map(function ($pos) {
                return [
                    'name' => $pos->title,
                    'count' => $pos->employees_count
                ];
            }),
            'upcoming_birthdays' => Employee::active()
                ->whereRaw('CAST(strftime("%m", date_of_birth) AS INTEGER) = ? AND CAST(strftime("%d", date_of_birth) AS INTEGER) BETWEEN ? AND ?', [
                    $currentMonth,
                    Carbon::now()->day,
                    Carbon::now()->addDays(7)->day
                ])
                ->with('department')
                ->get(['id', 'first_name', 'last_name', 'date_of_birth', 'department_id']),
            'work_anniversaries' => Employee::active()
                ->whereRaw('CAST(strftime("%m", hire_date) AS INTEGER) = ? AND CAST(strftime("%d", hire_date) AS INTEGER) BETWEEN ? AND ?', [
                    $currentMonth,
                    Carbon::now()->day,
                    Carbon::now()->addDays(7)->day
                ])
                ->with('department')
                ->get(['id', 'first_name', 'last_name', 'hire_date', 'department_id']),
        ];

        // Attendance Statistics
        $attendanceStats = [
            'today_present' => Attendance::whereDate('date', today())
                ->whereNotNull('clock_in_time')
                ->count(),
            'today_absent' => Employee::active()->count() - Attendance::whereDate('date', today())
                ->whereNotNull('clock_in_time')
                ->count(),
            'late_arrivals_today' => Attendance::whereDate('date', today())
                ->where('is_late', true)
                ->count(),
            'early_departures_today' => Attendance::whereDate('date', today())
                ->where('is_early_departure', true)
                ->count(),
            'average_hours_this_month' => Attendance::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->whereNotNull('total_hours')
                ->avg('total_hours'),
            'overtime_hours_this_month' => Attendance::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->sum('overtime_hours'),
        ];

        // Leave Statistics
        $leaveStats = [
            'pending_requests' => LeaveRequest::where('status', 'Pending')->count(),
            'approved_requests_this_month' => LeaveRequest::where('status', 'Approved')
                ->whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->count(),
            'employees_on_leave_today' => LeaveRequest::where('status', 'Approved')
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
            'upcoming_leaves' => LeaveRequest::where('status', 'Approved')
                ->whereBetween('start_date', [today(), today()->addDays(7)])
                ->with(['employee', 'leaveType'])
                ->get(),
            'leave_balance_alerts' => LeaveBalance::where('remaining_days', '<', 5)
                ->where('remaining_days', '>', 0)
                ->with(['employee', 'leaveType'])
                ->get(),
        ];

        // Payroll Statistics
        $payrollStats = [
            'pending_payrolls' => Payroll::where('status', 'Pending')->count(),
            'total_payroll_this_month' => Payroll::whereYear('pay_period_start', $currentYear)
                ->whereMonth('pay_period_start', $currentMonth)
                ->where('status', 'Paid')
                ->sum('net_pay'),
            'average_salary' => Employee::active()->avg('salary'),
            'payroll_processing_due' => Payroll::where('status', 'Pending')
                ->where('pay_date', '<=', today()->addDays(3))
                ->count(),
        ];

        // Performance Statistics
        $performanceStats = [
            'pending_reviews' => PerformanceReview::where('status', 'In Progress')->count(),
            'overdue_reviews' => PerformanceReview::where('status', 'In Progress')
                ->where('review_date', '<', today())
                ->count(),
            'completed_reviews_this_quarter' => PerformanceReview::where('status', 'Completed')
                ->whereBetween('review_date', [
                    Carbon::now()->startOfQuarter(),
                    Carbon::now()->endOfQuarter()
                ])
                ->count(),
            'active_goals' => PerformanceGoal::where('status', 'In Progress')->count(),
            'goals_due_soon' => PerformanceGoal::where('status', 'In Progress')
                ->whereBetween('target_date', [today(), today()->addDays(7)])
                ->count(),
            'average_performance_rating' => PerformanceReview::where('status', 'Completed')
                ->whereYear('review_date', $currentYear)
                ->avg('overall_rating'),
        ];

        // Recruitment Statistics
        $recruitmentStats = [
            'active_job_openings' => JobOpening::where('status', 'Published')->count(),
            'total_applications_this_month' => JobApplication::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->count(),
            'interviews_scheduled_today' => Interview::whereDate('scheduled_date', today())
                ->where('status', 'Scheduled')
                ->count(),
            'interviews_this_week' => Interview::whereBetween('scheduled_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->where('status', 'Scheduled')->count(),
            'applications_by_status' => JobApplication::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'top_job_openings' => JobOpening::withCount('applications')
                ->where('status', 'Published')
                ->orderBy('applications_count', 'desc')
                ->limit(5)
                ->get(),
        ];

        // Onboarding Statistics
        $onboardingStats = [
            'active_onboarding' => OnboardingChecklist::where('status', 'In Progress')->count(),
            'completed_this_month' => OnboardingChecklist::where('status', 'Completed')
                ->whereYear('completed_date', $currentYear)
                ->whereMonth('completed_date', $currentMonth)
                ->count(),
            'overdue_onboarding' => OnboardingChecklist::where('status', 'In Progress')
                ->where('due_date', '<', today())
                ->count(),
            'due_soon' => OnboardingChecklist::where('status', 'In Progress')
                ->whereBetween('due_date', [today(), today()->addDays(7)])
                ->count(),
            'average_completion_rate' => OnboardingChecklist::where('status', 'In Progress')
                ->avg('completion_percentage'),
        ];

        // Recent Activities
        $recentActivities = collect([
            // Recent hires
            Employee::where('hire_date', '>=', today()->subDays(7))
                ->with('department')
                ->get()
                ->map(function ($employee) {
                    return [
                        'type' => 'new_hire',
                        'title' => "New hire: {$employee->first_name} {$employee->last_name}",
                        'description' => "Joined {$employee->department->name} department",
                        'date' => $employee->hire_date,
                        'icon' => 'user-plus',
                        'color' => 'green'
                    ];
                }),

            // Recent leave requests
            LeaveRequest::where('created_at', '>=', today()->subDays(7))
                ->with(['employee', 'leaveType'])
                ->get()
                ->map(function ($leave) {
                    return [
                        'type' => 'leave_request',
                        'title' => "Leave request: {$leave->employee->first_name} {$leave->employee->last_name}",
                        'description' => "{$leave->leaveType->name} from {$leave->start_date->format('M d')} to {$leave->end_date->format('M d')}",
                        'date' => $leave->created_at,
                        'icon' => 'calendar',
                        'color' => 'blue'
                    ];
                }),

            // Recent job applications
            JobApplication::where('created_at', '>=', today()->subDays(7))
                ->with('jobOpening')
                ->get()
                ->map(function ($application) {
                    return [
                        'type' => 'job_application',
                        'title' => "New application: {$application->first_name} {$application->last_name}",
                        'description' => "Applied for {$application->jobOpening->title}",
                        'date' => $application->created_at,
                        'icon' => 'briefcase',
                        'color' => 'purple'
                    ];
                }),
        ])->flatten(1)->sortByDesc('date')->take(10)->values();

        // Quick Actions
        $quickActions = [
            [
                'title' => 'Add New Employee',
                'description' => 'Register a new employee',
                'route' => 'hris.employees.create',
                'icon' => 'user-plus',
                'color' => 'blue'
            ],
            [
                'title' => 'Process Payroll',
                'description' => 'Generate payroll for employees',
                'route' => 'hris.payroll.create',
                'icon' => 'credit-card',
                'color' => 'green'
            ],
            [
                'title' => 'Schedule Interview',
                'description' => 'Schedule candidate interview',
                'route' => 'hris.recruitment.interviews.create',
                'icon' => 'calendar',
                'color' => 'purple'
            ],
            [
                'title' => 'Create Job Opening',
                'description' => 'Post new job opening',
                'route' => 'hris.recruitment.job-openings.create',
                'icon' => 'briefcase',
                'color' => 'orange'
            ],
            [
                'title' => 'Performance Review',
                'description' => 'Conduct employee review',
                'route' => 'hris.performance.reviews.create',
                'icon' => 'star',
                'color' => 'yellow'
            ],
            [
                'title' => 'Onboarding Checklist',
                'description' => 'Create onboarding checklist',
                'route' => 'hris.onboarding.create',
                'icon' => 'check-circle',
                'color' => 'indigo'
            ],
        ];

        // Alerts and Notifications
        $alerts = [
            // Overdue performance reviews
            PerformanceReview::where('status', 'In Progress')
                ->where('review_date', '<', today())
                ->count() > 0 ? [
                    'type' => 'warning',
                    'title' => 'Overdue Performance Reviews',
                    'message' => PerformanceReview::where('status', 'In Progress')
                        ->where('review_date', '<', today())
                        ->count() . ' performance reviews are overdue',
                    'action' => 'hris.performance.reviews.index'
                ] : null,

            // Pending leave requests
            LeaveRequest::where('status', 'Pending')->count() > 0 ? [
                'type' => 'info',
                'title' => 'Pending Leave Requests',
                'message' => LeaveRequest::where('status', 'Pending')->count() . ' leave requests need approval',
                'action' => 'hris.leave.index'
            ] : null,

            // Overdue onboarding
            OnboardingChecklist::where('status', 'In Progress')
                ->where('due_date', '<', today())
                ->count() > 0 ? [
                    'type' => 'error',
                    'title' => 'Overdue Onboarding',
                    'message' => OnboardingChecklist::where('status', 'In Progress')
                        ->where('due_date', '<', today())
                        ->count() . ' onboarding checklists are overdue',
                    'action' => 'hris.onboarding.index'
                ] : null,

            // Low leave balances
            LeaveBalance::where('remaining_days', '<', 5)
                ->where('remaining_days', '>', 0)
                ->count() > 0 ? [
                    'type' => 'warning',
                    'title' => 'Low Leave Balances',
                    'message' => LeaveBalance::where('remaining_days', '<', 5)
                        ->where('remaining_days', '>', 0)
                        ->count() . ' employees have low leave balances',
                    'action' => 'hris.leave.balances'
                ] : null,
        ];

        // Filter out null alerts
        $alerts = array_filter($alerts);

        return Inertia::render('Hris/Dashboard', [
            'stats' => [
                'employees' => $employeeStats,
                'attendance' => $attendanceStats,
                'leave' => $leaveStats,
                'payroll' => $payrollStats,
                'performance' => $performanceStats,
                'recruitment' => $recruitmentStats,
                'onboarding' => $onboardingStats,
            ],
            'recentActivities' => $recentActivities,
            'quickActions' => $quickActions,
            'alerts' => array_values($alerts),
            'currentDate' => Carbon::now()->format('Y-m-d'),
            'currentUser' => auth()->user(),
        ]);
    }

    /**
     * Get dashboard statistics for API calls.
     */
    public function getStats(Request $request)
    {
        $type = $request->input('type', 'all');
        $period = $request->input('period', 'month'); // day, week, month, quarter, year

        $startDate = match($period) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        $endDate = match($period) {
            'day' => Carbon::today()->endOfDay(),
            'week' => Carbon::now()->endOfWeek(),
            'month' => Carbon::now()->endOfMonth(),
            'quarter' => Carbon::now()->endOfQuarter(),
            'year' => Carbon::now()->endOfYear(),
            default => Carbon::now()->endOfMonth(),
        };

        $stats = [];

        if ($type === 'all' || $type === 'attendance') {
            $stats['attendance'] = [
                'daily_attendance' => Attendance::whereBetween('date', [$startDate, $endDate])
                    ->selectRaw('DATE(date) as date, COUNT(*) as present_count')
                    ->whereNotNull('clock_in_time')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'department_attendance' => Department::withCount(['employees as present_count' => function ($query) use ($startDate, $endDate) {
                    $query->whereHas('attendances', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('date', [$startDate, $endDate])
                          ->whereNotNull('clock_in_time');
                    });
                }])->get(),
            ];
        }

        if ($type === 'all' || $type === 'leave') {
            $stats['leave'] = [
                'leave_trends' => LeaveRequest::whereBetween('start_date', [$startDate, $endDate])
                    ->selectRaw('DATE(start_date) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'leave_by_type' => LeaveRequest::whereBetween('start_date', [$startDate, $endDate])
                    ->join('hris_leave_types', 'hris_leave_requests.leave_type_id', '=', 'hris_leave_types.id')
                    ->selectRaw('hris_leave_types.name, COUNT(*) as count')
                    ->groupBy('hris_leave_types.name')
                    ->get(),
            ];
        }

        if ($type === 'all' || $type === 'recruitment') {
            $stats['recruitment'] = [
                'application_trends' => JobApplication::whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'applications_by_status' => JobApplication::whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
            ];
        }

        return response()->json($stats);
    }

    /**
     * Get upcoming events and deadlines.
     */
    public function getUpcomingEvents(Request $request)
    {
        $days = $request->input('days', 7);
        $endDate = Carbon::now()->addDays($days);

        $events = collect([
            // Upcoming interviews
            Interview::whereBetween('scheduled_date', [now(), $endDate])
                ->where('status', 'Scheduled')
                ->with(['jobApplication', 'primaryInterviewer'])
                ->get()
                ->map(function ($interview) {
                    return [
                        'type' => 'interview',
                        'title' => "Interview: {$interview->jobApplication->first_name} {$interview->jobApplication->last_name}",
                        'description' => "Interview for {$interview->jobApplication->jobOpening->title}",
                        'date' => $interview->scheduled_date,
                        'time' => $interview->scheduled_time,
                        'location' => $interview->location,
                        'priority' => 'high',
                    ];
                }),

            // Performance review deadlines
            PerformanceReview::whereBetween('review_date', [now(), $endDate])
                ->where('status', 'In Progress')
                ->with(['employee', 'reviewer'])
                ->get()
                ->map(function ($review) {
                    return [
                        'type' => 'performance_review',
                        'title' => "Performance Review: {$review->employee->first_name} {$review->employee->last_name}",
                        'description' => "Review period: {$review->review_period}",
                        'date' => $review->review_date,
                        'priority' => $review->review_date < now()->addDays(3) ? 'high' : 'medium',
                    ];
                }),

            // Onboarding deadlines
            OnboardingChecklist::whereBetween('due_date', [now(), $endDate])
                ->where('status', 'In Progress')
                ->with(['employee', 'assignee'])
                ->get()
                ->map(function ($checklist) {
                    return [
                        'type' => 'onboarding',
                        'title' => "Onboarding: {$checklist->employee->first_name} {$checklist->employee->last_name}",
                        'description' => "Completion: {$checklist->completion_percentage}%",
                        'date' => $checklist->due_date,
                        'priority' => $checklist->due_date < now()->addDays(3) ? 'high' : 'medium',
                    ];
                }),

            // Employee birthdays
            Employee::active()
                ->whereRaw('date(strftime("%Y", "now") || "-" || strftime("%m", date_of_birth) || "-" || strftime("%d", date_of_birth)) BETWEEN date("now") AND date("now", "+" || ? || " days")', [$days])
                ->with('department')
                ->get()
                ->map(function ($employee) {
                    $thisYearBirthday = Carbon::createFromFormat('Y-m-d', date('Y') . '-' . $employee->date_of_birth->format('m-d'));
                    if ($thisYearBirthday < Carbon::now()) {
                        $thisYearBirthday->addYear();
                    }

                    return [
                        'type' => 'birthday',
                        'title' => "Birthday: {$employee->first_name} {$employee->last_name}",
                        'description' => "{$employee->department->name} Department",
                        'date' => $thisYearBirthday,
                        'priority' => 'low',
                    ];
                }),

            // Work anniversaries
            Employee::active()
                ->whereRaw('date(strftime("%Y", "now") || "-" || strftime("%m", hire_date) || "-" || strftime("%d", hire_date)) BETWEEN date("now") AND date("now", "+" || ? || " days")', [$days])
                ->with('department')
                ->get()
                ->map(function ($employee) {
                    $thisYearAnniversary = Carbon::createFromFormat('Y-m-d', date('Y') . '-' . $employee->hire_date->format('m-d'));
                    if ($thisYearAnniversary < Carbon::now()) {
                        $thisYearAnniversary->addYear();
                    }

                    $yearsOfService = $thisYearAnniversary->year - $employee->hire_date->year;

                    return [
                        'type' => 'anniversary',
                        'title' => "Work Anniversary: {$employee->first_name} {$employee->last_name}",
                        'description' => "{$yearsOfService} years of service",
                        'date' => $thisYearAnniversary,
                        'priority' => 'low',
                    ];
                }),
        ])->flatten(1)->sortBy('date')->values();

        return response()->json($events);
    }
}
