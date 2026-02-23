<?php

namespace App\Http\Controllers\FRMS;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use App\Models\Core\User;
use App\Models\FRMS\Form;
use App\Models\Core\Branch;
use Illuminate\Http\Request;
use App\Models\Core\CoreBranch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\FRMS\FrmsDisbursement;
use App\Models\FRMS\FrmsLiquidationdetail;
use App\Models\FRMS\FrmsDocument;

class ReporsController extends Controller
{
    public function LiquidationReport(Request $request)
    {
        $siteId = $request->get('site_id');
        $keyword = trim($request->get('keyword', ''));

        // Start from FRMS Form: include employee and branch names
        $formsQuery = Form::query()
            ->select([
                'frms_form.id',
                'frms_form.frm_no',
                'frms_form.user_id',
                'frms_form.branch_id',
                'frms_form.purpose',
                'frms_form.request_date',
                'frms_form.expectedliquidation_date',
                'frms_form.status_request',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as employee"),
                'branches.branch_name',
                'branches.id as branch_id',
            ])
            ->leftJoin('core_users as users', 'frms_form.user_id', '=', 'users.id')
            ->leftJoin('core_branch as branches', 'frms_form.branch_id', '=', 'branches.id')
            ->whereIn('frms_form.status_request', ['FL', 'C']);

        if ($siteId) {
            $formsQuery->where('frms_form.branch_id', $siteId);
        }

        $forms = $formsQuery->orderByDesc('frms_form.id')->get();

        // Build table rows: include disbursement and liquidation by disbursement_id
        $liquidationData = collect();
        foreach ($forms as $form) {
            $disb = FrmsDisbursement::where('frms_id', $form->id)
                ->orderByDesc('id')
                ->first(['id', 'disbursement_no', 'status']);



            if ($disb) {
                $liqs = FrmsLiquidationdetail::where('disbursement_id', $disb->id)
                    ->orderByDesc('created_at')
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

                foreach ($liqs as $li) {
                    $liquidationData->push([
                        'form_id' => $form->id,
                        'frm_no' => $form->frm_no,
                        'employee' => $form->employee,
                        'branch_id' => $form->branch_id,
                        'branch_name' => $form->branch_name,
                        'purpose' => $form->purpose,
                        'request_date' => $form->request_date,
                        'expectedliquidation_date' => $form->expectedliquidation_date,
                        'status_request' => $form->status_request,
                        'disbursement_no' => $disb->disbursement_no,
                        'status' => $disb->status,
                        'ref_num' => $li->ref_num,
                        'date' => $li->date,
                        'description' => $li->description,
                        'expense_amount' => $li->expense_amount,
                    ]);
                }
            }
        }

        // Apply keyword filtering on the assembled rows
        if ($keyword !== '') {
            $kw = mb_strtolower($keyword);
            $liquidationData = $liquidationData->filter(function ($row) use ($kw) {
                return Str::contains(mb_strtolower($row['ref_num'] ?? ''), $kw)
                    || Str::contains(mb_strtolower($row['employee'] ?? ''), $kw)
                    || Str::contains(mb_strtolower($row['branch_name'] ?? ''), $kw)
                    || Str::contains(mb_strtolower($row['description'] ?? ''), $kw);
            })->values();
        }

        $totalExpenses = $liquidationData->sum('expense_amount');
        $totalRecords = $liquidationData->count();

        // Get sites for filter dropdown
        $sites = CoreBranch::select('id', 'branch_name')->orderBy('branch_name')->get();

        // If API is requested, return JSON payload (single-page data retrieval)
        if ($request->boolean('api') || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'liquidationData' => $liquidationData,
                'filters' => [
                    'site_id' => $siteId,
                    'keyword' => $keyword,
                ],
                'summary' => [
                    'total_expenses' => $totalExpenses,
                    'total_records' => $totalRecords,
                ],
                'sites' => $sites,
            ]);
        }

        return Inertia::render('FRMS/Reports/Liquidation/index', [
            'liquidationData' => $liquidationData,
            'filters' => [
                'site_id' => $siteId,
            ],
            'summary' => [
                'total_expenses' => $totalExpenses,
                'total_records' => $totalRecords,
            ],
            'sites' => $sites,
        ]);
    }

    public function LiquidationDetails($formId)
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
            ->first(['id', 'disbursement_no', 'status']);

        $liquidation = [];
        $approvalDetails = null;
        $requested = $items->sum('amount');
        $liquidatedTotal = 0;
        $variance = $requested;

        if ($disbursement) {
            $liquidation = FrmsLiquidationdetail::where('disbursement_id', $disbursement->id)
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'ref_num',
                    'or_no',
                    'description',
                    'amount',
                    'expense_amount',
                    'input_vat',
                    'vat_non_vat',
                    DB::raw('frms_liquidationdetail.created_at as date'),
                ]);

            // Calculate totals
            $liquidatedByExpense = $liquidation->sum('expense_amount');
            $liquidatedByAmount = $liquidation->sum('amount');
            $liquidatedTotal = $liquidatedByExpense > 0 ? $liquidatedByExpense : $liquidatedByAmount;
            $variance = $requested - $liquidatedTotal;

            // Fetch approval details
            $approvalData = DB::table('frms_liquidaionappved as fla')
                ->leftJoin('core_users as cu', 'fla.approvedby_id', '=', 'cu.id')
                ->leftJoin('core_role as cr', 'cu.member_role', '=', 'cr.id')
                ->where('fla.disbursement_id', $disbursement->id)
                ->orderBy('fla.created_at', 'asc')
                ->select([
                    'cu.first_name',
                    'cu.last_name',
                    'cr.description as role_description',
                    'fla.created_at as approved_date'
                ])
                ->get();

            if ($approvalData->isNotEmpty()) {
                $approvalDetails = $approvalData->map(function ($data) {
                    return [
                        'approver_name' => trim(($data->first_name ?? '') . ' ' . ($data->last_name ?? '')),
                        'role_description' => $data->role_description ?? 'N/A',
                        'approved_date' => $data->approved_date
                    ];
                });
            }


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
                        $docMap[$d->documentable_id] = asset('storage/' . $d->file_path);
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
            ] : null,
            'items' => $items,
            'liquidation' => $liquidation,
            'approval_details' => $approvalDetails,
            'summary' => [
                'requested_total' => $requested,
                'liquidated_total' => $liquidatedTotal,
                'variance' => $variance,
            ],
        ]);
    }

    public function FinanceReport(Request $request): Response
    {
    // Get filter parameters
    $dateFrom = $request->get('date_from');
    $dateTo = $request->get('date_to');
    $siteId = $request->get('site_id');
    $status = $request->get('status');

    // Build query for finance disbursements
    // Subquery: total requested amount per form
    $itemTotalsSub = DB::raw('(
        SELECT requesting_id, SUM(amount) AS total_amount
        FROM frms_list
        GROUP BY requesting_id
    ) AS item_totals');

    // Subquery: frequency labels per form
    $freqSub = DB::raw('(
        SELECT fl.requesting_id,
               GROUP_CONCAT(DISTINCT ctc.description) AS frequency
        FROM frms_list fl
        LEFT JOIN core_transaction_code ctc ON ctc.id = fl.frequency
        GROUP BY fl.requesting_id
    ) AS freq');

    // Subquery: receipts count per form based on liquidation documents
    $receiptCountSub = DB::raw('(
        SELECT l.ref_num,
               COUNT(d.id) AS original_receipts
        FROM frms_liquidationdetail l
        LEFT JOIN frms_documents d
               ON d.documentable_id = l.id
              AND d.documentable_type = "App\\Models\\FRMS\\FrmsLiquidationdetail"
        GROUP BY l.ref_num
    ) AS receipt_counts');

    $query = FrmsDisbursement::query()
        ->select([
            'frms_disbursement.*',
            'forms.frm_no',
            'forms.user_id',
            DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullname"),
            'branches.branch_name',
            'forms.purpose',
            DB::raw('forms.expectedliquidation_date as expected_liquidation_date'),
            DB::raw('frms_disbursement.created_at as actual_liquidation_date'),
            DB::raw('COALESCE(item_totals.total_amount, 0) as amount'),
            DB::raw('COALESCE(receipt_counts.original_receipts, 0) as original_receipts'),
            DB::raw('COALESCE(freq.frequency, "") as transaction_type'),
            DB::raw('COALESCE(freq.frequency, "") as frequency'),
            DB::raw('COALESCE(item_totals.total_amount,0) - 0 as defference')
        ])
        ->leftJoin('frms_form as forms', 'frms_disbursement.frms_id', '=', 'forms.id')
        ->leftJoin('core_users as users', 'forms.user_id', '=', 'users.id')
        ->leftJoin('core_branch as branches', 'forms.branch_id', '=', 'branches.id')
        ->leftJoin($itemTotalsSub, 'forms.id', '=', 'item_totals.requesting_id')
        ->leftJoin($freqSub, 'forms.id', '=', 'freq.requesting_id')
        ->leftJoin($receiptCountSub, 'forms.frm_no', '=', 'receipt_counts.ref_num');


    // Apply date filters
    if ($dateFrom) {
        $query->whereDate('frms_disbursement.created_at', '>=', $dateFrom);
    }
    if ($dateTo) {
        $query->whereDate('frms_disbursement.created_at', '<=', $dateTo);
    }

    // Apply site filter
    if ($siteId) {
        $query->where('branches.id', $siteId);
    }

    // Apply status filter
    if ($status) {
        $query->where('frms_disbursement.status', $status);
    }

    $disbursementData = $query->orderBy('frms_disbursement.id', 'desc')->get();

    // Get summary statistics
    $totalDisbursements = $disbursementData->sum('amount');
    $totalRecords = $disbursementData->count();
    $pendingLiquidations = $disbursementData->where('status', 'P')->count();
    $completedLiquidations = $disbursementData->where('status', 'C')->count();

    // Get sites for filter dropdown
    $sites = CoreBranch::select('id', 'branch_name')->orderBy('branch_name')->get();

        return Inertia::render('FRMS/Reports/Finance/index', [
            'disbursementData' => $disbursementData,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'site_id' => $siteId,
                'status' => $status,
            ],
            'summary' => [
                'total_disbursements' => $totalDisbursements,
                'total_records' => $totalRecords,
                'pending_liquidations' => $pendingLiquidations,
                'completed_liquidations' => $completedLiquidations,
            ],
            'sites' => $sites,
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
            'items' => $items

        ]);
    }
}
