@php($staff = $staff ?? null)
<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-person-badge"></i>Staff Details</div>
    <div class="form-section-sub">This person will be able to sign in and manage your leads and follow-ups</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name <span class="req">*</span></label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name',$staff->name??'') }}" required>@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
        <div class="col-md-6">
            <label class="form-label">Email <span class="req">*</span></label>
            <div class="input-icon-group"><i class="bi bi-envelope"></i><input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email',$staff->email??'') }}" required></div>
            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Password @if(!$staff)<span class="req">*</span>@else<span class="muted fw-normal">(leave blank to keep current)</span>@endif</label>
            <div class="input-icon-group"><i class="bi bi-lock"></i><input class="form-control @error('password') is-invalid @enderror" type="password" name="password" @if(!$staff) required @endif></div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Role Label <span class="req">*</span></label>
            <select class="form-select" name="role">
                @foreach($roles as $r)<option value="{{ $r }}" @selected(old('role',$staff?->roles?->first()?->name??'Staff')==$r)>{{ $r }}</option>@endforeach
            </select>
            <div class="form-section-sub mt-1 mb-0">Just a label for your team — actual access is controlled below.</div>
        </div>
    </div>
</div>

<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-shield-check"></i>Access Permissions</div>
    <div class="form-section-sub">Choose exactly what this person can do — you decide, nothing is assumed</div>
    <div class="row g-2">
        @php($selected = old('permissions', $staff?->permissions?->pluck('name')->all() ?? ['create_leads','edit_leads','complete_followups']))
        @foreach($permissionList as $key => $label)
            <div class="col-md-6">
                <label class="d-flex align-items-center gap-2 p-2 rounded-3" style="border:1px solid var(--border);cursor:pointer">
                    <input class="form-check-input m-0" type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key,$selected))>
                    <span style="font-size:13.3px">{{ $label }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
