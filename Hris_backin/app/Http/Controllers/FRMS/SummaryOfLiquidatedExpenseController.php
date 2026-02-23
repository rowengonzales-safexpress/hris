<?php

namespace App\Http\Controllers\FRMS;

use Inertia\Inertia;
use App\Models\FRMS\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\FRMS\FinanceDisbursement;
use App\Models\FRMS\SummaryOfLiquidatedExpense;

class SummaryOfLiquidatedExpenseController extends Controller
{
    public function index()
    {
        $expenses = SummaryOfLiquidatedExpense::orderByDesc('created_at')->get();

        return Inertia::render('FRMS/LiquidationExpenses/index', [
            'masterlist' => $expenses,
        ]);
    }

    public function getOpenReferences()
    {
        $openReferences = FinanceDisbursement::with(['form.user', 'form.branch'])
            ->where('status', 'O')
            ->orderByDesc('created_at')
            ->get(['id', 'ref_num', 'form_id', 'description', 'amount']);

        return response()->json($openReferences);
    }

    public function getFormDetails($formId)
    {
        $form = Form::with(['user', 'branch'])
            ->where('id', $formId)
            ->first(['id', 'frm_no', 'user_id', 'branch_id', 'expectedliquidation_date']);

        if (!$form) {
            return response()->json(['error' => 'Form not found'], 404);
        }

        return response()->json([
            'id' => $form->id,
            'frm_no' => $form->frm_no,
            'user_name' => $form->user ? $form->user->first_name . ' ' . $form->user->last_name : '',
            'branch_name' => $form->branch ? $form->branch->branch_name : '',
            'expectedliquidation_date' => $form->expectedliquidation_date,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'ref_num' => 'required|string|max:255',
            'date' => 'required|date',
            'or_no' => 'nullable|string|max:255',
            'plate_no' => 'nullable|string|max:255',
            'account_code_title' => 'nullable|string|max:255',
            'particulars' => 'nullable|string',
            'supplier_name' => 'required|string|max:255',
            'tin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'location_client' => 'nullable|string|max:255',
            'gross_amount' => 'nullable|numeric',
            'vat_non_vat' => 'nullable',
            'expense_amount' => 'required|numeric',
            'input_vat' => 'nullable|numeric',
            'accountable_person' => 'nullable|string|max:255',
            'validated_by_accounting' => 'nullable|boolean',
            'manual_journal_no' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'status' => 'nullable|in:P,O,A,C'
        ]);

        try {
            DB::beginTransaction();

            $vatValue = 0;
            if (is_array($request->vat_non_vat)) {
                $vatValue = $request->vat_non_vat['value'] ?? 0;
            } elseif (is_numeric($request->vat_non_vat)) {
                $vatValue = $request->vat_non_vat;
            }

            $payload = [
                'ref_num' => $validated['ref_num'],
                'date' => $validated['date'],
                'or_no' => $validated['or_no'] ?? null,
                'plate_no' => $validated['plate_no'] ?? null,
                'account_code_title' => $validated['account_code_title'] ?? null,
                'particulars' => $validated['particulars'] ?? null,
                'supplier_name' => $validated['supplier_name'],
                'tin' => $validated['tin'] ?? null,
                'address' => $validated['address'] ?? null,
                'location_client' => $validated['location_client'] ?? null,
                'gross_amount' => $validated['gross_amount'] ?? 0,
                'vat_non_vat' => $vatValue,
                'expense_amount' => $validated['expense_amount'],
                'input_vat' => $validated['input_vat'] ?? 0,
                'accountable_person' => $validated['accountable_person'] ?? null,
                'validated_by_accounting' => $validated['validated_by_accounting'] ?? false,
                'manual_journal_no' => $validated['manual_journal_no'] ?? null,
                'code' => $validated['code'] ?? null,
                'status' => $validated['status'] ?? 'P',
            ];

            $expense = SummaryOfLiquidatedExpense::updateOrCreate(
                ['id' => $validated['id'] ?? null],
                $payload
            );

            $total = SummaryOfLiquidatedExpense::where('ref_num', $payload['ref_num'])->sum('expense_amount');
            $disbursement = FinanceDisbursement::where('ref_num', $payload['ref_num'])->first();
            if ($disbursement) {
                $amount = $disbursement->amount ?? 0;
                $difference = $amount - $total;
                $disbursement->difference = $difference;
                if ($total >= $amount && $amount > 0) {
                    if (empty($disbursement->liquidation_report_mj)) {
                        $disbursement->liquidation_report_mj = 'LQ-' . date('Ymd') . '-' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
                    }
                    $disbursement->status = 'C';
                    $disbursement->actual_liquidation = now();
                }
                $disbursement->save();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Liquidated expense saved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to save liquidated expense: ' . $e->getMessage());
        }
    }
}
