<?php

namespace App\Http\Controllers\FRMS;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\FRMS\Form;
use Illuminate\Http\Request;
use App\Mail\MailNotification;
use App\Models\FRMS\FrmsRemarks;
use App\Models\FRMS\FrmsDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\FRMS\FrmsDisbursement;
use App\Models\FRMS\FrmsLiquidaionappved;
use App\Models\FRMS\FrmsLiquidationdetail;
use App\Services\FRMS\NotificationService;

class ApprovalController extends Controller
{
   public function index(): Response
{
    $disbursementApprovals = $this->getDisbursementApprovals();
    $liquidationApprovals = $this->getLiquidationApprovals();

    return Inertia::render('FRMS/Approval/index', [
        'disbursementApprovals' => $disbursementApprovals,
        'liquidationApprovals' => $liquidationApprovals,
    ]);
}

    private function getDisbursementApprovals()
    {
        $user = Auth::user();

        $query = Form::with(['user:id,first_name,last_name,sitehead_user_id'])
            ->orderByDesc('created_at');

        $supportRoleId = Role::where('name', 'Finance Staff')->value('id');

        if ($user && $user->member_role == $supportRoleId && in_array($user->user_type, ['Admin','admin', 'User','user'])) {
            $query->whereIn('status_request', ['A']);
        } else {
            $query->where('status_request', 'FA')
                ->whereHas('user', function ($q) {
                    $q->where('sitehead_user_id', Auth::id());
                });
        }

        return $query->get();
    }

    private function getLiquidationApprovals()
    {
        $user = Auth::user();

        $query = Form::with([
                'user:id,first_name,last_name,sitehead_user_id',
                'disbursements' => function ($q) {
                    $q->select('id', 'frms_id', 'status', 'disbursement_no')
                        ->whereNotNull('frms_id');
                }
            ])
            ->whereDoesntHave('remarks', function ($q) {
                $q->where('status', 'A');
            })
            ->orderByDesc('created_at');

        if ($user && in_array($user->user_type, ['admin','Admin', 'user','User'])) {
            if ($user->member_role == 5) {
                $query->whereHas('disbursements', function ($q) {
                    $q->whereNotNull('frms_id')
                        ->where('status', 'P');
                });
            } elseif ($user->member_role == 6) {
                $query->whereHas('disbursements', function ($q) {
                    $q->whereNotNull('frms_id')
                        ->where('status', 'FS');
                });
            } elseif ($user->member_role == 9) {
                $query->whereHas('disbursements', function ($q) {
                    $q->whereNotNull('frms_id')
                        ->where('status', 'FM');
                });
            } else {
                $query->where('status_request', 'FA')
                    ->whereHas('user', function ($q) {
                        $q->where('sitehead_user_id', Auth::id());
                    })
                    ->whereHas('disbursements', function ($q) {
                        $q->whereNotNull('frms_id');
                    });
            }
        } else {
            $query->where('status_request', 'FA')
                ->whereHas('user', function ($q) {
                    $q->where('sitehead_user_id', Auth::id());
                })
                ->whereHas('disbursements', function ($q) {
                    $q->whereNotNull('frms_id');
                });
        }

        return $query->get();
    }

    public function details($id)
    {
        $form = Form::with(['items', 'user:id,first_name,last_name', 'branch:id,branch_name'])
            ->findOrFail($id);

        $form->items->load('frequency');

        $data = [
            'id' => $form->id,
            'frm_no' => $form->frm_no,
            'request_date' => $form->request_date,
            'expectedliquidation_date' => $form->expectedliquidation_date,
            'purpose' => $form->purpose,
            'status_request' => $form->status_request,
            'user_name' => $form->user ? trim(($form->user->first_name ?? '') . ' ' . ($form->user->last_name ?? '')) : null,
            'branch_name' => $form->branch->branch_name ?? null,
            'quotation' => $form->quotation,
        ];

        return response()->json([
            'success' => true,
            'form' => $data,
            'items' => $form->items,
        ]);
    }

