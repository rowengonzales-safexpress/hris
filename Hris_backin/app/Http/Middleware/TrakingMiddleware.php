<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AdminController;



class TrakingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $adminController = new AdminController();
            $userMenus = $adminController->getUserMenusData($userId, 2);
            $branchName = $adminController->getBranchName(auth()->user()->branch_id);
            Inertia::share('userMenus', $userMenus);
            Inertia::share('branchName', $branchName);
        }

        return $next($request);
    }
}
