<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact {{ $client->company_name }}</title>
<link href="{{ asset('assets/vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
@if($recaptchaEnabled)<script src="https://www.google.com/recaptcha/api.js" async defer></script>@endif
<style>
body{min-height:100vh;display:grid;place-items:center;background:var(--background);padding:24px}
.box{width:min(480px,100%);background:#fff;border:1px solid var(--border);border-radius:var(--radius-xl);box-shadow:var(--shadow-lg);padding:36px}
</style>
</head>
<body>
<div class="box fade-up">
    <div class="text-center mb-4">
        <span class="d-inline-grid" style="width:48px;height:48px;border-radius:14px;background:var(--primary-soft);color:var(--primary);place-items:center;font-size:22px"><i class="bi bi-buildings"></i></span>
        <h4 class="fw-800 mt-3 mb-1">{{ $client->company_name }}</h4>
        <p class="muted mb-0" style="font-size:13.3px">Leave your details and we'll get back to you shortly.</p>
    </div>

    @if(session('submitted'))
        <div class="text-center py-4">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;border-radius:16px;background:var(--success-soft);color:var(--success);font-size:26px"><i class="bi bi-check-lg"></i></div>
            <h5 class="fw-bold mb-1">Thank you!</h5>
            <p class="muted mb-0">We've received your details and will be in touch soon.</p>
        </div>
    @else
        @if($errors->any())
            <div class="alert border-0 mb-3" style="background:var(--danger-soft);color:#b91c1c;border-radius:12px">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('public.lead-form.submit',$client) }}">
            @csrf
            <label class="form-label">Name <span class="req">*</span></label>
            <input class="form-control mb-3" name="name" value="{{ old('name') }}" required>
            <label class="form-label">Phone <span class="req">*</span></label>
            <div class="input-icon-group mb-3"><i class="bi bi-telephone"></i><input class="form-control" name="phone" value="{{ old('phone') }}" required></div>
            <label class="form-label">Email</label>
            <div class="input-icon-group mb-3"><i class="bi bi-envelope"></i><input class="form-control" type="email" name="email" value="{{ old('email') }}"></div>
            <label class="form-label">Company</label>
            <input class="form-control mb-3" name="company_name" value="{{ old('company_name') }}" placeholder="Optional">
            <label class="form-label">Message</label>
            <textarea class="form-control mb-3" name="notes" rows="3" placeholder="How can we help?">{{ old('notes') }}</textarea>
            @if($recaptchaEnabled)<div class="g-recaptcha mb-3" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>@endif
            <button class="btn btn-primary w-100 py-2">Submit</button>
        </form>
    @endif
</div>
</body>
</html>
