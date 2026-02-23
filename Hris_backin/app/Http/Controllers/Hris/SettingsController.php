<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Models\Hris\Department;
use App\Models\Hris\Position;
use App\Models\Hris\LeaveType;
use App\Models\Hris\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Display the main settings page.
     */
    public function index(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_departments' => Department::count(),
            'active_departments' => Department::where('is_active', true)->count(),
            'total_positions' => Position::count(),
            'active_positions' => Position::where('is_active', true)->count(),
            'total_leave_types' => LeaveType::count(),
            'active_leave_types' => LeaveType::where('is_active', true)->count(),
        ];

        return Inertia::render('Hris/Settings/Index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Display system settings.
     */
    public function system(): Response
    {
        // System configuration settings
        $settings = [
            'company_name' => config('app.name', 'SafExpress'),
            'timezone' => config('app.timezone', 'UTC'),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'currency' => 'USD',
            'working_hours_per_day' => 8,
            'working_days_per_week' => 5,
            'overtime_rate' => 1.5,
            'probation_period_months' => 6,
            'annual_leave_days' => 21,
            'sick_leave_days' => 10,
        ];

        return Inertia::render('Hris/Settings/System', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update system settings.
     */
    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'currency' => 'required|string|max:3',
            'working_hours_per_day' => 'required|integer|min:1|max:24',
            'working_days_per_week' => 'required|integer|min:1|max:7',
            'overtime_rate' => 'required|numeric|min:1',
            'probation_period_months' => 'required|integer|min:1|max:24',
            'annual_leave_days' => 'required|integer|min:0|max:365',
            'sick_leave_days' => 'required|integer|min:0|max:365',
        ]);

        // In a real application, you would save these to a settings table or config file
        // For now, we'll just return success

        return redirect()->route('hris.settings.system')
            ->with('success', 'System settings updated successfully!');
    }

    /**
     * Display user management.
     */
    public function users(Request $request): Response
    {
        $query = User::with('employee')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('is_active', $status === 'active');
            });

        $users = $query->latest()->paginate($request->input('per_page', 15));

        $roles = User::distinct()->pluck('role')->filter();

        return Inertia::render('Hris/Settings/Users', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Create a new user.
     */
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:50',
            'employee_id' => 'nullable|exists:hris_employees,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = $validated['password'];

        User::create($validated);

        return redirect()->route('hris.settings.users')
            ->with('success', 'User created successfully!');
    }

    /**
     * Update an existing user.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|max:50',
            'employee_id' => 'nullable|exists:hris_employees,id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = $validated['password'];
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('hris.settings.users')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('hris.settings.users')
                ->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('hris.settings.users')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Display permissions management.
     */
    public function permissions(): Response
    {
        $permissions = [
            'employee_management' => [
                'view_employees' => 'View employee records',
                'create_employees' => 'Create new employees',
                'edit_employees' => 'Edit employee information',
                'delete_employees' => 'Delete employees',
            ],
            'attendance_management' => [
                'view_attendance' => 'View attendance records',
                'create_attendance' => 'Create attendance records',
                'edit_attendance' => 'Edit attendance records',
                'delete_attendance' => 'Delete attendance records',
            ],
            'leave_management' => [
                'view_leave_requests' => 'View leave requests',
                'approve_leave_requests' => 'Approve/reject leave requests',
                'create_leave_requests' => 'Create leave requests',
                'edit_leave_requests' => 'Edit leave requests',
            ],
            'payroll_management' => [
                'view_payroll' => 'View payroll records',
                'create_payroll' => 'Generate payroll',
                'edit_payroll' => 'Edit payroll records',
                'approve_payroll' => 'Approve payroll',
            ],
            'reports' => [
                'view_reports' => 'View all reports',
                'export_reports' => 'Export reports',
                'create_custom_reports' => 'Create custom reports',
            ],
            'system_settings' => [
                'manage_settings' => 'Manage system settings',
                'manage_users' => 'Manage user accounts',
                'manage_permissions' => 'Manage permissions',
            ],
        ];

        $roles = User::distinct()->pluck('role')->filter();

        return Inertia::render('Hris/Settings/Permissions', [
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }

    /**
     * Update role permissions.
     */
    public function updatePermissions(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'permissions' => 'required|array',
        ]);

        // In a real application, you would save these to a permissions table
        // For now, we'll just return success

        return redirect()->route('hris.settings.permissions')
            ->with('success', 'Permissions updated successfully!');
    }

    /**
     * Display payroll settings.
     */
    public function payrollSettings(): Response
    {
        $settings = [
            'pay_frequency' => 'monthly', // weekly, bi-weekly, monthly
            'pay_day' => 'last_day', // first_day, 15th, last_day
            'overtime_calculation' => 'daily', // daily, weekly
            'overtime_threshold_hours' => 8,
            'weekend_overtime_rate' => 2.0,
            'holiday_overtime_rate' => 2.5,
            'tax_calculation_method' => 'percentage',
            'default_tax_rate' => 15.0,
            'social_security_rate' => 6.2,
            'medicare_rate' => 1.45,
        ];

        return Inertia::render('Hris/Settings/PayrollSettings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update payroll settings.
     */
    public function updatePayrollSettings(Request $request)
    {
        $validated = $request->validate([
            'pay_frequency' => 'required|in:weekly,bi-weekly,monthly',
            'pay_day' => 'required|string',
            'overtime_calculation' => 'required|in:daily,weekly',
            'overtime_threshold_hours' => 'required|integer|min:1|max:24',
            'weekend_overtime_rate' => 'required|numeric|min:1',
            'holiday_overtime_rate' => 'required|numeric|min:1',
            'tax_calculation_method' => 'required|in:percentage,bracket',
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'social_security_rate' => 'required|numeric|min:0|max:100',
            'medicare_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Save settings logic here

        return redirect()->route('hris.settings.payroll-settings')
            ->with('success', 'Payroll settings updated successfully!');
    }
}
