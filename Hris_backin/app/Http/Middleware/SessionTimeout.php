<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity');
            $lifetimeMinutes = (int) config('session.lifetime');

            if ($lastActivity && ((time() - (int) $lastActivity) > ($lifetimeMinutes * 60))) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login', ['expired' => 1]);
            }

            $request->session()->put('last_activity', time());
        }

        return $next($request);
    }
}

