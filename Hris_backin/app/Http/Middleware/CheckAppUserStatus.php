<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Core\CoreAppUser;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAppUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $appId = null): Response
    {
        if (Auth::check()) {
            $userId = Auth::id();

            // Resolve appId: prefer explicit middleware parameter; fallback to route parameter if provided
            if (!$appId) {
                $routeAppId = $request->route('app_id');
                if ($routeAppId) {
                    $appId = $routeAppId;
                }
            }

            // If appId is still not resolved, deny access as we can't validate
            if (!$appId) {
                abort(404);
            }

            // Check if user is active for this app
            $appUser = CoreAppUser::where('user_id', $userId)
                ->where('app_id', $appId)
                ->first();

            if (!$appUser || $appUser->is_active == 0) {
                // User is not active for this app, redirect to 404 page
                abort(404);
            }
        }

        return $next($request);
    }
}
