<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is logged in but their account is flagged as inactive
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect to login with a clear error message
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact IT support.'
            ]);
        }

        return $next($request);
    }
}
