<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        if (auth()->user()?->hasVerifiedEmail()) {
            return $this->redirectHome();
        }
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return $this->redirectHome()->with('success', 'Your email has been verified.');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectHome();
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent. Please check your inbox.');
    }

    private function redirectHome()
    {
        return redirect()->route('client.dashboard');
    }
}
