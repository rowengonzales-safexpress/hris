<?php

namespace App\Http\Controllers\FRMS;

use Inertia\Inertia;
use App\Models\FRMS\Form;
use App\Models\FRMS\FrmsLiquidationdetail;
use App\Models\FRMS\FrmsDisbursement;
use App\Models\FRMS\FrmsDocument;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
   public function index()
{
    $user = auth()->user();

    // Check if user exists and meets the criteria
    if ($user && in_array($user->member_role, [1, 5, 6, 7, 8, 9]) && in_array($user->user_type, ['admin', 'user'])) {
        $forms = Form::orderByDesc('created_at')
        ->where('status_request', 'FL')
            ->get();

        return Inertia::render('FRMS/Review/Finance/index', [
            'masterlist' => $forms,
        ]);
    }

    // Handle unauthorized access
    abort(401);

    // Alternative: return an empty response or error page
    // return Inertia::render('Unauthorized');
}

    public function show(Form $form)
    {
        $form->load(['user', 'branch', 'items.frequency', 'disbursements']);

        $requested = $form->items->sum('amount');
        $latestDisbursement = $form->disbursements->sortByDesc('created_at')->first();
        $liquidationDetails = $latestDisbursement
            ? FrmsLiquidationdetail::where('disbursement_id', $latestDisbursement->id)->get()
            : collect();
        $liquidatedByExpense = $liquidationDetails->sum('expense_amount');
        $liquidatedByAmount = $liquidationDetails->sum('amount');
        $liquidated = $liquidatedByExpense > 0 ? $liquidatedByExpense : $liquidatedByAmount;
        $refNum = optional($liquidationDetails->first())->ref_num;
        $variance = $requested - $liquidated;

        return Inertia::render('FRMS/Review/Finance/Reviewpage', [
            'form' => $form,
            'summary' => [
                'requested_total' => $requested,
                'liquidated_total' => $liquidated,
                'variance_total' => $variance,
                'disbursement_no' => optional($latestDisbursement)->disbursement_no,
                'liquidation_ref' => $refNum,

            ],
        ]);
    }

    public function FinanceDetails($formId)
    {
        $form = Form::with(['user', 'branch'])
            ->where('id', $formId)
            ->first(['id', 'frm_no', 'user_id', 'branch_id', 'purpose', 'request_date', 'expectedliquidation_date', 'status_request']);

        if (!$form) {
            return response()->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $items = DB::table('frms_list as fl')
            ->leftJoin('core_transaction_code as ctc', 'ctc.id', '=', 'fl.frequency')
            ->where('fl.requesting_id', $form->id)
            ->select([
                'fl.account_code_title',
                DB::raw('COALESCE(ctc.description, "") as frequency_label'),
                'fl.description',
                'fl.qty',
                'fl.unit_price',
                'fl.amount',
            ])->get();

        $disbursement = FrmsDisbursement::where('frms_id', $form->id)
            ->orderByDesc('id')
            ->first(['id', 'disbursement_no', 'status', DB::raw('created_at as actual_liquidation_date')]);

        $liquidation = [];
        if ($disbursement) {
            $liquidation = FrmsLiquidationdetail::where('disbursement_id', $disbursement->id)
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'ref_num',
                    'or_no',
                    'description',
                    'expense_amount',
                    'input_vat',
                    'vat_non_vat',
                    DB::raw('frms_liquidationdetail.created_at as date'),
                ]);

            $liqIds = $liquidation->pluck('id')->all();
            if (!empty($liqIds)) {
                $docs = FrmsDocument::whereIn('documentable_id', $liqIds)
                    ->where('documentable_type', FrmsLiquidationdetail::class)
                    ->where('is_active', true)
                    ->orderByDesc('created_at')
                    ->get(['id', 'documentable_id', 'file_path', 'mime_type']);

                $docMap = [];
                foreach ($docs as $d) {
                    if (!isset($docMap[$d->documentable_id])) {
                        if (str_starts_with($d->mime_type, 'image/')) {
                            $docMap[$d->documentable_id] = asset('storage/' . $d->file_path);
                        } else {
                            $docMap[$d->documentable_id] = asset('storage/' . $d->file_path);
                        }
                    }
                }

                $liquidation = $liquidation->map(function ($li) use ($docMap) {
                    $li->thumbnail_url = $docMap[$li->id] ?? null;
                    return $li;
                });
            }
        }

        return response()->json([
            'success' => true,
            'form' => [
                'frm_no' => $form->frm_no,
                'purpose' => $form->purpose,
                'request_date' => $form->request_date,
                'expectedliquidation_date' => $form->expectedliquidation_date,
                'status_request' => $form->status_request,
                'branch_name' => $form->branch?->branch_name,
                'employee_name' => trim(($form->user->first_name ?? '') . ' ' . ($form->user->last_name ?? '')),
            ],
            'disbursement' => $disbursement ? [
                'id' => $disbursement->id,
                'disbursement_no' => $disbursement->disbursement_no,
                'status' => $disbursement->status,
                'actual_liquidation_date' => $disbursement->actual_liquidation_date,
            ] : null,
            'items' => $items,
            'liquidation' => $liquidation,
        ]);
    }
}
