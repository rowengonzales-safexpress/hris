<?php

use App\Http\Controllers\Hris\DashboardController;
use App\Http\Controllers\Hris\EmployeeController;
use App\Http\Controllers\Hris\AttendanceController;
use App\Http\Controllers\Hris\LeaveController;
use App\Http\Controllers\Hris\PayrollController;
use App\Http\Controllers\Hris\PerformanceController;
use App\Http\Controllers\Hris\RecruitmentController;
use App\Http\Controllers\Hris\OnboardingController;
use App\Http\Controllers\Hris\DepartmentController;
use App\Http\Controllers\Hris\PositionController;
use App\Http\Controllers\Hris\LeaveTypeController;
use App\Http\Controllers\Hris\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRIS Routes
|--------------------------------------------------------------------------
|
| Here is where you can register HRIS routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "hris" middleware group.
|
*/

Route::prefix('hris')->name('hris.')->middleware(['auth', 'hris', 'check.app.user:3'])->group(function () {

    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/events', [DashboardController::class, 'getUpcomingEvents'])->name('dashboard.events');

    // Employee Management Routes
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::get('/department/{department}', [EmployeeController::class, 'getByDepartment'])->name('by-department');
        Route::get('/stats/overview', [EmployeeController::class, 'getStats'])->name('stats');
    });

    // Attendance Management Routes
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('clock-out');
        Route::get('/stats/overview', [AttendanceController::class, 'getStats'])->name('stats');
        Route::get('/reports/monthly', [AttendanceController::class, 'monthlyReport'])->name('reports.monthly');
    });

    // Leave Management Routes
    Route::prefix('leave')->name('leave.')->group(function () {
        // Leave Requests
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [LeaveController::class, 'show'])->name('show');
        Route::get('/{leaveRequest}/edit', [LeaveController::class, 'edit'])->name('edit');
        Route::put('/{leaveRequest}', [LeaveController::class, 'update'])->name('update');
        Route::delete('/{leaveRequest}', [LeaveController::class, 'destroy'])->name('destroy');
        Route::post('/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('reject');
        Route::post('/{leaveRequest}/cancel', [LeaveController::class, 'cancel'])->name('cancel');

        // Leave Balances
        Route::get('/balances', [LeaveController::class, 'balances'])->name('balances');
        Route::get('/balances/{employee}', [LeaveController::class, 'employeeBalances'])->name('balances.employee');

        // Leave Statistics
        Route::get('/stats/overview', [LeaveController::class, 'getStats'])->name('stats');
    });

    // Payroll Management Routes
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/create', [PayrollController::class, 'create'])->name('create');
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
        Route::get('/{payroll}/edit', [PayrollController::class, 'edit'])->name('edit');
        Route::put('/{payroll}', [PayrollController::class, 'update'])->name('update');
        Route::delete('/{payroll}', [PayrollController::class, 'destroy'])->name('destroy');
        Route::post('/{payroll}/approve', [PayrollController::class, 'approve'])->name('approve');
        Route::post('/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('mark-paid');
        Route::post('/generate-bulk', [PayrollController::class, 'generateBulk'])->name('generate-bulk');
        Route::get('/stats/overview', [PayrollController::class, 'getStats'])->name('stats');
        Route::get('/export/{format}', [PayrollController::class, 'export'])->name('export');
    });

    // Performance Management Routes
    Route::prefix('performance')->name('performance.')->group(function () {
        // Performance Reviews
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [PerformanceController::class, 'indexReviews'])->name('index');
            Route::get('/create', [PerformanceController::class, 'createReview'])->name('create');
            Route::post('/', [PerformanceController::class, 'storeReview'])->name('store');
            Route::get('/{performanceReview}', [PerformanceController::class, 'showReview'])->name('show');
            Route::get('/{performanceReview}/edit', [PerformanceController::class, 'editReview'])->name('edit');
            Route::put('/{performanceReview}', [PerformanceController::class, 'updateReview'])->name('update');
            Route::delete('/{performanceReview}', [PerformanceController::class, 'destroyReview'])->name('destroy');
            Route::post('/{performanceReview}/submit', [PerformanceController::class, 'submitReview'])->name('submit');
            Route::post('/{performanceReview}/approve', [PerformanceController::class, 'approveReview'])->name('approve');
        });

        // Performance Goals
        Route::prefix('goals')->name('goals.')->group(function () {
            Route::get('/', [PerformanceController::class, 'indexGoals'])->name('index');
            Route::get('/create', [PerformanceController::class, 'createGoal'])->name('create');
            Route::post('/', [PerformanceController::class, 'storeGoal'])->name('store');
            Route::get('/{performanceGoal}', [PerformanceController::class, 'showGoal'])->name('show');
            Route::get('/{performanceGoal}/edit', [PerformanceController::class, 'editGoal'])->name('edit');
            Route::put('/{performanceGoal}', [PerformanceController::class, 'updateGoal'])->name('update');
            Route::delete('/{performanceGoal}', [PerformanceController::class, 'destroyGoal'])->name('destroy');
            Route::post('/{performanceGoal}/update-progress', [PerformanceController::class, 'updateProgress'])->name('update-progress');
            Route::post('/{performanceGoal}/complete', [PerformanceController::class, 'completeGoal'])->name('complete');
            Route::post('/{performanceGoal}/cancel', [PerformanceController::class, 'cancelGoal'])->name('cancel');
        });

        // Performance Statistics
        Route::get('/stats/overview', [PerformanceController::class, 'getStats'])->name('stats');
    });

    // Recruitment Management Routes
    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        // Job Openings
        Route::prefix('job-openings')->name('job-openings.')->group(function () {
            Route::get('/', [RecruitmentController::class, 'indexJobOpenings'])->name('index');
            Route::get('/create', [RecruitmentController::class, 'createJobOpening'])->name('create');
            Route::post('/', [RecruitmentController::class, 'storeJobOpening'])->name('store');
            Route::get('/{jobOpening}', [RecruitmentController::class, 'showJobOpening'])->name('show');
            Route::get('/{jobOpening}/edit', [RecruitmentController::class, 'editJobOpening'])->name('edit');
            Route::put('/{jobOpening}', [RecruitmentController::class, 'updateJobOpening'])->name('update');
            Route::delete('/{jobOpening}', [RecruitmentController::class, 'destroyJobOpening'])->name('destroy');
            Route::post('/{jobOpening}/publish', [RecruitmentController::class, 'publishJobOpening'])->name('publish');
            Route::post('/{jobOpening}/close', [RecruitmentController::class, 'closeJobOpening'])->name('close');
        });

        // Job Applications
        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/', [RecruitmentController::class, 'indexApplications'])->name('index');
            Route::get('/create', [RecruitmentController::class, 'createApplication'])->name('create');
            Route::post('/', [RecruitmentController::class, 'storeApplication'])->name('store');
            Route::get('/{jobApplication}', [RecruitmentController::class, 'showApplication'])->name('show');
            Route::get('/{jobApplication}/edit', [RecruitmentController::class, 'editApplication'])->name('edit');
            Route::put('/{jobApplication}', [RecruitmentController::class, 'updateApplication'])->name('update');
            Route::delete('/{jobApplication}', [RecruitmentController::class, 'destroyApplication'])->name('destroy');
            Route::post('/{jobApplication}/move-stage', [RecruitmentController::class, 'moveToNextStage'])->name('move-stage');
            Route::post('/{jobApplication}/reject', [RecruitmentController::class, 'rejectApplication'])->name('reject');
            Route::post('/{jobApplication}/hire', [RecruitmentController::class, 'hireCandidate'])->name('hire');
        });

        // Interviews
        Route::prefix('interviews')->name('interviews.')->group(function () {
            Route::get('/', [RecruitmentController::class, 'indexInterviews'])->name('index');
            Route::get('/create', [RecruitmentController::class, 'createInterview'])->name('create');
            Route::post('/', [RecruitmentController::class, 'storeInterview'])->name('store');
            Route::get('/{interview}', [RecruitmentController::class, 'showInterview'])->name('show');
            Route::get('/{interview}/edit', [RecruitmentController::class, 'editInterview'])->name('edit');
            Route::put('/{interview}', [RecruitmentController::class, 'updateInterview'])->name('update');
            Route::delete('/{interview}', [RecruitmentController::class, 'destroyInterview'])->name('destroy');
            Route::post('/{interview}/complete', [RecruitmentController::class, 'completeInterview'])->name('complete');
            Route::post('/{interview}/cancel', [RecruitmentController::class, 'cancelInterview'])->name('cancel');
            Route::post('/{interview}/reschedule', [RecruitmentController::class, 'rescheduleInterview'])->name('reschedule');
        });

        // Recruitment Statistics
        Route::get('/stats/overview', [RecruitmentController::class, 'getStats'])->name('stats');
    });

    // Onboarding Management Routes
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [OnboardingController::class, 'index'])->name('index');
        Route::get('/create', [OnboardingController::class, 'create'])->name('create');
        Route::post('/', [OnboardingController::class, 'store'])->name('store');
        Route::get('/{onboarding}', [OnboardingController::class, 'show'])->name('show');
        Route::get('/{onboarding}/edit', [OnboardingController::class, 'edit'])->name('edit');
        Route::put('/{onboarding}', [OnboardingController::class, 'update'])->name('update');
        Route::delete('/{onboarding}', [OnboardingController::class, 'destroy'])->name('destroy');

        // Checklist Item Management
        Route::post('/{onboarding}/complete-item', [OnboardingController::class, 'completeItem'])->name('complete-item');
        Route::post('/{onboarding}/uncomplete-item', [OnboardingController::class, 'uncompleteItem'])->name('uncomplete-item');
        Route::post('/{onboarding}/add-item', [OnboardingController::class, 'addItem'])->name('add-item');
        Route::post('/{onboarding}/remove-item', [OnboardingController::class, 'removeItem'])->name('remove-item');

        // Checklist Management
        Route::post('/{onboarding}/complete', [OnboardingController::class, 'complete'])->name('complete');
        Route::post('/{onboarding}/reopen', [OnboardingController::class, 'reopen'])->name('reopen');

        // Bulk Operations
        Route::post('/generate-bulk', [OnboardingController::class, 'generateBulk'])->name('generate-bulk');

        // Templates and Statistics
        Route::get('/templates', [OnboardingController::class, 'getTemplates'])->name('templates');
        Route::get('/stats/overview', [OnboardingController::class, 'getStats'])->name('stats');

        // Checklist Routes
        Route::prefix('checklist')->name('checklist.')->group(function () {
            Route::get('/', [OnboardingController::class, 'indexChecklist'])->name('index');
            Route::get('/create', [OnboardingController::class, 'createChecklist'])->name('create');
            Route::post('/', [OnboardingController::class, 'storeChecklist'])->name('store');
            Route::get('/{checklist}', [OnboardingController::class, 'showChecklist'])->name('show');
            Route::get('/{checklist}/edit', [OnboardingController::class, 'editChecklist'])->name('edit');
            Route::put('/{checklist}', [OnboardingController::class, 'updateChecklist'])->name('update');
            Route::delete('/{checklist}', [OnboardingController::class, 'destroyChecklist'])->name('destroy');
        });

        // Training Routes
        Route::prefix('training')->name('training.')->group(function () {
            Route::get('/', [OnboardingController::class, 'indexTraining'])->name('index');
            Route::get('/create', [OnboardingController::class, 'createTraining'])->name('create');
            Route::post('/', [OnboardingController::class, 'storeTraining'])->name('store');
            Route::get('/{training}', [OnboardingController::class, 'showTraining'])->name('show');
            Route::get('/{training}/edit', [OnboardingController::class, 'editTraining'])->name('edit');
            Route::put('/{training}', [OnboardingController::class, 'updateTraining'])->name('update');
            Route::delete('/{training}', [OnboardingController::class, 'destroyTraining'])->name('destroy');
        });
    });

    // Department Management Routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
        Route::get('/stats/overview', [DepartmentController::class, 'getStats'])->name('stats');
    });

    // Position Management Routes
    Route::prefix('positions')->name('positions.')->group(function () {
        Route::get('/', [PositionController::class, 'index'])->name('index');
        Route::get('/create', [PositionController::class, 'create'])->name('create');
        Route::post('/', [PositionController::class, 'store'])->name('store');
        Route::get('/{position}', [PositionController::class, 'show'])->name('show');
        Route::get('/{position}/edit', [PositionController::class, 'edit'])->name('edit');
        Route::put('/{position}', [PositionController::class, 'update'])->name('update');
        Route::delete('/{position}', [PositionController::class, 'destroy'])->name('destroy');
        Route::get('/stats/overview', [PositionController::class, 'getStats'])->name('stats');
    });

    // Leave Type Management Routes
    Route::prefix('leave-types')->name('leave-types.')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::get('/create', [LeaveTypeController::class, 'create'])->name('create');
        Route::post('/', [LeaveTypeController::class, 'store'])->name('store');
        Route::get('/{leaveType}', [LeaveTypeController::class, 'show'])->name('show');
        Route::get('/{leaveType}/edit', [LeaveTypeController::class, 'edit'])->name('edit');
        Route::put('/{leaveType}', [LeaveTypeController::class, 'update'])->name('update');
        Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])->name('destroy');
        Route::get('/stats/overview', [LeaveTypeController::class, 'getStats'])->name('stats');
    });

    // Leave Balance Routes
    Route::prefix('leave-balances')->name('leave-balances.')->group(function () {
        Route::get('/', [LeaveController::class, 'indexBalances'])->name('index');
        Route::get('/create', [LeaveController::class, 'createBalance'])->name('create');
        Route::post('/', [LeaveController::class, 'storeBalance'])->name('store');
        Route::get('/{leaveBalance}', [LeaveController::class, 'showBalance'])->name('show');
        Route::get('/{leaveBalance}/edit', [LeaveController::class, 'editBalance'])->name('edit');
        Route::put('/{leaveBalance}', [LeaveController::class, 'updateBalance'])->name('update');
        Route::delete('/{leaveBalance}', [LeaveController::class, 'destroyBalance'])->name('destroy');
        Route::post('/bulk-update', [LeaveController::class, 'bulkUpdateBalances'])->name('bulk-update');
    });

    // Master Data Routes (for dropdowns and reference data)
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/departments', function () {
            return response()->json(\App\Models\Hris\Department::active()->get(['id', 'name', 'code']));
        })->name('departments');

        Route::get('/positions', function () {
            return response()->json(\App\Models\Hris\Position::active()->with('department:id,name')->get(['id', 'title', 'department_id']));
        })->name('positions');

        Route::get('/leave-types', function () {
            return response()->json(\App\Models\Hris\LeaveType::active()->get(['id', 'name', 'code', 'days_per_year', 'is_paid']));
        })->name('leave-types');

        Route::get('/employees', function () {
            return response()->json(\App\Models\Hris\Employee::active()->with('department:id,name')->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']));
        })->name('employees');

        Route::get('/job-openings', function () {
            return response()->json(\App\Models\Hris\JobOpening::where('status', 'Published')->with(['department:id,name', 'position:id,title'])->get(['id', 'title', 'department_id', 'position_id']));
        })->name('job-openings');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/employee-summary', [EmployeeController::class, 'employeeSummaryReport'])->name('employee-summary');
        Route::get('/attendance-summary', [AttendanceController::class, 'attendanceSummaryReport'])->name('attendance-summary');
        Route::get('/leave-summary', [LeaveController::class, 'leaveSummaryReport'])->name('leave-summary');
        Route::get('/payroll-summary', [PayrollController::class, 'payrollSummaryReport'])->name('payroll-summary');
        Route::get('/performance-summary', [PerformanceController::class, 'performanceSummaryReport'])->name('performance-summary');
        Route::get('/recruitment-summary', [RecruitmentController::class, 'recruitmentSummaryReport'])->name('recruitment-summary');
        Route::get('/onboarding-summary', [OnboardingController::class, 'onboardingSummaryReport'])->name('onboarding-summary');
    });

    // Settings Routes (for HRIS configuration)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        // System Settings
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SettingsController::class, 'systemSettings'])->name('index');
            Route::post('/', [SettingsController::class, 'updateSystemSettings'])->name('update');
        });

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [SettingsController::class, 'indexUsers'])->name('index');
            Route::get('/create', [SettingsController::class, 'createUser'])->name('create');
            Route::post('/', [SettingsController::class, 'storeUser'])->name('store');
            Route::get('/{user}', [SettingsController::class, 'showUser'])->name('show');
            Route::get('/{user}/edit', [SettingsController::class, 'editUser'])->name('edit');
            Route::put('/{user}', [SettingsController::class, 'updateUser'])->name('update');
            Route::delete('/{user}', [SettingsController::class, 'destroyUser'])->name('destroy');
            Route::post('/{user}/toggle-status', [SettingsController::class, 'toggleUserStatus'])->name('toggle-status');
            Route::post('/{user}/reset-password', [SettingsController::class, 'resetPassword'])->name('reset-password');
        });

        // Permissions Management
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [SettingsController::class, 'indexPermissions'])->name('index');
            Route::get('/create', [SettingsController::class, 'createPermission'])->name('create');
            Route::post('/', [SettingsController::class, 'storePermission'])->name('store');
            Route::get('/{permission}', [SettingsController::class, 'showPermission'])->name('show');
            Route::get('/{permission}/edit', [SettingsController::class, 'editPermission'])->name('edit');
            Route::put('/{permission}', [SettingsController::class, 'updatePermission'])->name('update');
            Route::delete('/{permission}', [SettingsController::class, 'destroyPermission'])->name('destroy');
            Route::post('/assign-role', [SettingsController::class, 'assignRole'])->name('assign-role');
            Route::post('/revoke-role', [SettingsController::class, 'revokeRole'])->name('revoke-role');
        });

        // Payroll Settings
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [SettingsController::class, 'payrollSettings'])->name('index');
            Route::post('/', [SettingsController::class, 'updatePayrollSettings'])->name('update');
        });
    });

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');

    Route::patch('/notifications/{notification}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});
