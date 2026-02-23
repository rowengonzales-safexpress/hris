<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AdminController;

class FrmsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $adminController = new AdminController();
            // App ID 4 reserved for FRMS
            $userMenus = $adminController->getUserMenusData($userId, 4);
            $branchName = $adminController->getBranchName(auth()->user()->branch_id);
            Inertia::share('userMenus', $userMenus);
            Inertia::share('branchName', $branchName);
        }

        return $next($request);
    }
}
