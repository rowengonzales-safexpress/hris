<?php

namespace App\Http\Controllers\FRMS;

use Inertia\Inertia;
use App\Models\FRMS\Form;
use Illuminate\Http\Request;
use App\Models\FRMS\FrmsList;
use App\Models\FRMS\FrmsDocument;
use Illuminate\Support\Facades\DB;
use App\Models\CoreTransactionCode;
use App\Http\Controllers\Controller;
use App\Models\FRMS\FrmsDisbursement;
use App\Models\FRMS\FinanceDisbursement;
use App\Models\FRMS\FrmsLiquidationdetail;
use App\Models\FRMS\FrmsRemarks;
use App\Services\FRMS\NotificationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotification;
use App\Models\Core\User;
use App\Models\Core\CoreBranch;

class FrmsLiquidationController extends Controller
{
     public function index()
    {
        $forms = Form::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status_request', 'FD')
                        ->whereDoesntHave('disbursements');
                })
                ->orWhereHas('disbursements', function ($d) {
                    $d->where('status', 'RJ');
                })
                ->orWhereHas('remarks', function ($r) {
                    $r->where('status', 'A');
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('FRMS/LiquidationExpenses/index', [
            'masterlist' => $forms,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'ref_num' => 'nullable|string|max:255',
            'debit' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'form_id' => 'nullable|integer',
            'transaction_type' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
            'frequency' => 'nullable|integer',
            'expected_liquidation' => 'nullable|date',
            'actual_liquidation' => 'nullable|date',
            'liquidation_report_mj' => 'nullable|string|max:255',
            'original_receipts' => 'nullable|boolean',
            'amount' => 'nullable|numeric',
            'difference' => 'nullable|numeric',
            'remarks' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:255',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
            'items' => 'array',
            'items.*.id' => 'nullable|integer',
            'items.*.frmslist_id' => 'nullable|integer',
            'items.*.description' => 'nullable|string',
            'items.*.or_no' => 'nullable|string',
            'items.*.amount' => 'nullable|numeric',
            'items.*.variance' => 'nullable|numeric',
            'items.*.reason' => 'nullable|string',
            'items.*.tin' => 'nullable|string',
            'items.*.address' => 'nullable|string',
            'items.*.vat_non_vat' => 'nullable|string',
            'items.*.vatcode' => 'nullable|string',
            'items.*.expense_amount' => 'nullable|numeric',
            'items.*.input_vat' => 'nullable|numeric',
            'items.*.support_document' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf'
        ]);

        DB::beginTransaction();
        try {
            $isUpdate = !empty($validated['id']);

            $payload = $validated;


            $disbursement = FrmsDisbursement::updateOrCreate(
                ['id' => $validated['id'] ?? null],
                array_merge($payload, [
                    'branch_id' => auth()->user()->branch_id,
                    'frms_id' => $validated['form_id'] ?? null,
                    'status' => $validated['status'] ?? 'P',
                    'created_by' => $validated['created_by'] ?? auth()->id(),
                    'updated_by' => $validated['updated_by'] ?? 0,
                ])
            );

            if (!empty($validated['items'])) {
                $existing = FrmsLiquidationdetail::where('disbursement_id', $disbursement->id)->get()->keyBy('id');
                $processedIds = [];
                $uploadedDocumentObjects = [];
                foreach ($validated['items'] as $idx => $item) {
                    if (!empty($item['id']) && $existing->has($item['id'])) {
                        $detail = $existing->get($item['id']);
                        $detail->update([
                            'frmslist_id' => $item['frmslist_id'] ?? $detail->frmslist_id,
                            'ref_num' => $validated['ref_num'] ?? $detail->ref_num,
                            'variance' => $item['variance'] ?? $detail->variance,
                            'description' => $item['description'] ?? $detail->description,
                            'or_no' => $item['or_no'] ?? $detail->or_no,
                            'amount' => $item['amount'] ?? $detail->amount,
                            'reason' => $item['reason'] ?? $detail->reason,
                            'tin' => $item['tin'] ?? $detail->tin,
                            'address' => $item['address'] ?? $detail->address,
                            'vat_non_vat' => $item['vat_non_vat'] ?? $detail->vat_non_vat,
                            'vatcode' => $item['vatcode'] ?? $detail->vatcode,
                            'expense_amount' => $item['expense_amount'] ?? $detail->expense_amount,
                            'input_vat' => $item['input_vat'] ?? $detail->input_vat,
                            'updated_by' => auth()->id(),
                        ]);
                    } else {
                        $detail = FrmsLiquidationdetail::create([
                            'disbursement_id' => $disbursement->id,
                            'frmslist_id' => $item['frmslist_id'] ?? null,
                            'ref_num' => $validated['ref_num'] ?? null,
                            'variance' => $item['variance'] ?? 0,
                            'description' => $item['description'] ?? null,
                            'or_no' => $item['or_no'] ?? null,
                            'amount' => $item['amount'] ?? 0,
                            'reason' => $item['reason'] ?? null,
                            'tin' => $item['tin'] ?? null,
                            'address' => $item['address'] ?? null,
                            'vat_non_vat' => $item['vat_non_vat'] ?? null,
                            'vatcode' => $item['vatcode'] ?? null,
                            'expense_amount' => $item['expense_amount'] ?? 0,
                            'input_vat' => $item['input_vat'] ?? 0,
                            'created_by' => auth()->id(),
                            'updated_by' => 0,
                        ]);
                    }
                    $processedIds[] = $detail->id;

                    $uploadedFile = $request->file("items.$idx.support_document");
                    if (!$uploadedFile) {
                        $itemsFiles = $request->file('items');
                        if (is_array($itemsFiles) && isset($itemsFiles[$idx]['support_document'])) {
                            $uploadedFile = $itemsFiles[$idx]['support_document'];
                        }
                    }
                    if ($uploadedFile) {
                        $originalName = $uploadedFile->getClientOriginalName();
                        $extension = $uploadedFile->getClientOriginalExtension();
                        $filename = Str::uuid().'.'.$extension;
                        $directory = 'frls_documents/'.($validated['form_id'] ?? 'unknown').'/'.date('Y').'/'.date('m');
                        $filePath = Storage::disk('public')->putFileAs($directory, $uploadedFile, $filename);

                        $document = new FrmsDocument();
                        $document->documentable_id = $detail->id;
                        $document->documentable_type = FrmsLiquidationdetail::class;
                        $document->document_name = pathinfo($originalName, PATHINFO_FILENAME);
                        $document->original_filename = $originalName;
                        $document->file_path = $filePath;
                        $document->file_extension = $extension;
                        $document->mime_type = $uploadedFile->getMimeType();
                        $document->file_size = $uploadedFile->getSize();
                        $document->description = $item['description'] ?? 'Liquidation support document';
                        $document->uploaded_by = auth()->id();
                        $document->is_active = true;
                        $document->save();
                        $uploadedDocumentObjects[] = $document;
                    }
                }
                if (!empty($processedIds)) {
                    FrmsLiquidationdetail::where('disbursement_id', $disbursement->id)
                        ->whereNotIn('id', $processedIds)
                        ->delete();
                }

                if (!empty($uploadedDocumentObjects)) {
                    try {
                        $formModel = Form::find($validated['form_id']);
                        foreach ($uploadedDocumentObjects as $doc) {
                            NotificationService::notifyDocumentUploaded($doc, $formModel, 4, $formModel?->user_id);
                        }
                    } catch (\Throwable $e) {
                    }
                }

            }

            if (!empty($validated['form_id'])) {
                $exists = FrmsDisbursement::where('frms_id', $validated['form_id'])->exists();
                if ($exists) {
                    FrmsRemarks::where('documentId', $validated['form_id'])
                        ->whereIn('aliase', ['liquidation_reject', 'Liquidation_Reject'])
                        ->where('status', 'A')
                        ->update(['status' => 'C']);
                }
            }

            Form::where('id', $validated['form_id'])->update([
                'status_request' => 'FL',
            ]);

            if (!$isUpdate) {
                $disbursement->load('form');
                NotificationService::notifyDisbursementCreated($disbursement, 4, $disbursement->form->user_id ?? null);
            }

            DB::commit();
            try {
                $formModel = Form::find($validated['form_id'] ?? null);
                if ($formModel) {
                    $creator = User::find($formModel->user_id);
                    $creatorEmail = $creator?->email;
                    $siteheadId = $creator?->sitehead_user_id;
                    $siteheadEmail = $siteheadId ? User::where('id', $siteheadId)->value('email') : null;

                    if ($isUpdate) {
                        $subject = 'Liquidation Details Updated - Request no. ' . $formModel->frm_no;
                        $emailRequest = [
                            'subject' => $subject,
                            'title' => 'Liquidation Details Updated',
                            'intro' => 'Liquidation details have been updated for your fund request.',
                            'frm_no' => $formModel->frm_no,
                            'purpose' => $formModel->purpose,
                            'request_date' => $formModel->request_date,
                            'expectedliquidation_date' => $formModel->expectedliquidation_date,
                            'status_request' => $formModel->status_request,
                            'action_url' => "/frls/form/{$formModel->id}",
                            'branch_name' => CoreBranch::where('id', $formModel->branch_id)->value('branch_name'),
                        ];
                    } else {
                        $subject = 'Fund Disbursement Processed - Request no. ' . ($formModel->frm_no ?? '');
                        $emailRequest = [
                            'subject' => $subject,
                            'title' => 'Fund Disbursement Processed',
                            'intro' => 'Your fund request has been processed for disbursement.',
                            'frm_no' => $formModel->frm_no,
                            'purpose' => $formModel->purpose,
                            'request_date' => $formModel->request_date,
                            'expectedliquidation_date' => $formModel->expectedliquidation_date,
                            'status_request' => $formModel->status_request,
                            'amount' => $disbursement->amount ?? 0,
                            'action_url' => "/frls/form/{$formModel->id}",
                            'branch_name' => CoreBranch::where('id', $formModel->branch_id)->value('branch_name'),
                        ];
                    }

                    $to = [];
                    if ($creatorEmail) { $to[] = $creatorEmail; }
                    if (empty($to) && $siteheadEmail) { $to[] = $siteheadEmail; }
                    $cc = [];
                    if ($siteheadEmail && !in_array($siteheadEmail, $to)) { $cc[] = $siteheadEmail; }

                    if (!empty($to)) {
                        Mail::to($to)
                            ->cc($cc)
                            ->send(new MailNotification($emailRequest, $subject, 'emails.generic'));
                    }
                }
            } catch (\Throwable $e) {
            }
            return redirect()->back()->with('success', 'Finance `disbursement` saved');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Unable to save Liquidation Detail: ' . $e->getMessage()]);
        }
    }

    public function breakdown($formId)
    {
        $items = FrmsList::where('requesting_id', $formId)
            ->select('id','account_code_title','frequency', 'description','qty','unit_price', 'amount')
            ->orderByDesc('created_at')
            ->get();

        $total = $items->sum('amount');

        return response()->json([
            'items' => $items,
            'total_amount' => $total,
        ]);
    }
}
