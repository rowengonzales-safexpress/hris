<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use App\Models\Hris\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request): Response
    {
        $query = Employee::with(['department', 'position', 'manager'])
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->employment_status, function ($query, $status) {
                return $query->where('employment_status', $status);
            })
            ->when($request->employment_type, function ($query, $type) {
                return $query->where('employment_type', $type);
            });

        $employees = $query->latest()->paginate($request->input('per_page', 15));

        $departments = Department::active()->get(['id', 'name']);

        return Inertia::render('Hris/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'department_id', 'employment_status', 'employment_type']),
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): Response
    {
        $departments = Department::active()->get(['id', 'name']);
        $positions = Position::active()->get(['id', 'title', 'department_id']);
        $managers = Employee::active()
            ->whereIn('position_id', Position::whereIn('level', ['Manager', 'Senior Manager', 'Director'])->pluck('id'))
            ->get(['id', 'first_name', 'last_name', 'employee_id']);

        return Inertia::render('Hris/Employee/Create', [
            'departments' => $departments,
            'positions' => $positions,
            'managers' => $managers,
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20|unique:hris_employees,employee_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:hris_employees,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'department_id' => 'required|exists:hris_departments,id',
            'position_id' => 'required|exists:hris_positions,id',
            'manager_id' => 'nullable|exists:hris_employees,id',
            'employment_type' => 'required|in:Full-time,Part-time,Contract,Temporary',
            'employment_status' => 'required|in:Active,Inactive,Terminated,On Leave',
            'salary' => 'required|numeric|min:0',
            'probation_end_date' => 'nullable|date|after:hire_date',
            'is_active' => 'boolean',
        ]);

        Employee::create($request->all());

        return redirect()->route('hris.employees.index')
            ->with('success', 'Employee created successfully!');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): Response
    {
        $employee->load([
            'department',
            'position',
            'manager',
            'subordinates',
            'documents',
            'leaveBalances.leaveType',
            'leaveRequests.leaveType',
            'attendances' => function ($query) {
                $query->latest()->limit(10);
            },
            'payrolls' => function ($query) {
                $query->latest()->limit(6);
            },
            'performanceReviews' => function ($query) {
                $query->latest()->limit(5);
            },
        ]);

        return Inertia::render('Hris/Employee/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): Response
    {
        $departments = Department::active()->get(['id', 'name']);
        $positions = Position::active()->get(['id', 'title', 'department_id']);
        $managers = Employee::active()
            ->where('id', '!=', $employee->id)
            ->whereIn('position_id', Position::whereIn('level', ['Manager', 'Senior Manager', 'Director'])->pluck('id'))
            ->get(['id', 'first_name', 'last_name', 'employee_id']);

        return Inertia::render('Hris/Employee/Edit', [
            'employee' => $employee,
            'departments' => $departments,
            'positions' => $positions,
            'managers' => $managers,
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20|unique:hris_employees,employee_id,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:hris_employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'department_id' => 'required|exists:hris_departments,id',
            'position_id' => 'required|exists:hris_positions,id',
            'manager_id' => 'nullable|exists:hris_employees,id',
            'employment_type' => 'required|in:Full-time,Part-time,Contract,Temporary',
            'employment_status' => 'required|in:Active,Inactive,Terminated,On Leave',
            'salary' => 'required|numeric|min:0',
            'probation_end_date' => 'nullable|date|after:hire_date',
            'termination_date' => 'nullable|date|after:hire_date',
            'termination_reason' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $employee->update($request->all());

        return redirect()->route('hris.employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee)
    {
        // Check if employee has dependent records
        if ($employee->subordinates()->count() > 0) {
            return back()->withErrors(['message' => 'Cannot delete employee with subordinates. Please reassign them first.']);
        }

        if ($employee->leaveRequests()->count() > 0 || $employee->attendances()->count() > 0) {
            return back()->withErrors(['message' => 'Cannot delete employee with attendance or leave records.']);
        }

        $employee->delete();

        return redirect()->route('hris.employees.index')
            ->with('success', 'Employee deleted successfully!');
    }

    /**
     * Get employees by department for dropdown
     */
    public function getByDepartment(Request $request)
    {
        $departmentId = $request->input('department_id');

        $employees = Employee::active()
            ->where('department_id', $departmentId)
            ->get(['id', 'first_name', 'last_name', 'employee_id']);

        return response()->json($employees);
    }

    /**
     * Get employee statistics
     */
    public function getStats()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::active()->count(),
            'new_hires_this_month' => Employee::whereMonth('hire_date', now()->month)
                ->whereYear('hire_date', now()->year)
                ->count(),
            'employees_on_probation' => Employee::active()
                ->where('probation_end_date', '>', now())
                ->count(),
            'by_department' => Employee::with('department')
                ->selectRaw('department_id, count(*) as count')
                ->groupBy('department_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'department' => $item->department->name ?? 'Unknown',
                        'count' => $item->count
                    ];
                }),
            'by_employment_type' => Employee::selectRaw('employment_type, count(*) as count')
                ->groupBy('employment_type')
                ->get()
                ->pluck('count', 'employment_type'),
        ];

        return response()->json($stats);
    }
}
