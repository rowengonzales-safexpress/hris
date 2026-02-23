<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\Position;
use App\Models\Hris\Department;
use App\Models\Hris\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request): Response
    {
        $query = Position::with('department')
            ->withCount('employees')
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->level, function ($query, $level) {
                return $query->where('level', $level);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('is_active', $status === 'active');
            });

        $positions = $query->latest()->paginate($request->input('per_page', 15));
        
        $departments = Department::active()->get(['id', 'name']);
        $levels = Position::distinct()->pluck('level')->filter();

        return Inertia::render('Hris/Positions/Index', [
            'positions' => $positions,
            'departments' => $departments,
            'levels' => $levels,
            'filters' => $request->only(['search', 'department_id', 'level', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new position.
     */
    public function create(): Response
    {
        $departments = Department::active()->get(['id', 'name', 'code']);

        return Inertia::render('Hris/Positions/Create', [
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created position.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:hris_positions,code',
            'department_id' => 'required|exists:hris_departments,id',
            'description' => 'nullable|string',
            'level' => 'required|string|max:50',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'requirements' => 'nullable|array',
            'responsibilities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        Position::create($validated);

        return redirect()->route('hris.positions.index')
            ->with('success', 'Position created successfully!');
    }

    /**
     * Display the specified position.
     */
    public function show(Position $position): Response
    {
        $position->load([
            'department',
            'employees.department',
            'employees' => function ($query) {
                $query->active()->latest();
            }
        ]);

        $stats = [
            'total_employees' => $position->employees->count(),
            'active_employees' => $position->employees->where('is_active', true)->count(),
            'average_salary' => $position->employees->avg('salary'),
            'salary_range' => [
                'min' => $position->min_salary,
                'max' => $position->max_salary,
            ],
        ];

        return Inertia::render('Hris/Positions/Show', [
            'position' => $position,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position): Response
    {
        $departments = Department::active()->get(['id', 'name', 'code']);

        return Inertia::render('Hris/Positions/Edit', [
            'position' => $position,
            'departments' => $departments,
        ]);
    }

    /**
     * Update the specified position.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:hris_positions,code,' . $position->id,
            'department_id' => 'required|exists:hris_departments,id',
            'description' => 'nullable|string',
            'level' => 'required|string|max:50',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'requirements' => 'nullable|array',
            'responsibilities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return redirect()->route('hris.positions.index')
            ->with('success', 'Position updated successfully!');
    }

    /**
     * Remove the specified position.
     */
    public function destroy(Position $position)
    {
        // Check if position has employees
        if ($position->employees()->count() > 0) {
            return redirect()->route('hris.positions.index')
                ->with('error', 'Cannot delete position with active employees!');
        }

        $position->delete();

        return redirect()->route('hris.positions.index')
            ->with('success', 'Position deleted successfully!');
    }

    /**
     * Get positions by department
     */
    public function getByDepartment(Department $department)
    {
        $positions = $department->positions()
            ->active()
            ->get(['id', 'title', 'code', 'level']);

        return response()->json($positions);
    }

    /**
     * Get position statistics
     */
    public function getStats(Position $position)
    {
        $stats = [
            'total_employees' => $position->employees()->count(),
            'active_employees' => $position->employees()->active()->count(),
            'average_salary' => $position->employees()->avg('salary'),
            'salary_distribution' => $position->employees()
                ->selectRaw('
                    COUNT(*) as total,
                    AVG(salary) as average,
                    MIN(salary) as minimum,
                    MAX(salary) as maximum
                ')
                ->first(),
        ];

        return response()->json($stats);
    }
}