<?php

namespace App\Http\Controllers\FRMS;

use App\Http\Controllers\Controller;
use App\Models\FRMS\Form;
use App\Models\FRMS\FrmsList;
use App\Models\FRMS\FrmsDocument;
use App\Models\FRMS\FrmsRemarks;
use App\Services\FRMS\NotificationService;
use App\Models\Core\User;
use App\Mail\MailNotification;
use App\Models\Core\CoreBranch;
use App\Models\CoreTransactionCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::with('items')->orderByDesc('created_at')
            ->where('user_id', auth()->id())
            ->get();
        return Inertia::render('FRMS/Form/index', [
            'masterlist' => $forms,
        ]);
    }

    public function show($id)
    {
        $form = Form::with(['items', 'documents'])->find($id);
        if (!$form) {
             return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $form
        ]);
    }

    public function store(Request $request)
    {
        // Validate header data
        $validatedHeader = $request->validate([
            'user_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'request_date' => 'required|date',
            'expectedliquidation_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'quotation' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'status_request' => 'required|in:FA,FD,FL,A,C,X',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
        ]);

        $validatedItems = $request->validate([
            'items' => 'nullable|array',
            'items.*.account_code_title' => 'nullable|string|max:255',
            'items.*.frequency' => 'nullable|integer',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();


            // Check if this is an update or create
            $isUpdate = !empty($request->id);
            $oldStatus = null;

            if ($isUpdate) {
                $existingForm = Form::find($request->id);
                $oldStatus = $existingForm ? $existingForm->status_request : null;
            }

            // Derive purpose when not provided (system-generated)
            $purpose = $validatedHeader['purpose'] ?? '';
            if ($purpose === '' && !empty($validatedItems['items'])) {
                $descriptions = collect($validatedItems['items'])
                    ->pluck('description')
                    ->filter()
                    ->implode(', ');
                $purpose = $descriptions !== '' ? ('Fund Request for ' . $descriptions) : 'Fund Request';
            }
            if ($purpose === '') {
                $purpose = 'Fund Request';
            }

            // Prepare data for update/create
            $formData = [
                'user_id' => $validatedHeader['user_id'],
                'branch_id' => $validatedHeader['branch_id'],
                'request_date' => $validatedHeader['request_date'],
                'expectedliquidation_date' => $validatedHeader['expectedliquidation_date'] ?? null,
                'purpose' => $purpose,
                'status_request' => $validatedHeader['status_request'],
                'created_by' => $validatedHeader['created_by'] ?? auth()->id(),
                'updated_by' => $validatedHeader['updated_by'] ?? 0,
            ];

            if ($request->hasFile('quotation')) {
                $formData['quotation'] = $request->file('quotation')->store('frms/quotations', 'public');
            }

            // Insert or update header (Form)
            $form = Form::updateOrCreate(
                ['id' => $request->id],
                $formData
            );

            if ($isUpdate) {
                $hasRemarks = FrmsRemarks::where('documentId', $form->id)->exists();
                if ($hasRemarks) {
                    $form->status_request = 'A';
                    $form->save();
                    FrmsRemarks::where('documentId', $form->id)->update(['status' => 'C']);
                    $validatedHeader['status_request'] = 'A';
                }
            }

            // Save to frms_documents
            if ($request->hasFile('quotation')) {
                $file = $request->file('quotation');

                $form->documents()->updateOrCreate(
                    ['description' => 'Quotation'],
                    [
                        'document_name' => 'Quotation',
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $formData['quotation'],
                        'file_extension' => $file->getClientOriginalExtension(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                        'is_active' => true
                    ]
                );
            }

            // Sync items (FrmsList)
            if (!empty($validatedItems['items'])) {
                // Clear existing items for this form
                FrmsList::where('requesting_id', $form->id)->delete();

                foreach ($validatedItems['items'] as $item) {
                    FrmsList::create([
                        'requesting_id' => $form->id,
                        'account_code_title' => $item['account_code_title'] ?? null,
                        'frequency' => (int)($item['frequency'] ?? 0),
                        'description' => $item['description'] ?? null,
                        'qty' => $item['qty'] ?? 0,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'amount' => $item['amount'] ?? 0,
                    ]);
                }
            }

            // Notify only the site head on create/update
            $creator = User::find($form->user_id);
            $siteheadId = $creator?->sitehead_user_id;
            if ($siteheadId) {
                if (!$isUpdate) {
                    NotificationService::create(
                        $form->user_id,
                        'Fund Request Created',
                        "Fund request {$form->frm_no} has been created and is pending approval.",
                        'info',
                        "/frls/form/{$form->id}",
                        ['form_id' => $form->id, 'frm_no' => $form->frm_no],
                        4,
                        $siteheadId
                    );
                } elseif ($oldStatus && $oldStatus !== $validatedHeader['status_request']) {
                    $statusLabels = [
                        'FA' => 'Approval',
                        'FD' => 'Disbursement',
                        'FL' => 'Liquidation',
                        'A'  => 'Approved',
                        'C'  => 'Closed',
                        'X'  => 'Canceled',
                        'P'  => 'Pending',
                        'O'  => 'Open',
                    ];
                    $newStatusLabel = $statusLabels[$validatedHeader['status_request']] ?? $validatedHeader['status_request'];
                    NotificationService::create(
                        $form->user_id,
                        'Fund Request Status Updated',
                        "Fund request {$form->frm_no} status changed to {$newStatusLabel}.",
                        'info',
                        "/frls/form/{$form->id}",
                        [
                            'request_no' => $form->frm_no,
                            'request_date' => $form->request_date,
                            'expected_date' => $form->expectedliquidation_date,
                            'purpose' => $form->purpose,
                            'new_status' => $newStatusLabel,
                            'old_status' => $oldStatus,
                        ],
                        4,
                        $siteheadId
                    );
                }
            }

            DB::commit();

            try {
                $creator = User::find($form->user_id);
                $creatorEmail = $creator?->email;
                $siteheadId = $creator?->sitehead_user_id;
                $siteheadEmail = $siteheadId ? User::where('id', $siteheadId)->value('email') : null;

                // Gather line items for details
                $freqMap = CoreTransactionCode::active()->pluck('description', 'id')->toArray();
                $items = FrmsList::where('requesting_id', $form->id)
                    ->get(['account_code_title','description','qty','unit_price','amount','frequency'])
                    ->map(function($i){
                        return [
                            'account_code_title' => $i->account_code_title,
                            'frequency' => $i->frequency,
                            'description' => $i->description,
                            'qty' => $i->qty,
                            'unit_price' => $i->unit_price,
                            'amount' => $i->amount,
                        ];
                    })
                    ->toArray();
                // Replace frequency IDs with description labels
                $items = array_map(function($row) use ($freqMap) {
                    $freqId = $row['frequency'] ?? null;
                    $row['frequency'] = $freqId !== null ? ($freqMap[$freqId] ?? $freqId) : '';
                    return $row;
                }, $items);
                $totalAmount = FrmsList::where('requesting_id', $form->id)->sum('amount');
                $branchName = CoreBranch::where('id', $form->branch_id)->value('branch_name');

                if (!$isUpdate) {
                    $subject = 'New Fund Request No ' . $form->frm_no;
                    $emailRequest = [
                        'subject' => $subject,
                        'title' => 'Fund Request Created',
                        'intro' => ($creator ? ($creator->first_name . ' ' . $creator->last_name) : 'A user') . ' has submitted a fund request and requires review.',
                        'frm_no' => $form->frm_no,
                        'purpose' => $form->purpose,
                        'request_date' => \Carbon\Carbon::parse($form->request_date)->format('Y-m-d') . ' ' . $form->created_at->format('h:i A'),
                        'expectedliquidation_date' => $form->expectedliquidation_date,
                        'status_request' => $form->status_request,
                        'created_by' => $creator ? ($creator->first_name . ' ' . $creator->last_name) : null,
                        'action_url' => "/frls/form/{$form->id}",
                        'approval_url' => "/frls/approval",
                        'items' => $items,
                        'total_amount' => $totalAmount,
                        'branch_name' => $branchName,
                    ];
                } else {
                    $subject = 'Fund Request Updated - Request no. ' . $form->frm_no;
                    $emailRequest = [
                        'subject' => $subject,
                        'title' => 'Fund Request Updated',
                        'intro' => 'A fund request has been updated and may require review.',
                        'frm_no' => $form->frm_no,
                        'purpose' => $form->purpose,
                        'request_date' => \Carbon\Carbon::parse($form->request_date)->format('Y-m-d') . ' ' . $form->created_at->format('h:i A'),
                        'expectedliquidation_date' => $form->expectedliquidation_date,
                        'status_request' => $form->status_request,
                        'created_by' => $creator ? ($creator->first_name . ' ' . $creator->last_name) : null,
                        'action_url' => "/flms/form/{$form->id}",
                        'items' => $items,
                        'total_amount' => $totalAmount,
                        'branch_name' => $branchName,
                    ];
                }

                if ($siteheadEmail) {
                    Mail::to([$siteheadEmail])
                        ->send(new MailNotification($emailRequest, $subject, 'emails.generic'));
                }
            } catch (\Throwable $e) {
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Unable to save fund request: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Fund request saved');
    }
    public function destroy($id)
    {
        $form = Form::find($id);
        if (!$form) {
            return redirect()->back()->withErrors(['error' => 'Form not found']);
        }

        try {
            DB::beginTransaction();
            FrmsList::where('requesting_id', $form->id)->delete();


            $form->delete();
            DB::commit();
            try {
                $creator = User::find($form->user_id);
                $creatorEmail = $creator?->email;
                $siteheadId = $creator?->sitehead_user_id;
                $siteheadEmail = $siteheadId ? User::where('id', $siteheadId)->value('email') : null;

                $subject = 'Fund Request Deleted - Request no. ' . $form->frm_no;
                $emailRequest = [
                    'subject' => $subject,
                    'title' => 'Fund Request Deleted',
                    'intro' => 'A fund request has been deleted.',
                    'frm_no' => $form->frm_no,
                    'purpose' => $form->purpose,
                    'request_date' => \Carbon\Carbon::parse($form->request_date)->format('Y-m-d') . ' ' . $form->created_at->format('h:i A'),
                    'expectedliquidation_date' => $form->expectedliquidation_date,
                    'status_request' => $form->status_request,
                    'created_by' => $creator ? ($creator->first_name . ' ' . $creator->last_name) : null,
                    'action_url' => "/flms/form",
                    'branch_name' => CoreBranch::where('id', $form->branch_id)->value('branch_name'),
                ];

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
            } catch (\Throwable $e) {
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Unable to delete form: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Form deleted');
    }

}
