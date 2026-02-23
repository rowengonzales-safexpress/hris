<?php

namespace App\Http\Middleware;

use Closure;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;



class AdminMiddleware
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
            $userMenus = $adminController->getUserMenusData($userId, 1);

            Inertia::share('userMenus', $userMenus);
        }

        return $next($request);


    }


}
