<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\LeaveType;
use App\Models\Hris\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types.
     */
    public function index(Request $request): Response
    {
        $query = LeaveType::withCount('leaveRequests')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('is_active', $status === 'active');
            });

        $leaveTypes = $query->latest()->paginate($request->input('per_page', 15));

        return Inertia::render('Hris/LeaveTypes/Index', [
            'leaveTypes' => $leaveTypes,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new leave type.
     */
    public function create(): Response
    {
        return Inertia::render('Hris/LeaveTypes/Create');
    }

    /**
     * Store a newly created leave type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hris_leave_types,name',
            'code' => 'required|string|max:10|unique:hris_leave_types,code',
            'description' => 'nullable|string',
            'days_per_year' => 'required|integer|min:0|max:365',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'requires_approval' => 'boolean',
            'requires_medical_certificate' => 'boolean',
            'is_paid' => 'boolean',
            'carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|integer|min:0',
            'gender_specific' => 'nullable|in:male,female',
            'min_service_months' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        LeaveType::create($validated);

        return redirect()->route('hris.leave-types.index')
            ->with('success', 'Leave type created successfully!');
    }

    /**
     * Display the specified leave type.
     */
    public function show(LeaveType $leaveType): Response
    {
        $leaveType->load([
            'leaveRequests.employee',
            'leaveBalances.employee'
        ]);

        $stats = [
            'total_requests' => $leaveType->leaveRequests->count(),
            'pending_requests' => $leaveType->leaveRequests->where('status', 'Pending')->count(),
            'approved_requests' => $leaveType->leaveRequests->where('status', 'Approved')->count(),
            'rejected_requests' => $leaveType->leaveRequests->where('status', 'Rejected')->count(),
            'total_days_used' => $leaveType->leaveRequests->where('status', 'Approved')->sum('days_requested'),
            'employees_with_balance' => $leaveType->leaveBalances->count(),
        ];

        return Inertia::render('Hris/LeaveTypes/Show', [
            'leaveType' => $leaveType,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified leave type.
     */
    public function edit(LeaveType $leaveType): Response
    {
        return Inertia::render('Hris/LeaveTypes/Edit', [
            'leaveType' => $leaveType,
        ]);
    }

    /**
     * Update the specified leave type.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hris_leave_types,name,' . $leaveType->id,
            'code' => 'required|string|max:10|unique:hris_leave_types,code,' . $leaveType->id,
            'description' => 'nullable|string',
            'days_per_year' => 'required|integer|min:0|max:365',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'requires_approval' => 'boolean',
            'requires_medical_certificate' => 'boolean',
            'is_paid' => 'boolean',
            'carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|integer|min:0',
            'gender_specific' => 'nullable|in:male,female',
            'min_service_months' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $leaveType->update($validated);

        return redirect()->route('hris.leave-types.index')
            ->with('success', 'Leave type updated successfully!');
    }

    /**
     * Remove the specified leave type.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Check if leave type has active requests or balances
        if ($leaveType->leaveRequests()->count() > 0 || $leaveType->leaveBalances()->count() > 0) {
            return redirect()->route('hris.leave-types.index')
                ->with('error', 'Cannot delete leave type with existing requests or balances!');
        }

        $leaveType->delete();

        return redirect()->route('hris.leave-types.index')
            ->with('success', 'Leave type deleted successfully!');
    }

    /**
     * Get leave type statistics
     */
    public function getStats(LeaveType $leaveType)
    {
        $currentYear = date('Y');
        
        $stats = [
            'total_requests_this_year' => $leaveType->leaveRequests()
                ->whereYear('start_date', $currentYear)
                ->count(),
            'approved_days_this_year' => $leaveType->leaveRequests()
                ->where('status', 'Approved')
                ->whereYear('start_date', $currentYear)
                ->sum('days_requested'),
            'pending_requests' => $leaveType->leaveRequests()
                ->where('status', 'Pending')
                ->count(),
            'average_days_per_request' => $leaveType->leaveRequests()
                ->where('status', 'Approved')
                ->avg('days_requested'),
            'most_common_months' => $leaveType->leaveRequests()
                ->where('status', 'Approved')
                ->selectRaw('MONTH(start_date) as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('count', 'desc')
                ->limit(3)
                ->get(),
        ];

        return response()->json($stats);
    }
}