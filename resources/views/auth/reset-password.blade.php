<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password — LeadFlow CRM</title>
<link href="{{ asset('assets/vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
<style>body{min-height:100vh;display:grid;place-items:center;background:var(--background);padding:24px}.box{width:min(420px,100%);background:#fff;border:1px solid var(--border);border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);padding:36px}</style>
</head>
<body>
<div class="box fade-up">
    <h3 class="fw-800 mb-1">Set a new password</h3>
    <p class="muted mb-4" style="font-size:13.3px">Choose a strong password for your account.</p>

    @if($errors->any())
        <div class="alert border-0 mb-3" style="background:var(--danger-soft);color:#b91c1c;border-radius:12px">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="form-label">Email</label>
        <div class="input-icon-group mb-3"><i class="bi bi-envelope"></i><input class="form-control" type="email" name="email" value="{{ old('email', $email) }}" required></div>
        <label class="form-label">New Password</label>
        <div class="input-icon-group mb-3"><i class="bi bi-lock"></i><input class="form-control" type="password" name="password" required></div>
        <label class="form-label">Confirm New Password</label>
        <div class="input-icon-group mb-3"><i class="bi bi-lock"></i><input class="form-control" type="password" name="password_confirmation" required></div>
        <button class="btn btn-primary w-100 py-2">Reset password</button>
    </form>
</div>
</body>
</html>
