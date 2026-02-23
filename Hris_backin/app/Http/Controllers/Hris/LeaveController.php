<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\LeaveRequest;
use App\Models\Hris\LeaveBalance;
use App\Models\Hris\LeaveType;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of leave requests.
     */
    public function index(Request $request): Response
    {
        $query = LeaveRequest::with(['employee.department', 'leaveType', 'approver'])
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
            ->when($request->leave_type_id, function ($query, $leaveTypeId) {
                return $query->where('leave_type_id', $leaveTypeId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->where('start_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->where('end_date', '<=', $dateTo);
            });

        $leaveRequests = $query->latest()->paginate($request->input('per_page', 15));
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $leaveTypes = LeaveType::active()->get(['id', 'name', 'code']);
        
        return Inertia::render('Hris/Leave/Index', [
            'leaveRequests' => $leaveRequests,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'filters' => $request->only(['search', 'employee_id', 'leave_type_id', 'status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create(): Response
    {
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $leaveTypes = LeaveType::active()->get();
        
        return Inertia::render('Hris/Leave/Create', [
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'leave_type_id' => 'required|exists:hris_leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $employee = Employee::findOrFail($request->employee_id);

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Check if exceeds maximum consecutive days
        if ($totalDays > $leaveType->max_consecutive_days) {
            return back()->withErrors([
                'end_date' => "Leave cannot exceed {$leaveType->max_consecutive_days} consecutive days for this leave type."
            ]);
        }

        // Check leave balance
        $leaveBalance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $startDate->year)
            ->first();

        if ($leaveBalance && !$leaveBalance->hasSufficientBalance($totalDays)) {
            return back()->withErrors([
                'total_days' => 'Insufficient leave balance for this request.'
            ]);
        }

        // Check for overlapping leave requests
        $overlapping = LeaveRequest::where('employee_id', $request->employee_id)
            ->where('status', '!=', 'Rejected')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->exists();

        if ($overlapping) {
            return back()->withErrors([
                'start_date' => 'Leave request overlaps with existing leave request.'
            ]);
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
            'status' => $leaveType->requires_approval ? 'Pending' : 'Approved',
            'applied_date' => now(),
        ]);

        // If auto-approved, update leave balance
        if (!$leaveType->requires_approval && $leaveBalance) {
            $leaveBalance->addPendingDays($totalDays);
            $leaveBalance->useDays($totalDays);
        } elseif ($leaveBalance) {
            $leaveBalance->addPendingDays($totalDays);
        }

        return redirect()->route('hris.leave.index')
            ->with('success', 'Leave request submitted successfully!');
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leave): Response
    {
        $leave->load(['employee.department', 'leaveType', 'approver']);

        return Inertia::render('Hris/Leave/Show', [
            'leaveRequest' => $leave,
        ]);
    }

    /**
     * Show the form for editing the specified leave request.
     */
    public function edit(LeaveRequest $leave): Response
    {
        if ($leave->status !== 'Pending') {
            return back()->withErrors(['message' => 'Only pending leave requests can be edited.']);
        }

        $leave->load(['employee.department', 'leaveType']);
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $leaveTypes = LeaveType::active()->get();

        return Inertia::render('Hris/Leave/Edit', [
            'leaveRequest' => $leave,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Update the specified leave request.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'Pending') {
            return back()->withErrors(['message' => 'Only pending leave requests can be updated.']);
        }

        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'leave_type_id' => 'required|exists:hris_leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        // Restore previous pending days
        $oldLeaveBalance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', Carbon::parse($leave->start_date)->year)
            ->first();

        if ($oldLeaveBalance) {
            $oldLeaveBalance->removePendingDays($leave->total_days);
        }

        // Recalculate and validate new request
        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Check leave balance for new request
        $newLeaveBalance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $startDate->year)
            ->first();

        if ($newLeaveBalance && !$newLeaveBalance->hasSufficientBalance($totalDays)) {
            // Restore old pending days
            if ($oldLeaveBalance) {
                $oldLeaveBalance->addPendingDays($leave->total_days);
            }
            return back()->withErrors([
                'total_days' => 'Insufficient leave balance for this request.'
            ]);
        }

        $leave->update([
            'employee_id' => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
        ]);

        // Add new pending days
        if ($newLeaveBalance) {
            $newLeaveBalance->addPendingDays($totalDays);
        }

        return redirect()->route('hris.leave.index')
            ->with('success', 'Leave request updated successfully!');
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy(LeaveRequest $leave)
    {
        if ($leave->status === 'Approved') {
            return back()->withErrors(['message' => 'Cannot delete approved leave requests.']);
        }

        // Restore pending days if applicable
        if ($leave->status === 'Pending') {
            $leaveBalance = LeaveBalance::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', Carbon::parse($leave->start_date)->year)
                ->first();

            if ($leaveBalance) {
                $leaveBalance->removePendingDays($leave->total_days);
            }
        }

        $leave->delete();

        return redirect()->route('hris.leave.index')
            ->with('success', 'Leave request deleted successfully!');
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'Pending') {
            return response()->json(['message' => 'Only pending requests can be approved'], 422);
        }

        $leave->approve(auth()->id(), $request->input('approval_comments'));

        return response()->json(['message' => 'Leave request approved successfully']);
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== 'Pending') {
            return response()->json(['message' => 'Only pending requests can be rejected'], 422);
        }

        $leave->reject(auth()->id(), $request->input('rejection_reason', 'No reason provided'));

        return response()->json(['message' => 'Leave request rejected successfully']);
    }

    /**
     * Cancel a leave request.
     */
    public function cancel(LeaveRequest $leave)
    {
        if (!in_array($leave->status, ['Pending', 'Approved'])) {
            return response()->json(['message' => 'Only pending or approved requests can be cancelled'], 422);
        }

        $leave->cancel();

        return response()->json(['message' => 'Leave request cancelled successfully']);
    }

    /**
     * Display leave balances.
     */
    public function balances(Request $request): Response
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $query = LeaveBalance::with(['employee.department', 'leaveType'])
            ->where('year', $year)
            ->when($request->employee_id, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            });

        $leaveBalances = $query->get();
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $departments = Department::active()->get(['id', 'name']);
        $leaveTypes = LeaveType::active()->get(['id', 'name', 'code']);

        return Inertia::render('Hris/Leave/Balances', [
            'leaveBalances' => $leaveBalances,
            'employees' => $employees,
            'departments' => $departments,
            'leaveTypes' => $leaveTypes,
            'year' => $year,
            'filters' => $request->only(['employee_id', 'department_id']),
        ]);
    }

    /**
     * Get leave statistics.
     */
    public function getStats(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $stats = [
            'total_requests' => LeaveRequest::whereYear('start_date', $year)->count(),
            'pending_requests' => LeaveRequest::pending()->whereYear('start_date', $year)->count(),
            'approved_requests' => LeaveRequest::approved()->whereYear('start_date', $year)->count(),
            'rejected_requests' => LeaveRequest::rejected()->whereYear('start_date', $year)->count(),
            'total_days_taken' => LeaveRequest::approved()->whereYear('start_date', $year)->sum('total_days'),
            'by_leave_type' => LeaveRequest::with('leaveType')
                ->whereYear('start_date', $year)
                ->get()
                ->groupBy('leaveType.name')
                ->map(function ($group) {
                    return [
                        'total_requests' => $group->count(),
                        'approved_requests' => $group->where('status', 'Approved')->count(),
                        'total_days' => $group->where('status', 'Approved')->sum('total_days'),
                    ];
                }),
            'by_department' => LeaveRequest::with(['employee.department'])
                ->whereYear('start_date', $year)
                ->get()
                ->groupBy('employee.department.name')
                ->map(function ($group) {
                    return [
                        'total_requests' => $group->count(),
                        'approved_requests' => $group->where('status', 'Approved')->count(),
                        'total_days' => $group->where('status', 'Approved')->sum('total_days'),
                    ];
                }),
        ];

        return response()->json($stats);
    }
}