<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureClientActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user->is_active) {
            return $this->deny($request, 'Your account has been suspended. Please contact your administrator.');
        }

        $client = $user->client;

        if (!$client || !$client->canAccess()) {
            return $this->deny($request, 'Your account is inactive. Please contact your administrator.');
        }

        return $next($request);
    }

    private function deny(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], 403);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
