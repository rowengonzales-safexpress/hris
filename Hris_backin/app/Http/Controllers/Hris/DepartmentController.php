<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\Department;
use App\Models\Hris\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request): Response
    {
        $query = Department::withCount('employees')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('is_active', $status === 'active');
            });

        $departments = $query->latest()->paginate($request->input('per_page', 15));

        return Inertia::render('Hris/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): Response
    {
        return Inertia::render('Hris/Departments/Create');
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hris_departments,name',
            'code' => 'required|string|max:10|unique:hris_departments,code',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:hris_employees,id',
            'budget' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('hris.departments.index')
            ->with('success', 'Department created successfully!');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): Response
    {
        $department->load([
            'manager',
            'employees.position',
            'employees' => function ($query) {
                $query->active()->latest();
            }
        ]);

        $stats = [
            'total_employees' => $department->employees->count(),
            'active_employees' => $department->employees->where('is_active', true)->count(),
            'average_salary' => $department->employees->avg('salary'),
            'total_budget' => $department->budget,
        ];

        return Inertia::render('Hris/Departments/Show', [
            'department' => $department,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): Response
    {
        $employees = Employee::active()
            ->get(['id', 'first_name', 'last_name', 'employee_id']);

        return Inertia::render('Hris/Departments/Edit', [
            'department' => $department,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hris_departments,name,' . $department->id,
            'code' => 'required|string|max:10|unique:hris_departments,code,' . $department->id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:hris_employees,id',
            'budget' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('hris.departments.index')
            ->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return redirect()->route('hris.departments.index')
                ->with('error', 'Cannot delete department with active employees!');
        }

        $department->delete();

        return redirect()->route('hris.departments.index')
            ->with('success', 'Department deleted successfully!');
    }

    /**
     * Get department statistics
     */
    public function getStats(Department $department)
    {
        $stats = [
            'total_employees' => $department->employees()->count(),
            'active_employees' => $department->employees()->active()->count(),
            'average_salary' => $department->employees()->avg('salary'),
            'total_payroll' => $department->employees()->sum('salary'),
            'positions_count' => $department->employees()->distinct('position_id')->count(),
        ];

        return response()->json($stats);
    }
}