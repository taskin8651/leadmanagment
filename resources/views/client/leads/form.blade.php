@php($lead = $lead ?? null)

<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-person-vcard"></i>Contact Information</div>
    <div class="form-section-sub">Who is this lead and how do we reach them</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name <span class="req">*</span></label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name',$lead->name??'') }}" required>@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" name="company_name" value="{{ old('company_name',$lead->company_name??'') }}" placeholder="Optional"></div>
        <div class="col-md-6">
            <label class="form-label">Phone <span class="req">*</span></label>
            <div class="input-icon-group"><i class="bi bi-telephone"></i><input class="form-control @error('phone') is-invalid @enderror" name="phone" id="phoneInput" value="{{ old('phone',$lead->phone??'') }}" required autocomplete="off"></div>
            <div id="duplicateHint" class="small mt-1" style="display:none;color:var(--warning)"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <div class="input-icon-group"><i class="bi bi-envelope"></i><input class="form-control" type="email" name="email" value="{{ old('email',$lead->email??'') }}"></div>
        </div>
    </div>
</div>

<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-clipboard-data"></i>Lead Details</div>
    <div class="form-section-sub">Where this lead came from and how it's progressing</div>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Source <span class="req">*</span></label><input class="form-control" name="source" value="{{ old('source',$lead->source??'Website') }}" list="sourceList" required>
            <datalist id="sourceList"><option>Website</option><option>Referral</option><option>Ads</option><option>Walk-in</option><option>Social Media</option><option>WhatsApp Ads</option><option>Facebook Lead Ads</option><option>Google Ads Lead Form</option></datalist>
        </div>
        <div class="col-md-4"><label class="form-label">Status <span class="req">*</span></label><select class="form-select" name="status">@foreach(['new','contacted','qualified','follow-up','won','lost'] as $s)<option value="{{ $s }}" @selected(old('status',$lead->status??'new')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Priority <span class="req">*</span></label><select class="form-select" name="priority">@foreach(['low','medium','high','hot'] as $s)<option value="{{ $s }}" @selected(old('priority',$lead->priority??'medium')==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Estimated Value</label><div class="input-icon-group"><i class="bi bi-currency-rupee"></i><input class="form-control" type="number" step="0.01" min="0" name="estimated_value" value="{{ old('estimated_value',$lead->estimated_value??'') }}"></div></div>
    </div>
</div>

@if(isset($tags))
<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-tags"></i>Tags</div>
    <div class="form-section-sub">Organize leads with your own labels</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Existing Tags</label>
            <select class="form-select" name="tags[]" multiple size="4">
                @php($selectedTags = old('tags', isset($lead) ? $lead->tags->pluck('id')->all() : []))
                @foreach($tags as $t)<option value="{{ $t->id }}" @selected(in_array($t->id, $selectedTags))>{{ $t->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">New Tags</label>
            <input class="form-control" name="new_tags" placeholder="e.g. Hot Deal, Referral Q1 (comma separated)">
            <div class="form-section-sub mt-1 mb-0">New tags are created automatically when you save.</div>
        </div>
    </div>
</div>
@endif

@if(isset($fieldDefinitions) && $fieldDefinitions->isNotEmpty())
<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-input-cursor-text"></i>Additional Details</div>
    <div class="form-section-sub">Custom fields configured for your account</div>
    <div class="row g-3">
        @foreach($fieldDefinitions as $def)
            <div class="col-md-6">
                <label class="form-label">{{ $def->label }}</label>
                <input class="form-control" type="{{ $def->type === 'number' ? 'number' : ($def->type === 'date' ? 'date' : 'text') }}" name="custom[{{ $def->key }}]" value="{{ old('custom.'.$def->key, $lead->custom_fields[$def->key] ?? '') }}">
            </div>
        @endforeach
    </div>
</div>
@endif

@if(isset($assignees) && $assignees->isNotEmpty())
<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-person-check"></i>Assignment</div>
    <div class="form-section-sub">Which team member owns this lead</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Assigned To</label>
            <select class="form-select" name="assigned_to">
                <option value="">Unassigned</option>
                @foreach($assignees as $u)<option value="{{ $u->id }}" @selected(old('assigned_to',$lead->assigned_to??'')==$u->id)>{{ $u->name }} ({{ $u->roles->first()->name ?? 'Owner' }})</option>@endforeach
            </select>
        </div>
    </div>
</div>
@endif

<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-calendar-event"></i>Follow-up</div>
    <div class="form-section-sub">Schedule the next touchpoint</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Next Follow-up</label><input class="form-control" type="datetime-local" name="next_follow_up_at" value="{{ old('next_follow_up_at',isset($lead)&&$lead->next_follow_up_at?$lead->next_follow_up_at->format('Y-m-d\TH:i'):'') }}"></div>
    </div>
</div>

<div class="card p-4 form-section fade-up">
    <div class="form-section-title"><i class="bi bi-journal-text"></i>Notes</div>
    <div class="form-section-sub">Anything worth remembering about this lead</div>
    <textarea class="form-control" rows="4" name="notes" placeholder="Add context, requirements, objections…">{{ old('notes',$lead->notes??'') }}</textarea>
</div>
