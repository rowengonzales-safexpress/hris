<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\PerformanceReview;
use App\Models\Hris\PerformanceGoal;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Display a listing of performance reviews.
     */
    public function reviews(Request $request): Response
    {
        $query = PerformanceReview::with(['employee.department', 'reviewer', 'approver'])
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
            ->when($request->review_type, function ($query, $reviewType) {
                return $query->where('review_type', $reviewType);
            })
            ->when($request->year, function ($query, $year) {
                return $query->whereYear('review_period_start', $year);
            });

        $reviews = $query->latest('review_period_start')->paginate($request->input('per_page', 15));
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $departments = Department::active()->get(['id', 'name']);

        return Inertia::render('Hris/Performance/Reviews/Index', [
            'reviews' => $reviews,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'employee_id', 'department_id', 'status', 'review_type', 'year']),
        ]);
    }

    /**
     * Show the form for creating a new performance review.
     */
    public function createReview(): Response
    {
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        $reviewers = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Performance/Reviews/Create', [
            'employees' => $employees,
            'reviewers' => $reviewers,
        ]);
    }

    /**
     * Store a newly created performance review.
     */
    public function storeReview(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'reviewer_id' => 'required|exists:hris_employees,id',
            'review_type' => 'required|in:Annual,Mid-Year,Quarterly,Probation,Project-Based',
            'review_period_start' => 'required|date',
            'review_period_end' => 'required|date|after_or_equal:review_period_start',
            'due_date' => 'required|date|after_or_equal:today',
            'goals' => 'nullable|array',
            'competencies' => 'nullable|array',
            'kpis' => 'nullable|array',
            'comments' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate review
        $exists = PerformanceReview::where('employee_id', $request->employee_id)
            ->where('review_type', $request->review_type)
            ->where('review_period_start', $request->review_period_start)
            ->where('review_period_end', $request->review_period_end)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'review_period_start' => 'Performance review already exists for this employee and period.'
            ]);
        }

        $review = PerformanceReview::create([
            'employee_id' => $request->employee_id,
            'reviewer_id' => $request->reviewer_id,
            'review_type' => $request->review_type,
            'review_period_start' => $request->review_period_start,
            'review_period_end' => $request->review_period_end,
            'due_date' => $request->due_date,
            'goals' => $request->goals ?? [],
            'competencies' => $request->competencies ?? [],
            'kpis' => $request->kpis ?? [],
            'status' => 'Draft',
            'created_by' => auth()->id(),
            'comments' => $request->comments,
        ]);

        return redirect()->route('hris.performance.reviews')
            ->with('success', 'Performance review created successfully!');
    }

    /**
     * Display the specified performance review.
     */
    public function showReview(PerformanceReview $review): Response
    {
        $review->load(['employee.department', 'reviewer', 'approver']);

        return Inertia::render('Hris/Performance/Reviews/Show', [
            'review' => $review,
        ]);
    }

    /**
     * Show the form for editing the specified performance review.
     */
    public function editReview(PerformanceReview $review): Response
    {
        if (in_array($review->status, ['Submitted', 'Approved'])) {
            return back()->withErrors(['message' => 'Cannot edit submitted or approved reviews.']);
        }

        $review->load(['employee.department', 'reviewer']);
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        $reviewers = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Performance/Reviews/Edit', [
            'review' => $review,
            'employees' => $employees,
            'reviewers' => $reviewers,
        ]);
    }

    /**
     * Update the specified performance review.
     */
    public function updateReview(Request $request, PerformanceReview $review)
    {
        if (in_array($review->status, ['Submitted', 'Approved'])) {
            return back()->withErrors(['message' => 'Cannot update submitted or approved reviews.']);
        }

        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'reviewer_id' => 'required|exists:hris_employees,id',
            'review_type' => 'required|in:Annual,Mid-Year,Quarterly,Probation,Project-Based',
            'review_period_start' => 'required|date',
            'review_period_end' => 'required|date|after_or_equal:review_period_start',
            'due_date' => 'required|date',
            'goals' => 'nullable|array',
            'competencies' => 'nullable|array',
            'kpis' => 'nullable|array',
            'goal_rating' => 'nullable|numeric|min:1|max:5',
            'competency_rating' => 'nullable|numeric|min:1|max:5',
            'kpi_rating' => 'nullable|numeric|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
            'reviewer_comments' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'employee_id' => $request->employee_id,
            'reviewer_id' => $request->reviewer_id,
            'review_type' => $request->review_type,
            'review_period_start' => $request->review_period_start,
            'review_period_end' => $request->review_period_end,
            'due_date' => $request->due_date,
            'goals' => $request->goals ?? [],
            'competencies' => $request->competencies ?? [],
            'kpis' => $request->kpis ?? [],
            'goal_rating' => $request->goal_rating,
            'competency_rating' => $request->competency_rating,
            'kpi_rating' => $request->kpi_rating,
            'comments' => $request->comments,
            'reviewer_comments' => $request->reviewer_comments,
        ]);

        // Recalculate overall rating if ratings are provided
        if ($request->goal_rating || $request->competency_rating || $request->kpi_rating) {
            $review->calculateOverallRating();
        }

        return redirect()->route('hris.performance.reviews')
            ->with('success', 'Performance review updated successfully!');
    }

    /**
     * Remove the specified performance review.
     */
    public function destroyReview(PerformanceReview $review)
    {
        if (in_array($review->status, ['Submitted', 'Approved'])) {
            return back()->withErrors(['message' => 'Cannot delete submitted or approved reviews.']);
        }

        $review->delete();

        return redirect()->route('hris.performance.reviews')
            ->with('success', 'Performance review deleted successfully!');
    }

    /**
     * Submit a performance review.
     */
    public function submitReview(PerformanceReview $review)
    {
        if ($review->status !== 'Draft') {
            return response()->json(['message' => 'Only draft reviews can be submitted'], 422);
        }

        $review->submit();

        return response()->json(['message' => 'Performance review submitted successfully']);
    }

    /**
     * Approve a performance review.
     */
    public function approveReview(Request $request, PerformanceReview $review)
    {
        if ($review->status !== 'Submitted') {
            return response()->json(['message' => 'Only submitted reviews can be approved'], 422);
        }

        $review->approve(auth()->id(), $request->input('approval_comments'));

        return response()->json(['message' => 'Performance review approved successfully']);
    }

    /**
     * Display a listing of performance goals.
     */
    public function goals(Request $request): Response
    {
        $query = PerformanceGoal::with(['employee.department', 'setter'])
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                            ->orWhereHas('employee', function ($q) use ($search) {
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
            ->when($request->priority, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->when($request->category, function ($query, $category) {
                return $query->where('category', $category);
            });

        $goals = $query->latest('created_at')->paginate($request->input('per_page', 15));
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $departments = Department::active()->get(['id', 'name']);

        return Inertia::render('Hris/Performance/Goals/Index', [
            'goals' => $goals,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'employee_id', 'department_id', 'status', 'priority', 'category']),
        ]);
    }

    /**
     * Show the form for creating a new performance goal.
     */
    public function createGoal(): Response
    {
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Performance/Goals/Create', [
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created performance goal.
     */
    public function storeGoal(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:1000',
            'category' => 'required|in:Performance,Development,Behavioral,Project,Sales,Quality',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'target_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'target_date' => 'required|date|after_or_equal:start_date',
            'weight' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $goal = PerformanceGoal::create([
            'employee_id' => $request->employee_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value ?? 0,
            'unit' => $request->unit,
            'start_date' => $request->start_date,
            'target_date' => $request->target_date,
            'weight' => $request->weight ?? 0,
            'status' => 'Active',
            'set_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('hris.performance.goals')
            ->with('success', 'Performance goal created successfully!');
    }

    /**
     * Display the specified performance goal.
     */
    public function showGoal(PerformanceGoal $goal): Response
    {
        $goal->load(['employee.department', 'setter']);

        return Inertia::render('Hris/Performance/Goals/Show', [
            'goal' => $goal,
        ]);
    }

    /**
     * Show the form for editing the specified performance goal.
     */
    public function editGoal(PerformanceGoal $goal): Response
    {
        if (in_array($goal->status, ['Completed', 'Cancelled'])) {
            return back()->withErrors(['message' => 'Cannot edit completed or cancelled goals.']);
        }

        $goal->load(['employee.department', 'setter']);
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Performance/Goals/Edit', [
            'goal' => $goal,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified performance goal.
     */
    public function updateGoal(Request $request, PerformanceGoal $goal)
    {
        if (in_array($goal->status, ['Completed', 'Cancelled'])) {
            return back()->withErrors(['message' => 'Cannot update completed or cancelled goals.']);
        }

        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:1000',
            'category' => 'required|in:Performance,Development,Behavioral,Project,Sales,Quality',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'target_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'target_date' => 'required|date|after_or_equal:start_date',
            'weight' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $goal->update([
            'employee_id' => $request->employee_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value ?? 0,
            'unit' => $request->unit,
            'start_date' => $request->start_date,
            'target_date' => $request->target_date,
            'weight' => $request->weight ?? 0,
            'notes' => $request->notes,
        ]);

        return redirect()->route('hris.performance.goals')
            ->with('success', 'Performance goal updated successfully!');
    }

    /**
     * Remove the specified performance goal.
     */
    public function destroyGoal(PerformanceGoal $goal)
    {
        if ($goal->status === 'Completed') {
            return back()->withErrors(['message' => 'Cannot delete completed goals.']);
        }

        $goal->delete();

        return redirect()->route('hris.performance.goals')
            ->with('success', 'Performance goal deleted successfully!');
    }

    /**
     * Update goal progress.
     */
    public function updateProgress(Request $request, PerformanceGoal $goal)
    {
        if ($goal->status !== 'Active') {
            return response()->json(['message' => 'Only active goals can have progress updated'], 422);
        }

        $request->validate([
            'current_value' => 'required|numeric|min:0',
            'progress_notes' => 'nullable|string|max:500',
        ]);

        $goal->updateProgress($request->current_value, $request->progress_notes);

        return response()->json(['message' => 'Goal progress updated successfully']);
    }

    /**
     * Complete a performance goal.
     */
    public function completeGoal(Request $request, PerformanceGoal $goal)
    {
        if ($goal->status !== 'Active') {
            return response()->json(['message' => 'Only active goals can be completed'], 422);
        }

        $goal->complete($request->input('completion_notes'));

        return response()->json(['message' => 'Goal completed successfully']);
    }

    /**
     * Cancel a performance goal.
     */
    public function cancelGoal(Request $request, PerformanceGoal $goal)
    {
        if ($goal->status === 'Completed') {
            return response()->json(['message' => 'Cannot cancel completed goals'], 422);
        }

        $goal->cancel($request->input('cancellation_reason', 'No reason provided'));

        return response()->json(['message' => 'Goal cancelled successfully']);
    }

    /**
     * Get performance statistics.
     */
    public function getStats(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $reviewStats = [
            'total_reviews' => PerformanceReview::whereYear('review_period_start', $year)->count(),
            'draft_reviews' => PerformanceReview::where('status', 'Draft')->whereYear('review_period_start', $year)->count(),
            'submitted_reviews' => PerformanceReview::where('status', 'Submitted')->whereYear('review_period_start', $year)->count(),
            'approved_reviews' => PerformanceReview::where('status', 'Approved')->whereYear('review_period_start', $year)->count(),
            'overdue_reviews' => PerformanceReview::overdue()->whereYear('review_period_start', $year)->count(),
            'average_rating' => PerformanceReview::whereYear('review_period_start', $year)
                ->whereNotNull('overall_rating')
                ->avg('overall_rating'),
            'by_type' => PerformanceReview::whereYear('review_period_start', $year)
                ->selectRaw('review_type, COUNT(*) as count, AVG(overall_rating) as avg_rating')
                ->groupBy('review_type')
                ->get(),
        ];

        $goalStats = [
            'total_goals' => PerformanceGoal::whereYear('start_date', $year)->count(),
            'active_goals' => PerformanceGoal::active()->whereYear('start_date', $year)->count(),
            'completed_goals' => PerformanceGoal::completed()->whereYear('start_date', $year)->count(),
            'overdue_goals' => PerformanceGoal::overdue()->whereYear('start_date', $year)->count(),
            'at_risk_goals' => PerformanceGoal::atRisk()->whereYear('start_date', $year)->count(),
            'average_progress' => PerformanceGoal::active()->whereYear('start_date', $year)
                ->get()
                ->avg('progress_percentage'),
            'by_category' => PerformanceGoal::whereYear('start_date', $year)
                ->selectRaw('category, COUNT(*) as count, AVG(CASE WHEN target_value > 0 THEN (current_value / target_value) * 100 ELSE 0 END) as avg_progress')
                ->groupBy('category')
                ->get(),
            'by_priority' => PerformanceGoal::whereYear('start_date', $year)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get(),
        ];

        return response()->json([
            'reviews' => $reviewStats,
            'goals' => $goalStats,
        ]);
    }
}