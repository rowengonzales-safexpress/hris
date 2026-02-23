<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingHeader;
use App\Models\TrackingEvent;
use App\Models\TrackingDocument;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        $usersTotal = User::whereNull('deleted_at')->count();
        $usersActive = User::where('status', 'A')->whereNull('deleted_at')->count();
        $usersInactive = User::where('status', 'I')->whereNull('deleted_at')->count();
        $usersCancelled = User::where('status', 'C')->whereNull('deleted_at')->count();
        $usersPending = User::where('status', 'P')->whereNull('deleted_at')->count();
        $admins = User::where('user_type', 'admin')->count();
        $standardUsers = User::where('user_type', 'user')->count();

        $recentUsers = User::orderByDesc('created_at')
            ->take(10)
            ->get(['id','name','email','user_type','status','created_at']);

        return Inertia::render('Admin/Dashboard', [
            'usersSummary' => [
                'total' => $usersTotal,
                'active' => $usersActive,
                'inactive' => $usersInactive,
                'cancelled' => $usersCancelled,
                'pending' => $usersPending,
                'admins' => $admins,
                'users' => $standardUsers,
            ],
            'recentUsers' => $recentUsers,
        ]);
    }


}
