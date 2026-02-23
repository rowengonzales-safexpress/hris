<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Hris\Payroll;
use App\Models\Hris\Employee;
use App\Models\Hris\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll records.
     */
    public function index(Request $request): Response
    {
        $query = Payroll::with(['employee.department', 'processor'])
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
            ->when($request->pay_period_start, function ($query, $payPeriodStart) {
                return $query->where('pay_period_start', '>=', $payPeriodStart);
            })
            ->when($request->pay_period_end, function ($query, $payPeriodEnd) {
                return $query->where('pay_period_end', '<=', $payPeriodEnd);
            });

        $payrolls = $query->latest('pay_period_start')->paginate($request->input('per_page', 15));
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id']);
        
        $departments = Department::active()->get(['id', 'name']);

        return Inertia::render('Hris/Payroll/Index', [
            'payrolls' => $payrolls,
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'employee_id', 'department_id', 'status', 'pay_period_start', 'pay_period_end']),
        ]);
    }

    /**
     * Show the form for creating a new payroll record.
     */
    public function create(): Response
    {
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id', 'basic_salary']);

        return Inertia::render('Hris/Payroll/Create', [
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created payroll record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Check for duplicate payroll record
        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('pay_period_start', $request->pay_period_start)
            ->where('pay_period_end', $request->pay_period_end)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'pay_period_start' => 'Payroll record already exists for this employee and pay period.'
            ]);
        }

        $overtimePay = ($request->overtime_hours ?? 0) * ($request->overtime_rate ?? 0);
        
        $grossPay = ($request->basic_salary ?? 0) + 
                   ($request->allowances ?? 0) + 
                   $overtimePay + 
                   ($request->bonus ?? 0) + 
                   ($request->commission ?? 0);

        $totalDeductions = ($request->tax_deduction ?? 0) + 
                          ($request->insurance_deduction ?? 0) + 
                          ($request->loan_deduction ?? 0) + 
                          ($request->other_deductions ?? 0);

        $netPay = $grossPay - $totalDeductions;

        $payroll = Payroll::create([
            'employee_id' => $request->employee_id,
            'pay_period_start' => $request->pay_period_start,
            'pay_period_end' => $request->pay_period_end,
            'basic_salary' => $request->basic_salary,
            'allowances' => $request->allowances ?? 0,
            'overtime_hours' => $request->overtime_hours ?? 0,
            'overtime_rate' => $request->overtime_rate ?? 0,
            'overtime_pay' => $overtimePay,
            'bonus' => $request->bonus ?? 0,
            'commission' => $request->commission ?? 0,
            'gross_pay' => $grossPay,
            'tax_deduction' => $request->tax_deduction ?? 0,
            'insurance_deduction' => $request->insurance_deduction ?? 0,
            'loan_deduction' => $request->loan_deduction ?? 0,
            'other_deductions' => $request->other_deductions ?? 0,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'status' => 'Draft',
            'processed_by' => auth()->id(),
            'processed_date' => now(),
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('hris.payroll.index')
            ->with('success', 'Payroll record created successfully!');
    }

    /**
     * Display the specified payroll record.
     */
    public function show(Payroll $payroll): Response
    {
        $payroll->load(['employee.department', 'processor']);

        return Inertia::render('Hris/Payroll/Show', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Show the form for editing the specified payroll record.
     */
    public function edit(Payroll $payroll): Response
    {
        if ($payroll->status === 'Paid') {
            return back()->withErrors(['message' => 'Cannot edit paid payroll records.']);
        }

        $payroll->load(['employee.department']);
        
        $employees = Employee::active()->with('department')
            ->get(['id', 'first_name', 'last_name', 'employee_id', 'department_id', 'basic_salary']);

        return Inertia::render('Hris/Payroll/Edit', [
            'payroll' => $payroll,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified payroll record.
     */
    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'Paid') {
            return back()->withErrors(['message' => 'Cannot update paid payroll records.']);
        }

        $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Check for duplicate payroll record (excluding current record)
        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('pay_period_start', $request->pay_period_start)
            ->where('pay_period_end', $request->pay_period_end)
            ->where('id', '!=', $payroll->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'pay_period_start' => 'Payroll record already exists for this employee and pay period.'
            ]);
        }

        $overtimePay = ($request->overtime_hours ?? 0) * ($request->overtime_rate ?? 0);
        
        $grossPay = ($request->basic_salary ?? 0) + 
                   ($request->allowances ?? 0) + 
                   $overtimePay + 
                   ($request->bonus ?? 0) + 
                   ($request->commission ?? 0);

        $totalDeductions = ($request->tax_deduction ?? 0) + 
                          ($request->insurance_deduction ?? 0) + 
                          ($request->loan_deduction ?? 0) + 
                          ($request->other_deductions ?? 0);

        $netPay = $grossPay - $totalDeductions;

        $payroll->update([
            'employee_id' => $request->employee_id,
            'pay_period_start' => $request->pay_period_start,
            'pay_period_end' => $request->pay_period_end,
            'basic_salary' => $request->basic_salary,
            'allowances' => $request->allowances ?? 0,
            'overtime_hours' => $request->overtime_hours ?? 0,
            'overtime_rate' => $request->overtime_rate ?? 0,
            'overtime_pay' => $overtimePay,
            'bonus' => $request->bonus ?? 0,
            'commission' => $request->commission ?? 0,
            'gross_pay' => $grossPay,
            'tax_deduction' => $request->tax_deduction ?? 0,
            'insurance_deduction' => $request->insurance_deduction ?? 0,
            'loan_deduction' => $request->loan_deduction ?? 0,
            'other_deductions' => $request->other_deductions ?? 0,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'processed_by' => auth()->id(),
            'processed_date' => now(),
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('hris.payroll.index')
            ->with('success', 'Payroll record updated successfully!');
    }

    /**
     * Remove the specified payroll record.
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'Paid') {
            return back()->withErrors(['message' => 'Cannot delete paid payroll records.']);
        }

        $payroll->delete();

        return redirect()->route('hris.payroll.index')
            ->with('success', 'Payroll record deleted successfully!');
    }

    /**
     * Approve a payroll record.
     */
    public function approve(Payroll $payroll)
    {
        if ($payroll->status !== 'Draft') {
            return response()->json(['message' => 'Only draft payroll records can be approved'], 422);
        }

        $payroll->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_date' => now(),
        ]);

        return response()->json(['message' => 'Payroll record approved successfully']);
    }

    /**
     * Mark a payroll record as paid.
     */
    public function markAsPaid(Request $request, Payroll $payroll)
    {
        if ($payroll->status !== 'Approved') {
            return response()->json(['message' => 'Only approved payroll records can be marked as paid'], 422);
        }

        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $payroll->update([
            'status' => 'Paid',
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
        ]);

        return response()->json(['message' => 'Payroll record marked as paid successfully']);
    }

    /**
     * Generate payroll for all employees.
     */
    public function generateBulk(Request $request)
    {
        $request->validate([
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after_or_equal:pay_period_start',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:hris_employees,id',
            'department_id' => 'nullable|exists:hris_departments,id',
        ]);

        $query = Employee::active();

        if ($request->employee_ids) {
            $query->whereIn('id', $request->employee_ids);
        } elseif ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->get();
        $created = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            // Check if payroll already exists
            $exists = Payroll::where('employee_id', $employee->id)
                ->where('pay_period_start', $request->pay_period_start)
                ->where('pay_period_end', $request->pay_period_end)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Create basic payroll record
            Payroll::create([
                'employee_id' => $employee->id,
                'pay_period_start' => $request->pay_period_start,
                'pay_period_end' => $request->pay_period_end,
                'basic_salary' => $employee->basic_salary,
                'allowances' => 0,
                'overtime_hours' => 0,
                'overtime_rate' => 0,
                'overtime_pay' => 0,
                'bonus' => 0,
                'commission' => 0,
                'gross_pay' => $employee->basic_salary,
                'tax_deduction' => 0,
                'insurance_deduction' => 0,
                'loan_deduction' => 0,
                'other_deductions' => 0,
                'total_deductions' => 0,
                'net_pay' => $employee->basic_salary,
                'status' => 'Draft',
                'processed_by' => auth()->id(),
                'processed_date' => now(),
            ]);

            $created++;
        }

        return response()->json([
            'message' => "Bulk payroll generation completed. Created: {$created}, Skipped: {$skipped}",
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Get payroll statistics.
     */
    public function getStats(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');

        $query = Payroll::query();

        if ($month) {
            $query->whereYear('pay_period_start', $year)
                  ->whereMonth('pay_period_start', $month);
        } else {
            $query->whereYear('pay_period_start', $year);
        }

        $stats = [
            'total_records' => $query->count(),
            'draft_records' => (clone $query)->where('status', 'Draft')->count(),
            'approved_records' => (clone $query)->where('status', 'Approved')->count(),
            'paid_records' => (clone $query)->where('status', 'Paid')->count(),
            'total_gross_pay' => (clone $query)->sum('gross_pay'),
            'total_deductions' => (clone $query)->sum('total_deductions'),
            'total_net_pay' => (clone $query)->sum('net_pay'),
            'by_department' => (clone $query)->with(['employee.department'])
                ->get()
                ->groupBy('employee.department.name')
                ->map(function ($group) {
                    return [
                        'total_records' => $group->count(),
                        'total_gross_pay' => $group->sum('gross_pay'),
                        'total_net_pay' => $group->sum('net_pay'),
                        'average_salary' => $group->avg('basic_salary'),
                    ];
                }),
            'monthly_trend' => Payroll::selectRaw('MONTH(pay_period_start) as month, COUNT(*) as count, SUM(net_pay) as total_net_pay')
                ->whereYear('pay_period_start', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Export payroll data.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'pay_period_start' => 'nullable|date',
            'pay_period_end' => 'nullable|date',
            'employee_ids' => 'nullable|array',
            'department_id' => 'nullable|exists:hris_departments,id',
        ]);

        // This would typically use a package like Laravel Excel
        // For now, return a simple CSV export
        $query = Payroll::with(['employee.department']);

        if ($request->pay_period_start) {
            $query->where('pay_period_start', '>=', $request->pay_period_start);
        }

        if ($request->pay_period_end) {
            $query->where('pay_period_end', '<=', $request->pay_period_end);
        }

        if ($request->employee_ids) {
            $query->whereIn('employee_id', $request->employee_ids);
        }

        if ($request->department_id) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $payrolls = $query->get();

        $filename = 'payroll_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payrolls) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Employee ID', 'Employee Name', 'Department', 'Pay Period Start', 'Pay Period End',
                'Basic Salary', 'Allowances', 'Overtime Pay', 'Bonus', 'Commission', 'Gross Pay',
                'Tax Deduction', 'Insurance Deduction', 'Loan Deduction', 'Other Deductions',
                'Total Deductions', 'Net Pay', 'Status', 'Payment Date'
            ]);

            // CSV data
            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->employee->employee_id,
                    $payroll->employee->full_name,
                    $payroll->employee->department->name,
                    $payroll->pay_period_start,
                    $payroll->pay_period_end,
                    $payroll->basic_salary,
                    $payroll->allowances,
                    $payroll->overtime_pay,
                    $payroll->bonus,
                    $payroll->commission,
                    $payroll->gross_pay,
                    $payroll->tax_deduction,
                    $payroll->insurance_deduction,
                    $payroll->loan_deduction,
                    $payroll->other_deductions,
                    $payroll->total_deductions,
                    $payroll->net_pay,
                    $payroll->status,
                    $payroll->payment_date,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}