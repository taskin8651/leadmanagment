<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditLog::create(['action' => 'login_failed', 'description' => 'Failed login for ' . $credentials['email'], 'ip_address' => $request->ip(), 'created_at' => now()]);
            return back()->withErrors(['email' => 'Invalid login credentials.'])->withInput();
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'Your account has been suspended. Please contact your administrator.'])->withInput();
        }

        if ($user->hasAnyRole(['Admin', 'Staff', 'Telecaller'])) {
            $client = $user->client;
            if (!$client || !$client->canAccess()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Your account is inactive. Please contact your administrator.',
                ])->withInput();
            }
        }

        $request->session()->regenerate();
        AuditLog::record('login', $user->name . ' logged in');

        return redirect()->route('client.dashboard');
    }

    public function logout(Request $request)
    {
        AuditLog::record('logout', Auth::user()?->name . ' logged out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}