<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\OnboardingChecklist;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    /**
     * Display a listing of onboarding checklists.
     */
    public function index(Request $request): Response
    {
        $query = OnboardingChecklist::with(['employee.department', 'assignee'])
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($request->employee_id, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->assignee_id, function ($query, $assigneeId) {
                return $query->where('assigned_to', $assigneeId);
            })
            ->when($request->overdue, function ($query, $overdue) {
                if ($overdue === 'true') {
                    return $query->where('due_date', '<', now())
                                 ->where('status', '!=', 'Completed');
                }
            });

        $checklists = $query->latest('created_at')->paginate($request->input('per_page', 15));
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $departments = Department::active()->get(['id', 'name']);

        return Inertia::render('Hris/Onboarding/Index', [
            'checklists' => $checklists,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'employee_id', 'department_id', 'status', 'assignee_id', 'overdue']),
        ]);
    }

    /**
     * Show the form for creating a new onboarding checklist.
     */
    public function create(): Response
    {
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        $assignees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        // Default checklist items based on common onboarding practices
        $defaultItems = [
            [
                'title' => 'Complete employment paperwork',
                'description' => 'Fill out tax forms, emergency contacts, and other required documents',
                'category' => 'Documentation',
                'is_mandatory' => true,
                'estimated_hours' => 1.0,
                'order' => 1,
            ],
            [
                'title' => 'IT setup and account creation',
                'description' => 'Create email account, provide laptop/equipment, set up system access',
                'category' => 'IT Setup',
                'is_mandatory' => true,
                'estimated_hours' => 2.0,
                'order' => 2,
            ],
            [
                'title' => 'Office tour and workspace assignment',
                'description' => 'Show office facilities, assign desk/workspace, introduce to facilities',
                'category' => 'Orientation',
                'is_mandatory' => true,
                'estimated_hours' => 0.5,
                'order' => 3,
            ],
            [
                'title' => 'Meet team members and key stakeholders',
                'description' => 'Introduce to immediate team, department heads, and key collaborators',
                'category' => 'Introductions',
                'is_mandatory' => true,
                'estimated_hours' => 1.0,
                'order' => 4,
            ],
            [
                'title' => 'Review job description and expectations',
                'description' => 'Discuss role responsibilities, performance expectations, and goals',
                'category' => 'Role Clarification',
                'is_mandatory' => true,
                'estimated_hours' => 1.0,
                'order' => 5,
            ],
            [
                'title' => 'Company policies and procedures training',
                'description' => 'Review employee handbook, code of conduct, and company policies',
                'category' => 'Training',
                'is_mandatory' => true,
                'estimated_hours' => 2.0,
                'order' => 6,
            ],
            [
                'title' => 'Safety and security briefing',
                'description' => 'Emergency procedures, security protocols, and safety guidelines',
                'category' => 'Safety',
                'is_mandatory' => true,
                'estimated_hours' => 0.5,
                'order' => 7,
            ],
            [
                'title' => 'Benefits enrollment',
                'description' => 'Health insurance, retirement plans, and other benefit selections',
                'category' => 'Benefits',
                'is_mandatory' => false,
                'estimated_hours' => 1.0,
                'order' => 8,
            ],
        ];

        return Inertia::render('Hris/Onboarding/Create', [
            'employees' => $employees,
            'assignees' => $assignees,
            'defaultItems' => $defaultItems,
        ]);
    }

    /**
     * Store a newly created onboarding checklist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'assigned_to' => 'required|exists:hris_employees,id',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'checklist_items' => 'required|array|min:1',
            'checklist_items.*.title' => 'required|string|max:200',
            'checklist_items.*.description' => 'nullable|string|max:500',
            'checklist_items.*.category' => 'required|string|max:50',
            'checklist_items.*.is_mandatory' => 'boolean',
            'checklist_items.*.estimated_hours' => 'nullable|numeric|min:0',
            'checklist_items.*.order' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for existing checklist
        $exists = OnboardingChecklist::where('employee_id', $request->employee_id)
            ->where('status', '!=', 'Completed')
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'employee_id' => 'Active onboarding checklist already exists for this employee.'
            ]);
        }

        $checklist = OnboardingChecklist::create([
            'employee_id' => $request->employee_id,
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'checklist_items' => $request->checklist_items,
            'status' => 'In Progress',
            'completion_percentage' => 0,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('hris.onboarding.index')
            ->with('success', 'Onboarding checklist created successfully!');
    }

    /**
     * Display the specified onboarding checklist.
     */
    public function show(OnboardingChecklist $onboarding): Response
    {
        $onboarding->load(['employee.department', 'assignee']);

        return Inertia::render('Hris/Onboarding/Show', [
            'checklist' => $onboarding,
        ]);
    }

    /**
     * Show the form for editing the specified onboarding checklist.
     */
    public function edit(OnboardingChecklist $onboarding): Response
    {
        if ($onboarding->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot edit completed onboarding checklists.']);
        }

        $onboarding->load(['employee.department', 'assignee']);
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        $assignees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Onboarding/Edit', [
            'checklist' => $onboarding,
            'employees' => $employees,
            'assignees' => $assignees,
        ]);
    }

    /**
     * Update the specified onboarding checklist.
     */
    public function update(Request $request, OnboardingChecklist $onboarding)
    {
        if ($onboarding->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot update completed onboarding checklists.']);
        }

        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'assigned_to' => 'required|exists:hris_employees,id',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'checklist_items' => 'required|array|min:1',
            'checklist_items.*.title' => 'required|string|max:200',
            'checklist_items.*.description' => 'nullable|string|max:500',
            'checklist_items.*.category' => 'required|string|max:50',
            'checklist_items.*.is_mandatory' => 'boolean',
            'checklist_items.*.estimated_hours' => 'nullable|numeric|min:0',
            'checklist_items.*.order' => 'required|integer|min:1',
            'checklist_items.*.is_completed' => 'boolean',
            'checklist_items.*.completed_date' => 'nullable|date',
            'checklist_items.*.completed_by' => 'nullable|exists:hris_employees,id',
            'checklist_items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $onboarding->update([
            'employee_id' => $request->employee_id,
            'assigned_to' => $request->assigned_to,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'checklist_items' => $request->checklist_items,
            'notes' => $request->notes,
        ]);

        // Recalculate completion percentage
        $onboarding->updateCompletion();

        return redirect()->route('hris.onboarding.index')
            ->with('success', 'Onboarding checklist updated successfully!');
    }

    /**
     * Remove the specified onboarding checklist.
     */
    public function destroy(OnboardingChecklist $onboarding)
    {
        if ($onboarding->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot delete completed onboarding checklists.']);
        }

        $onboarding->delete();

        return redirect()->route('hris.onboarding.index')
            ->with('success', 'Onboarding checklist deleted successfully!');
    }

    /**
     * Complete a checklist item.
     */
    public function completeItem(Request $request, OnboardingChecklist $onboarding)
    {
        $request->validate([
            'item_index' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $onboarding->completeItem($request->item_index, auth()->id(), $request->notes);

        return response()->json(['message' => 'Checklist item completed successfully']);
    }

    /**
     * Uncomplete a checklist item.
     */
    public function uncompleteItem(Request $request, OnboardingChecklist $onboarding)
    {
        $request->validate([
            'item_index' => 'required|integer|min:0',
        ]);

        $onboarding->uncompleteItem($request->item_index);

        return response()->json(['message' => 'Checklist item marked as incomplete']);
    }

    /**
     * Add a new item to the checklist.
     */
    public function addItem(Request $request, OnboardingChecklist $onboarding)
    {
        if ($onboarding->status === 'Completed') {
            return response()->json(['message' => 'Cannot add items to completed checklists'], 422);
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:50',
            'is_mandatory' => 'boolean',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $newItem = [
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'is_mandatory' => $request->boolean('is_mandatory'),
            'estimated_hours' => $request->estimated_hours ?? 0,
            'order' => count($onboarding->checklist_items) + 1,
            'is_completed' => false,
            'completed_date' => null,
            'completed_by' => null,
            'notes' => null,
        ];

        $onboarding->addItem($newItem);

        return response()->json(['message' => 'Item added to checklist successfully']);
    }

    /**
     * Remove an item from the checklist.
     */
    public function removeItem(Request $request, OnboardingChecklist $onboarding)
    {
        if ($onboarding->status === 'Completed') {
            return response()->json(['message' => 'Cannot remove items from completed checklists'], 422);
        }

        $request->validate([
            'item_index' => 'required|integer|min:0',
        ]);

        $onboarding->removeItem($request->item_index);

        return response()->json(['message' => 'Item removed from checklist successfully']);
    }

    /**
     * Mark entire checklist as completed.
     */
    public function complete(Request $request, OnboardingChecklist $onboarding)
    {
        if ($onboarding->status === 'Completed') {
            return response()->json(['message' => 'Checklist is already completed'], 422);
        }

        // Check if all mandatory items are completed
        $mandatoryItems = collect($onboarding->checklist_items)->where('is_mandatory', true);
        $incompleteMandatory = $mandatoryItems->where('is_completed', false);

        if ($incompleteMandatory->count() > 0) {
            return response()->json([
                'message' => 'Cannot complete checklist. All mandatory items must be completed first.',
                'incomplete_mandatory' => $incompleteMandatory->pluck('title')->toArray()
            ], 422);
        }

        $onboarding->update([
            'status' => 'Completed',
            'completed_date' => now(),
            'completion_percentage' => 100,
            'completion_notes' => $request->input('completion_notes'),
        ]);

        return response()->json(['message' => 'Onboarding checklist completed successfully']);
    }

    /**
     * Reopen a completed checklist.
     */
    public function reopen(OnboardingChecklist $onboarding)
    {
        if ($onboarding->status !== 'Completed') {
            return response()->json(['message' => 'Only completed checklists can be reopened'], 422);
        }

        $onboarding->update([
            'status' => 'In Progress',
            'completed_date' => null,
            'completion_notes' => null,
        ]);

        // Recalculate completion percentage
        $onboarding->updateCompletion();

        return response()->json(['message' => 'Onboarding checklist reopened successfully']);
    }

    /**
     * Generate bulk onboarding checklists for new employees.
     */
    public function generateBulk(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:hris_employees,id',
            'assigned_to' => 'required|exists:hris_employees,id',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'template_items' => 'required|array|min:1',
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($request->employee_ids as $employeeId) {
            // Check if checklist already exists
            $exists = OnboardingChecklist::where('employee_id', $employeeId)
                ->where('status', '!=', 'Completed')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            OnboardingChecklist::create([
                'employee_id' => $employeeId,
                'assigned_to' => $request->assigned_to,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'checklist_items' => $request->template_items,
                'status' => 'In Progress',
                'completion_percentage' => 0,
                'created_by' => auth()->id(),
            ]);

            $created++;
        }

        return response()->json([
            'message' => "Bulk onboarding generation completed. Created: {$created}, Skipped: {$skipped}",
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Get onboarding statistics.
     */
    public function getStats(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');

        $query = OnboardingChecklist::query();

        if ($month) {
            $query->whereYear('start_date', $year)->whereMonth('start_date', $month);
        } else {
            $query->whereYear('start_date', $year);
        }

        $stats = [
            'total_checklists' => (clone $query)->count(),
            'in_progress' => (clone $query)->where('status', 'In Progress')->count(),
            'completed' => (clone $query)->where('status', 'Completed')->count(),
            'overdue' => (clone $query)->where('due_date', '<', now())
                ->where('status', '!=', 'Completed')->count(),
            'due_soon' => (clone $query)->whereBetween('due_date', [now(), now()->addDays(7)])
                ->where('status', '!=', 'Completed')->count(),
            'average_completion' => (clone $query)->avg('completion_percentage'),
            'average_completion_time' => OnboardingChecklist::where('status', 'Completed')
                ->whereYear('start_date', $year)
                ->get()
                ->map(function ($checklist) {
                    return $checklist->start_date->diffInDays($checklist->completed_date);
                })
                ->avg(),
            'by_department' => (clone $query)->with(['employee.department'])
                ->get()
                ->groupBy('employee.department.name')
                ->map(function ($group) {
                    return [
                        'total' => $group->count(),
                        'completed' => $group->where('status', 'Completed')->count(),
                        'average_completion' => $group->avg('completion_percentage'),
                    ];
                }),
            'completion_trend' => OnboardingChecklist::selectRaw('MONTH(start_date) as month, COUNT(*) as total, SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed')
                ->whereYear('start_date', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get checklist templates.
     */
    public function getTemplates()
    {
        $templates = [
            'general' => [
                'name' => 'General Employee Onboarding',
                'description' => 'Standard onboarding checklist for all new employees',
                'items' => [
                    [
                        'title' => 'Complete employment paperwork',
                        'description' => 'Fill out tax forms, emergency contacts, and other required documents',
                        'category' => 'Documentation',
                        'is_mandatory' => true,
                        'estimated_hours' => 1.0,
                        'order' => 1,
                    ],
                    [
                        'title' => 'IT setup and account creation',
                        'description' => 'Create email account, provide laptop/equipment, set up system access',
                        'category' => 'IT Setup',
                        'is_mandatory' => true,
                        'estimated_hours' => 2.0,
                        'order' => 2,
                    ],
                    [
                        'title' => 'Office tour and workspace assignment',
                        'description' => 'Show office facilities, assign desk/workspace, introduce to facilities',
                        'category' => 'Orientation',
                        'is_mandatory' => true,
                        'estimated_hours' => 0.5,
                        'order' => 3,
                    ],
                    [
                        'title' => 'Meet team members and key stakeholders',
                        'description' => 'Introduce to immediate team, department heads, and key collaborators',
                        'category' => 'Introductions',
                        'is_mandatory' => true,
                        'estimated_hours' => 1.0,
                        'order' => 4,
                    ],
                    [
                        'title' => 'Review job description and expectations',
                        'description' => 'Discuss role responsibilities, performance expectations, and goals',
                        'category' => 'Role Clarification',
                        'is_mandatory' => true,
                        'estimated_hours' => 1.0,
                        'order' => 5,
                    ],
                    [
                        'title' => 'Company policies and procedures training',
                        'description' => 'Review employee handbook, code of conduct, and company policies',
                        'category' => 'Training',
                        'is_mandatory' => true,
                        'estimated_hours' => 2.0,
                        'order' => 6,
                    ],
                    [
                        'title' => 'Safety and security briefing',
                        'description' => 'Emergency procedures, security protocols, and safety guidelines',
                        'category' => 'Safety',
                        'is_mandatory' => true,
                        'estimated_hours' => 0.5,
                        'order' => 7,
                    ],
                    [
                        'title' => 'Benefits enrollment',
                        'description' => 'Health insurance, retirement plans, and other benefit selections',
                        'category' => 'Benefits',
                        'is_mandatory' => false,
                        'estimated_hours' => 1.0,
                        'order' => 8,
                    ],
                ]
            ],
            'manager' => [
                'name' => 'Manager Onboarding',
                'description' => 'Extended onboarding checklist for new managers',
                'items' => [
                    // Include all general items plus manager-specific ones
                    [
                        'title' => 'Management training program',
                        'description' => 'Complete leadership and management skills training',
                        'category' => 'Training',
                        'is_mandatory' => true,
                        'estimated_hours' => 8.0,
                        'order' => 9,
                    ],
                    [
                        'title' => 'Review team structure and responsibilities',
                        'description' => 'Understand team dynamics, individual roles, and reporting structure',
                        'category' => 'Team Management',
                        'is_mandatory' => true,
                        'estimated_hours' => 2.0,
                        'order' => 10,
                    ],
                    [
                        'title' => 'Budget and resource overview',
                        'description' => 'Review department budget, resources, and approval processes',
                        'category' => 'Financial',
                        'is_mandatory' => true,
                        'estimated_hours' => 1.5,
                        'order' => 11,
                    ],
                ]
            ],
            'remote' => [
                'name' => 'Remote Employee Onboarding',
                'description' => 'Specialized onboarding for remote workers',
                'items' => [
                    [
                        'title' => 'Remote work setup and equipment delivery',
                        'description' => 'Ensure home office setup, equipment delivery, and connectivity',
                        'category' => 'Remote Setup',
                        'is_mandatory' => true,
                        'estimated_hours' => 2.0,
                        'order' => 1,
                    ],
                    [
                        'title' => 'Virtual team introductions',
                        'description' => 'Schedule video calls with team members and stakeholders',
                        'category' => 'Introductions',
                        'is_mandatory' => true,
                        'estimated_hours' => 1.5,
                        'order' => 2,
                    ],
                    [
                        'title' => 'Remote work policies and expectations',
                        'description' => 'Review remote work guidelines, communication protocols, and expectations',
                        'category' => 'Policies',
                        'is_mandatory' => true,
                        'estimated_hours' => 1.0,
                        'order' => 3,
                    ],
                ]
            ]
        ];

        return response()->json($templates);
    }
}