    public function finish(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'branch_id' => ['required', 'integer'],
        ]);

        $form = Form::where('id', $validated['id'])
            ->where('branch_id', $validated['branch_id'])
            ->first();

        if (!$form) {
            return response()->json([
                'success' => false,
                'message' => 'Form not found for provided branch.',
            ], 404);
        }

        $user = Auth::user();
        $isAdmin = $user && $user->member_role == 5 && in_array($user->user_type, ['admin', 'Admin', 'User', 'user']);

        $oldStatus = $form->status_request;

        if ($isAdmin) {
            if ($form->status_request == 'A') {
                $form->status_request = 'FD';
            } elseif ($form->status_request == 'FD') {
                $form->status_request = 'FL';
            } elseif ($form->status_request == 'FL') {
                // No change
            } elseif ($form->status_request == 'C') {
                // No change
            }
        } else {
            $form->status_request = 'A';
        }
        $form->save();

        $disbursement = FrmsDisbursement::where('frms_id', $form->id)
            ->orderByDesc('id')
            ->first();

        if ($disbursement && $user && in_array($user->user_type, ['admin','Admin', 'user','User'])) {
            if ($user->member_role == 5) {
                $disbursement->status = 'FS';
                $disbursement->save();

                FrmsLiquidaionappved::create([
                    'disbursement_id' => $disbursement->id,
                    'approvedby_id' => Auth::id(),
                ]);

                $managers = User::where('member_role', 6)->pluck('id')->toArray();
                if (!empty($managers)) {
                    NotificationService::createForUsers(
                        $managers,
                        'Disbursement Status Updated',
                        'Disbursement ' . ($disbursement->disbursement_no ?? '') . ' moved to Finance Staff (FS).',
                        'info',
                        '/flms/finance-disbursement',
                        [
                            'disbursement_id' => $disbursement->id,
                            'form_id' => $form->id,
                            'new_status' => 'FS',
                        ],
                        4,
                        null
                    );
                }
            } elseif ($user->member_role == 6) {
                $creatorRole = null;
                if ($disbursement->created_by) {
                    $creatorRole = User::where('id', $disbursement->created_by)->value('member_role');
                }

                if (in_array($creatorRole, [7, 8])) {
                    $disbursement->status = 'FM';
                    $disbursement->save();

                    FrmsLiquidaionappved::create([
                        'disbursement_id' => $disbursement->id,
                        'approvedby_id' => Auth::id(),
                    ]);

                    $managers = User::where('member_role', 6)->pluck('id')->toArray();
                    if (!empty($managers)) {
                        NotificationService::createForUsers(
                            $managers,
                            'Disbursement Status Updated',
                            'Disbursement ' . ($disbursement->disbursement_no ?? '') . ' moved to Finance Manager (FM).',
                            'info',
                            '/flms/finance-disbursement',
                            [
                                'disbursement_id' => $disbursement->id,
                                'form_id' => $form->id,
                                'new_status' => 'FM',
                            ],
                            4,
                            null
                        );
                    }
                } else {
                    $disbursement->status = 'C';
                    $disbursement->save();
                    //update form request
                    $form->status_request = 'C';
                    $form->save();

                    FrmsLiquidaionappved::create([
                        'disbursement_id' => $disbursement->id,
                        'approvedby_id' => Auth::id(),
                    ]);

                    if ($disbursement->created_by) {
                        NotificationService::create(
                            $disbursement->created_by,
                            'Disbursement Closed',
                            'Disbursement ' . ($disbursement->disbursement_no ?? '') . ' has been closed.',
                            'info',
                            '/flms/finance-disbursement',
                            [
                                'disbursement_id' => $disbursement->id,
                                'form_id' => $form->id,
                                'new_status' => 'C',
                            ],
                            4,
                            $disbursement->created_by
                        );
                    }
                }
            } else {
                $disbursement->status = 'C';
                $disbursement->save();

                 //update form request
                    $form->status_request = 'C';
                    $form->save();

                FrmsLiquidaionappved::create([
                    'disbursement_id' => $disbursement->id,
                    'approvedby_id' => Auth::id(),
                ]);

                if ($disbursement->created_by) {
                    NotificationService::create(
                        $disbursement->created_by,
                        'Disbursement Closed',
                        'Disbursement ' . ($disbursement->disbursement_no ?? '') . ' has been closed.',
                        'info',
                        '/flms/finance-disbursement',
                        [
                            'disbursement_id' => $disbursement->id,
                            'form_id' => $form->id,
                            'new_status' => 'C',
                        ],
                        4,
                        $disbursement->created_by
                    );
                }
            }
        }

        try {
            $creator = User::find($form->user_id);
            $creatorEmail = $creator?->email;
            $siteheadId = $creator?->sitehead_user_id;
            $siteheadEmail = $siteheadId ? User::where('id', $siteheadId)->value('email') : null;

                if ($isAdmin) {
                    $subject = 'Disbursement Approved - Request no. ' . $form->frm_no;
                    $emailRequest = [
                        'subject' => $subject,
                        'title' => 'Disbursement Approved',
                        'intro' => 'Your fund request has been approved for disbursement.',
                        'frm_no' => $form->frm_no,
                        'purpose' => $form->purpose,
                        'request_date' => $form->request_date,
                        'expectedliquidation_date' => $form->expectedliquidation_date,
                        'status_request' => $form->status_request,
                        'created_by' => $creator ? ($creator->first_name . ' ' . $creator->last_name) : null,
                        'action_url' => "/flms/finance-disbursement",
                        'branch_name' => \App\Models\Core\CoreBranch::where('id', $form->branch_id)->value('branch_name'),
                    ];
                } else {
                    $subject = 'Fund Request Status Updated to Approved - Request no. ' . $form->frm_no;
                    $emailRequest = [
                        'subject' => $subject,
                        'title' => 'Fund Request Status Updated',
                        'intro' => ($creator ? ($creator->first_name . ' ' . $creator->last_name) : 'A user') . " moved fund request to For Liquidation.",
                        'frm_no' => $form->frm_no,
                        'purpose' => $form->purpose,
                        'request_date' => $form->request_date,
                        'expectedliquidation_date' => $form->expectedliquidation_date,
                        'status_request' => $form->status_request,
                        'created_by' => $creator ? ($creator->first_name . ' ' . $creator->last_name) : null,
                        'action_url' => "/flms/form/{$form->id}",
                        'approval_url' => "/flms/approval",
                        'branch_name' => \App\Models\Core\CoreBranch::where('id', $form->branch_id)->value('branch_name'),
                    ];
                }



            if ($isAdmin) {
                NotificationService::notifyUser(
                    $form,
                    $oldStatus,
                    'FD',
                    4,
                    $form->user_id
                );

                if (!empty($creatorEmail)) {
                    Mail::to([$creatorEmail])
                        ->send(new MailNotification($emailRequest, $subject, 'emails.generic'));
                }
            } else {
                $admin = User::where('member_role', 5)
                    ->where('user_type', 'admin')
                    ->orderBy('id')
                    ->first(['id', 'email']);
                if ($admin) {
                    NotificationService::notifySystem(
                        $form,
                        $oldStatus,
                        'A',
                        4,
                        $form->user_id
                    );

                    if (!empty($admin->email)) {
                        Mail::to([$admin->email])
                            ->send(new MailNotification($emailRequest, $subject, 'emails.generic'));
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return redirect()->back()->with('success', 'Request Fund moved to For Liquidation (FL)');
    }

    public function reject(Request $request)
    {
        $validated = $request->validate([
            'documentId' => ['required', 'integer'],
            'aliase' => ['required', 'string'],
            'remarks' => ['required', 'string'],
        ]);

        $user = Auth::user();

        try {
            if ($validated['aliase'] === 'form') {
                $form = Form::findOrFail($validated['documentId']);

                $aliase = ($form->status_request === 'FL') ? 'liquidation_reject' : 'disbursement_reject';

                // Save remarks
                FrmsRemarks::create([
                    'documentId' => $form->id,
                    'aliase' => $aliase,
                    'remarks' => $validated['remarks'],
                    'status' => 'A',
                ]);

                $oldStatus = $form->status_request;
                $newStatus = ($form->status_request === 'A') ? 'FA' : 'RJ';
                $form->status_request = $newStatus;
                $form->save();

                // Notification and Email
                $creator = User::find($form->user_id);
                $creatorEmail = $creator?->email;

                $subject = 'Fund Request Rejected - Request no. ' . $form->frm_no;
                $emailRequest = [
                    'subject' => $subject,
                    'title' => 'Fund Request Rejected',
                    'intro' => 'Your fund request has been rejected/returned.',
                    'frm_no' => $form->frm_no,
                    'purpose' => $form->purpose,
                    'remarks' => $validated['remarks'],
                    'status_request' => $newStatus,
                    'created_by' => $creator ? ($creator->first_name . ' ' . $creator->last_name) : null,
                    'action_url' => "/flms/form/{$form->id}",
                    'branch_name' => \App\Models\Core\CoreBranch::where('id', $form->branch_id)->value('branch_name'),
                ];

                NotificationService::notifyUser(
                    $form,
                    $oldStatus,
                    $newStatus,
                    4,
                    $form->user_id
                );

                if (!empty($creatorEmail)) {
                    Mail::to([$creatorEmail])
                        ->send(new MailNotification($emailRequest, $subject, 'emails.generic'));
                }

            } elseif ($validated['aliase'] === 'disbursement') {
                $disbursement = FrmsDisbursement::findOrFail($validated['documentId']);
                $form = Form::find($disbursement->frms_id);

                // Save remarks
                FrmsRemarks::create([
                    'documentId' => $disbursement->id,
                    'aliase' => 'Liquidation_reject',
                    'remarks' => $validated['remarks'],
                    'status' => 'A',
                ]);

                // Update status to P as requested
                $disbursement->status = 'P';
                $disbursement->save();

                if ($disbursement->created_by) {
                     NotificationService::create(
                        $disbursement->created_by,
                        'Disbursement Rejected',
                        'Disbursement ' . ($disbursement->disbursement_no ?? '') . ' has been rejected/returned.',
                        'error',
                        '/flms/finance-disbursement',
                        [
                            'remarks' => $validated['remarks'] ?? null,
                            'aliase' => 'Liquidation_reject',
                        ],
                        4,
                        $disbursement->created_by
                    );
                }
            }

            return redirect()->back()->with('success', 'Request rejected successfully');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    public function liquidationDetails($formId)
    {
        $form = Form::with(['items', 'user:id,first_name,last_name', 'branch:id,branch_name'])
            ->where('id', $formId)
            ->first(['id', 'frm_no', 'user_id', 'branch_id', 'purpose', 'request_date', 'expectedliquidation_date', 'status_request', 'quotation']);

        if (!$form) {
            return response()->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $disbursement = FrmsDisbursement::where('frms_id', $form->id)
            ->orderByDesc('id')
            ->first(['id', 'disbursement_no', 'status']);

        $items = [];
        if ($disbursement) {
            $rejectionRemarks = \App\Models\FRMS\FrmsRemarks::where('documentId', $disbursement->id)
                ->whereIn('aliase', ['Liquidation_reject','liquidation_reject'])
                ->orderByDesc('id')
                ->first(['remarks','status','aliase','id']);
            $itemsCollection = FrmsLiquidationdetail::where('disbursement_id', $disbursement->id)
                ->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'disbursement_id',
                    'frmslist_id',
                    'ref_num',
                    'variance',
                    'description',
                    'or_no',
                    'amount',
                    'reason',
                    'tin',
                    'address',
                    'vatcode',
                    'vat_non_vat',
                    'expense_amount',
                    'input_vat',
                ]);

            $liquidationIds = $itemsCollection->pluck('id');
            $documents = FrmsDocument::where('documentable_type', FrmsLiquidationdetail::class)
                ->whereIn('documentable_id', $liquidationIds)
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->get(['id', 'documentable_id', 'original_filename', 'file_path', 'mime_type', 'file_extension', 'file_size']);
            $grouped = $documents->groupBy('documentable_id');

            $items = $itemsCollection->map(function ($item) use ($grouped) {
                $docs = $grouped->get($item->id, collect());
                return array_merge($item->toArray(), [
                    'documents' => $docs->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'original_filename' => $doc->original_filename,
                            'file_url' => $doc->file_url,
                            'mime_type' => $doc->mime_type,
                            'file_extension' => $doc->file_extension,
                            'file_size' => $doc->file_size,
                        ];
                    })->values()->all(),
                ]);
            })->values()->all();
        }

        $data = [
            'id' => $form->id,
            'frm_no' => $form->frm_no,
            'request_date' => $form->request_date,
            'expectedliquidation_date' => $form->expectedliquidation_date,
            'purpose' => $form->purpose,
            'status_request' => $form->status_request,
            'user_name' => $form->user ? trim(($form->user->first_name ?? '') . ' ' . ($form->user->last_name ?? '')) : null,
            'branch_name' => $form->branch->branch_name ?? null,
            'quotation' => $form->quotation,
        ];

        return response()->json([
            'success' => true,
            'form' => $data,
            'disbursement' => $disbursement,
            'rejection_remarks' => $rejectionRemarks,
            'items' => $items,
        ]);
    }
}
