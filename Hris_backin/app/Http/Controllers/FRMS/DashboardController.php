<?php

namespace App\Http\Controllers\FRMS;

use App\Http\Controllers\Controller;
use App\Models\FRMS\Form;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Safeguard if migrations haven't been run yet
        if (!Schema::hasTable('frms_form')) {
            $summary = [
                'requests' => 0,
                'approval' => 0,
                'disbursement' => 0,
                'liquidation' => 0,
                'review' => 0

            ];
            $recentRequests = [];
        } else {
            $user = auth()->user();
            $canViewAll = $user && $user->member_role == 5 && in_array($user->user_type, ['admin', 'user']);

            $formQuery = Form::query();
            if (!$canViewAll) {
                $formQuery->where('user_id', auth()->id());
            }

            $summary = [
                'requests' => (clone $formQuery)->count(),
                'approval' => (clone $formQuery)->where('status_request', 'FA')->count(),
                'disbursement' => (clone $formQuery)->where('status_request', 'FD')->count(),
                'liquidation' => (clone $formQuery)->where('status_request', 'FL')->count(),
                'review' => (clone $formQuery)->count(),
                'total_requests' => (clone $formQuery)->count(),
                'pending' => (clone $formQuery)->where('status_request', 'FA')->count(),
                'approved' => (clone $formQuery)->where('status_request', 'A')->count(),
                'disbursed' => (clone $formQuery)->whereHas('disbursements')->count(),
            ];

            // Get recent fund requests (Top 5)
            $recentRequests = $this->getRecentFundRequests($canViewAll);
        }

        return Inertia::render('FRMS/Dashboard', [
            'summary' => $summary,
            'recentRequests' => $recentRequests,
        ]);
    }

    /**
     * Get recent fund requests for dashboard display
     */
    private function getRecentFundRequests($canViewAll = false)
    {
        $query = Form::with(['user', 'branch', 'items'])
            ->orderByDesc('created_at')
            ->limit(5);

        if (!$canViewAll) {
            $query->where('user_id', auth()->id());
        }

        return $query->get()
            ->map(function ($form) {
                // Calculate total amount from items
                $totalAmount = $form->items->sum('amount');

                // Get requester name
                $requesterName = $form->user
                    ? ($form->user->first_name . ' ' . $form->user->last_name)
                    : 'Unknown User';

                // Get department/branch name
                $department = $form->branch ? $form->branch->branch_name : 'Unknown Branch';

                // Format status
                $status = $this->formatStatus($form->status_request);

                return [
                    'request_number' => $form->frm_no,
                    'requester_name' => $requesterName,
                    'department' => $department,
                    'amount' => $totalAmount,
                    'status' => $status,
                    'created_date' => $form->created_at->format('M d, Y'),
                ];
            })
            ->toArray();
    }

    /**
     * Format status for display
     */
    private function formatStatus($statusCode)
    {
        $statusMap = [
            'FA' => 'For Approved',
            'FD' => 'For Disbursement',
            'FL' => 'For Liquidation',
            'A'  => 'Approved',
            'C'  => 'Closed',
            'X'  => 'Canceled',
        ];

        return $statusMap[$statusCode] ?? 'Unknown';
    }
}
