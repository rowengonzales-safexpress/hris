<?php

namespace App\Services\FRMS;

use App\Models\Notification;
use App\Models\Core\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Notify when FRMS disbursement is created
     */
    public static function notifyDisbursementCreated($disbursement, $appId = 4, $userTo = null): void
    {
        // Get the related form and user
        $form = $disbursement->form;
        if (!$form) return;

        $user = User::find($form->user_id);
        if (!$user) return;

        // Notify the user who created the original form
        self::create(
            $form->user_id,
            'Fund Disbursement Processed',
            "Your fund request {$form->frm_no} has been processed for disbursement. Amount: " . number_format($disbursement->amount ?? 0, 2),
            'success',
            "/frms/finance-disbursement",
            [
                'disbursement_id' => $disbursement->id,
                'form_id' => $form->id,
                'frm_no' => $form->frm_no,
                'amount' => $disbursement->amount
            ],
            $appId,
            $userTo
        );

        // Notify sitehead user if the form creator has one
        if ($user->sitehead_user_id) {
            self::create(
                $user->sitehead_user_id,
                'Team Member Fund Disbursement Processed',
                "Fund request {$form->frm_no} by {$user->first_name} {$user->last_name} has been processed for disbursement. Amount: " . number_format($disbursement->amount ?? 0, 2),
                'info',
                "/frms/finance-disbursement",
                [
                    'disbursement_id' => $disbursement->id,
                    'form_id' => $form->id,
                    'frm_no' => $form->frm_no,
                    'amount' => $disbursement->amount,
                    'creator_name' => "{$user->first_name} {$user->last_name}"
                ],
                $appId,
                $user->sitehead_user_id
            );
        }
    }

    /**
     * Create a notification for a specific user
     */
    public static function create(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?array $data = null,
        ?int $appId = null,
        ?int $userTo = null
    ): Notification {
        // Get the user to check for sitehead_user_id
        $user = User::find($userId);

        // If user has sitehead_user_id and userTo is not explicitly set, use sitehead_user_id
        if ($user && $user->sitehead_user_id && $userTo === null) {
            $userTo = $user->sitehead_user_id;
        }

        return Notification::create([
            'user_id' => $userId,
            'app_id' => $appId,
            'user_to' => $userTo,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Create notifications for multiple users
     */
    public static function createForUsers(
        array $userIds,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?array $data = null,
        ?int $appId = null,
        ?int $userTo = null
    ): void {
        foreach ($userIds as $userId) {
            self::create($userId, $title, $message, $type, $actionUrl, $data, $appId, $userTo);
        }
    }

    /**
     * FRMS specific notification methods
     */

    /**
     * Notify when a new FRMS form is created
     */
    public static function notifyFormCreated($form, $appId = 4, $userTo = null): void
    {
        // Notify the user who created the form
        self::create(
            $form->user_id,
            'Fund Request Created',
            "Your fund request {$form->frm_no} has been successfully created and is pending approval.",
            'success',
            "/frms/form/{$form->id}",
            ['form_id' => $form->id, 'frm_no' => $form->frm_no],
            $appId,
            $userTo
        );

        // Notify sitehead user if the creator has one
        $creator = User::find($form->user_id);
        if ($creator && $creator->sitehead_user_id) {
            self::create(
                $creator->sitehead_user_id,
                'Fund Request Created by Team Member',
                "A fund request {$form->frm_no} has been created by {$creator->first_name} {$creator->last_name} and is pending approval.",
                'info',
                "/frms/form/{$form->id}",
                [
                    'form_id' => $form->id,
                    'frm_no' => $form->frm_no,
                    'creator_name' => "{$creator->first_name} {$creator->last_name}"
                ],
                $appId,
                $creator->sitehead_user_id
            );
        }

        // Notify finance team (assuming they have a specific role or are in a specific branch)
        // You can modify this based on your user role system
        $financeUsers = User::where('member_role', 'finance')->pluck('id')->toArray();
        if (!empty($financeUsers)) {
            self::createForUsers(
                $financeUsers,
                'New Fund Request Submitted',
                "A new fund request {$form->frm_no} has been submitted and requires review.",
                'info',
                "/frms/form/{$form->id}",
                ['form_id' => $form->id, 'frm_no' => $form->frm_no],
                $appId,
                $userTo
            );
        }
    }

    /**
     * Notify when FRMS form status changes
     */
    public static function notifyFormStatusChanged($form, string $oldStatus, string $newStatus, $appId = 4, $userTo = null): void
    {
        $statusLabels = [
            'FA' => 'For Approved',
            'FD' => 'For Disbursement',
            'FL' => 'For Liquidation',
            'A'  => 'Approved',
            'C'  => 'Closed',
            'X'  => 'Canceled',
            // fallback legacy
            'P'  => 'Pending',
            'O'  => 'Open',
        ];

        $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;

        $type = match($newStatus) {
            'A' => 'success',
            'C' => 'info',
            'O' => 'warning',
            default => 'info'
        };

        $creator = User::find($form->user_id);
        $creatorName = $creator ? ($creator->first_name . ' ' . $creator->last_name) : 'User';



        // Notify sitehead user if the creator has one
        if ($creator && $creator->sitehead_user_id) {
            self::create(
                $creator->sitehead_user_id,
                'Team Member Fund Request Status Updated',
                "Fund request {$form->frm_no} by {$creatorName} has been updated by immediate head to {$newStatusLabel}.",
                $type,
                "/frms/form/{$form->id}",
                [
                    'request_no' => $form->frm_no,
                    'request_date' => $form->request_date,
                    'expected_date' => $form->expectedliquidation_date,
                    'purpose' => $form->purpose,
                    'new_status' => $newStatusLabel,
                    'old_status' => $oldStatusLabel,
                    'creator_name' => $creatorName
                ],
                $appId,
                $userTo
            );
        }
    }

    /**
     * Notify when liquidation is due
     */
    public static function notifyLiquidationDue($disbursement, $appId = 4, $userTo = null): void
    {
        $form = $disbursement->form;

        if ($form && $disbursement->expected_liquidation) {
            self::create(
                $form->user_id,
                'Liquidation Due Reminder',
                "Your liquidation for fund request {$form->frm_no} is due on " . date('M d, Y', strtotime($disbursement->expected_liquidation)),
                'warning',
                "/frms/finance-disbursement/{$disbursement->id}",
                [
                    'disbursement_id' => $disbursement->id,
                    'form_id' => $form->id,
                    'frm_no' => $form->frm_no,
                    'due_date' => $disbursement->expected_liquidation
                ],
                $appId,
                $userTo
            );
        }
    }

    /**
     * Notify when document is uploaded
     */
    public static function notifyDocumentUploaded($document, $form, $appId = 4, $userTo = null): void
    {
        self::create(
            $form->user_id,
            'Document Uploaded',
            "A new document '{$document->original_filename}' has been uploaded for fund request {$form->frm_no}.",
            'info',
            "/frms/form/{$form->id}/documents",
            [
                'document_id' => $document->id,
                'form_id' => $form->id,
                'frm_no' => $form->frm_no,
                'filename' => $document->original_filename
            ],
            $appId,
            $userTo
        );

        // Notify finance team about document upload
        $financeUsers = User::where('member_role', 'finance')->pluck('id')->toArray();
        if (!empty($financeUsers)) {
            self::createForUsers(
                $financeUsers,
                'Document Uploaded for Review',
                "A document has been uploaded for fund request {$form->frm_no} and requires review.",
                'info',
                "/frms/form/{$form->id}/documents",
                [
                    'document_id' => $document->id,
                    'form_id' => $form->id,
                    'frm_no' => $form->frm_no,
                    'filename' => $document->original_filename
                ],
                $appId,
                $userTo
            );
        }
    }

    /**
     * General system notification
     */
    public static function notifySystem($form, string $oldStatus, string $newStatus, $appId = 4, $userTo = null): void
    {
        $statusLabels = [
            'FA' => 'For Approved',
            'FD' => 'For Disbursement',
            'FL' => 'For Liquidation',
            'A'  => 'Approved',
            'C'  => 'Closed',
            'X'  => 'Canceled',
            // fallback legacy
            'P'  => 'Pending',
            'O'  => 'Open',
        ];

        $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;

        $type = match($newStatus) {
            'A' => 'success',
            'C' => 'info',
            'O' => 'warning',
            default => 'info'
        };

        $creator = User::find($form->user_id);
        $creatorName = $creator ? ($creator->first_name . ' ' . $creator->last_name) : 'User';

        // Notify the user who created the form
        self::create(
            $form->user_id,
            'Fund Request Status Updated',
            "Fund request {$form->frm_no} by {$creatorName} has been updated by immediate head to {$newStatusLabel}.",
            $type,
            "/frms/form/{$form->id}",
            [
                'request_no' => $form->frm_no,
                'request_date' => $form->request_date,
                'expected_date' => $form->expectedliquidation_date,
                'purpose' => $form->purpose,
                'new_status' => $newStatusLabel,
                'old_status' => $oldStatusLabel,
                'creator_name' => $creatorName
            ],
            $appId,
            $userTo
        );

        // Notify a single admin user (member_role = 5, user_type = admin)
        $admin = User::where('member_role', 5)
            ->where('user_type', 'admin')
            ->orderBy('id')
            ->first(['id']);
        if ($admin) {
            self::create(
                $admin->id,
                'Fund Request Status Updated',
                "Fund request {$form->frm_no} has been updated to {$newStatusLabel}.",
                $type,
                "/frms/form/{$form->id}",
                [
                    'request_no' => $form->frm_no,
                    'request_date' => $form->request_date,
                    'expected_date' => $form->expectedliquidation_date,
                    'purpose' => $form->purpose,
                    'new_status' => $newStatusLabel,
                    'old_status' => $oldStatusLabel,
                    'creator_name' => $creatorName
                ],
                $appId,
                $form->user_id
            );
        }
    }

     public static function notifyUser($form, string $oldStatus, string $newStatus, $appId = 4, $userTo = null): void
    {
        $statusLabels = [
            'FA' => 'For Approved',
            'FD' => 'For Disbursement',
            'FL' => 'For Liquidation',
            'A'  => 'Approved',
            'C'  => 'Closed',
            'X'  => 'Canceled',
            // fallback legacy
            'P'  => 'Pending',
            'O'  => 'Open',
        ];

        $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
        $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;

        $type = match($newStatus) {
            'A' => 'success',
            'C' => 'info',
            'O' => 'warning',
            default => 'info'
        };

        $creator = User::find($form->user_id);
        $creatorName = $creator ? ($creator->first_name . ' ' . $creator->last_name) : 'User';

        // Notify the user who created the form
        self::create(
            $form->user_id,
            'Fund Request Status Updated',
            "Fund request {$form->frm_no} by {$creatorName} has been updated by Finance to {$newStatusLabel}.",
            $type,
            "/frms/form/{$form->id}",
            [
                'request_no' => $form->frm_no,
                'request_date' => $form->request_date,
                'expected_date' => $form->expectedliquidation_date,
                'purpose' => $form->purpose,
                'new_status' => $newStatusLabel,
                'old_status' => $oldStatusLabel,
                'creator_name' => $creatorName
            ],
            $appId,
            $userTo
        );
    }
}
