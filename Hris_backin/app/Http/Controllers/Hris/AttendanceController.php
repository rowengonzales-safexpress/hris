<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\Attendance;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): Response
    {
        $query = Attendance::with(['employee.department'])
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
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->where('date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->where('date', '<=', $dateTo);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            });

        $attendances = $query->latest('date')->latest('clock_in')
            ->paginate($request->input('per_page', 15));
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $departments = Department::active()->get(['id', 'name']);
        
        // Calculate attendance statistics for today
        $today = Carbon::today();
        $totalEmployees = Employee::active()->count();
        $presentToday = Attendance::whereDate('date', $today)
            ->where('status', 'present')
            ->count();
        $lateArrivals = Attendance::whereDate('date', $today)
            ->where('status', 'late')
            ->count();
        $absentToday = $totalEmployees - $presentToday - $lateArrivals;
        $attendanceRate = $totalEmployees > 0 ? round(($presentToday + $lateArrivals) / $totalEmployees * 100, 1) : 0;
        
        $stats = [
            'present_today' => $presentToday,
            'late_arrivals' => $lateArrivals,
            'absent_today' => $absentToday,
            'attendance_rate' => $attendanceRate,
        ];
        
        return Inertia::render('Hris/Attendance/Index', [
            'attendanceRecords' => $attendances,
            'employees' => $employees,
            'departments' => $departments,
            'stats' => $stats,
            'filters' => $request->only(['search', 'employee_id', 'department_id', 'date_from', 'date_to', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create(): Response
    {
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Attendance/Create', [
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'status' => 'required|in:Present,Absent,Late,Half Day,Holiday,Weekend',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate attendance record
        $existingRecord = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->first();

        if ($existingRecord) {
            return back()->withErrors(['message' => 'Attendance record already exists for this employee on this date.']);
        }

        Attendance::create($request->all());

        return redirect()->route('hris.attendance.index')
            ->with('success', 'Attendance record created successfully!');
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance): Response
    {
        $attendance->load(['employee.department']);

        return Inertia::render('Hris/Attendance/Show', [
            'attendance' => $attendance,
        ]);
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance): Response
    {
        $attendance->load(['employee.department']);
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);

        return Inertia::render('Hris/Attendance/Edit', [
            'attendance' => $attendance,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'status' => 'required|in:Present,Absent,Late,Half Day,Holiday,Weekend',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate attendance record (excluding current record)
        $existingRecord = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('id', '!=', $attendance->id)
            ->first();

        if ($existingRecord) {
            return back()->withErrors(['message' => 'Attendance record already exists for this employee on this date.']);
        }

        $attendance->update($request->all());

        return redirect()->route('hris.attendance.index')
            ->with('success', 'Attendance record updated successfully!');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('hris.attendance.index')
            ->with('success', 'Attendance record deleted successfully!');
    }

    /**
     * Clock in for an employee
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
        ]);

        $today = Carbon::today();
        $now = Carbon::now();

        // Check if already clocked in today
        $existingRecord = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $today)
            ->first();

        if ($existingRecord) {
            return response()->json(['message' => 'Already clocked in today'], 422);
        }

        // Determine status based on clock in time
        $standardClockIn = Carbon::createFromTime(9, 0, 0); // 9:00 AM
        $status = $now->gt($standardClockIn->addMinutes(15)) ? 'Late' : 'Present';

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $today,
            'clock_in' => $now->format('H:i:s'),
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Clocked in successfully',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Clock out for an employee
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
        ]);

        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No clock in record found for today'], 422);
        }

        if ($attendance->clock_out) {
            return response()->json(['message' => 'Already clocked out today'], 422);
        }

        $attendance->update([
            'clock_out' => $now->format('H:i:s'),
        ]);

        return response()->json([
            'message' => 'Clocked out successfully',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function getStats(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $stats = [
            'total_records' => Attendance::whereBetween('date', [$startDate, $endDate])->count(),
            'present_count' => Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'Present')->count(),
            'late_count' => Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'Late')->count(),
            'absent_count' => Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'Absent')->count(),
            'average_hours' => Attendance::whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('clock_out')
                ->get()
                ->avg(function ($attendance) {
                    return $attendance->getTotalHours();
                }),
            'by_department' => Attendance::with(['employee.department'])
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('employee.department.name')
                ->map(function ($group) {
                    return [
                        'total' => $group->count(),
                        'present' => $group->where('status', 'Present')->count(),
                        'late' => $group->where('status', 'Late')->count(),
                        'absent' => $group->where('status', 'Absent')->count(),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Get monthly attendance report
     */
    public function monthlyReport(Request $request): Response
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $attendances = Attendance::with(['employee.department'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get()
            ->groupBy('employee_id');

        $employees = Employee::active()->with('department')->get();

        return Inertia::render('Hris/Attendance/MonthlyReport', [
            'attendances' => $attendances,
            'employees' => $employees,
            'month' => $month,
            'year' => $year,
        ]);
    }
}