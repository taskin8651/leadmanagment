<?php

namespace App\Http\Controllers\Client;

use App\Exports\LeadsExport;
use App\Http\Controllers\Controller;
use App\Mail\LeadEmail;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\LeadAttachment;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\LeadAssigned;
use App\Services\ElevenZaWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    private function client()
    {
        return Auth::user()->client;
    }

    private function isOwner(): bool
    {
        return Auth::user()->hasRole('Admin');
    }

    private function scopeVisible($query)
    {
        $query->where('client_id', $this->client()->id);
        if (!$this->isOwner()) {
            $query->where(fn ($q) => $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to'));
        }
        return $query;
    }

    private function authorizeLead(Lead $lead): void
    {
        $visible = $lead->client_id === $this->client()->id
            && ($this->isOwner() || $lead->assigned_to === Auth::id() || is_null($lead->assigned_to));
        abort_unless($visible, 403);
    }

    public function index(Request $request)
    {
        $query = $this->scopeVisible(Lead::query())->with('assignee', 'tags')->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('tag')) $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->tag));
        $leads = $query->paginate(15)->withQueryString();

        $kpis = [
            'total' => $this->scopeVisible(Lead::query())->count(),
            'new' => $this->scopeVisible(Lead::query())->where('status', 'new')->count(),
            'contacted' => $this->scopeVisible(Lead::query())->where('status', 'contacted')->count(),
            'qualified' => $this->scopeVisible(Lead::query())->where('status', 'qualified')->count(),
            'follow-up' => $this->scopeVisible(Lead::query())->where('status', 'follow-up')->count(),
            'won' => $this->scopeVisible(Lead::query())->where('status', 'won')->count(),
            'lost' => $this->scopeVisible(Lead::query())->where('status', 'lost')->count(),
        ];

        $tags = $this->client()->tags()->orderBy('name')->get();
        $assignees = $this->isOwner() ? $this->assignableUsers() : collect();

        return view('client.leads.index', compact('leads', 'kpis', 'tags', 'assignees'));
    }

    public function export(Request $request)
    {
        $query = $this->scopeVisible(Lead::query())->with('client');
        if ($request->filled('ids')) {
            $query->whereIn('id', $request->input('ids'));
        } else {
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
            }
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('tag')) $query->whereHas('tags', fn ($q) => $q->where('tags.id', $request->tag));
        }
        $leads = $query->get();
        return Excel::download(new LeadsExport($leads), 'leads-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    private function assignableUsers()
    {
        return User::where('client_id', $this->client()->id)->where('is_active', true)->orderBy('name')->get();
    }

    public function create()
    {
        abort_unless(Auth::user()->can('create_leads'), 403);
        return view('client.leads.create', [
            'assignees' => $this->isOwner() ? $this->assignableUsers() : collect(),
            'tags' => $this->client()->tags()->orderBy('name')->get(),
            'fieldDefinitions' => $this->client()->customFieldDefinitions()->get(),
        ]);
    }

    public function checkDuplicate(Request $request)
    {
        $phone = $request->get('phone');
        if (!$phone) return response()->json(['duplicate' => null]);

        $lead = Lead::where('client_id', $this->client()->id)->where('phone', $phone)
            ->when($request->get('exclude'), fn ($q, $id) => $q->where('id', '!=', $id))
            ->first();

        return response()->json([
            'duplicate' => $lead ? ['id' => $lead->id, 'name' => $lead->name, 'url' => route('client.leads.show', $lead)] : null,
        ]);
    }

    private function syncCustomFields(Request $request): ?array
    {
        $defs = $this->client()->customFieldDefinitions()->pluck('key');
        if ($defs->isEmpty()) return null;
        $values = [];
        foreach ($defs as $key) {
            if ($request->has("custom.$key")) $values[$key] = $request->input("custom.$key");
        }
        return $values;
    }

    private function syncFollowUpDate(Lead $lead, ?string $newDate, ?string $previousDate): void
    {
        if (!$newDate) return;
        if ($previousDate && \Carbon\Carbon::parse($newDate)->equalTo(\Carbon\Carbon::parse($previousDate))) return;

        $exists = $lead->followUps()->where('status', 'pending')
            ->whereDate('follow_up_at', \Carbon\Carbon::parse($newDate)->toDateString())
            ->exists();
        if ($exists) return;

        $lead->followUps()->create([
            'follow_up_at' => $newDate,
            'type' => 'Call',
            'assigned_to' => $lead->assigned_to ?: Auth::id(),
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);
        $lead->logActivity('note', 'Follow-up scheduled for ' . \Carbon\Carbon::parse($newDate)->format('d M Y, h:i A'));
    }

    private function syncTags(Lead $lead, Request $request): void
    {
        $ids = collect($request->input('tags', []))->filter()->map(fn ($id) => (int) $id);
        foreach (array_filter(array_map('trim', explode(',', (string) $request->input('new_tags')))) as $name) {
            $tag = Tag::firstOrCreate(['client_id' => $this->client()->id, 'name' => $name], ['color' => 'primary']);
            $ids->push($tag->id);
        }
        $validIds = Tag::where('client_id', $this->client()->id)->whereIn('id', $ids)->pluck('id');
        $lead->tags()->sync($validIds);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->can('create_leads'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'source' => ['required', 'string', 'max:80'],
            'status' => ['required', 'in:new,contacted,qualified,follow-up,won,lost'],
            'priority' => ['required', 'in:low,medium,high,hot'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'next_follow_up_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);
        $data['lead_number'] = 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT);
        $data['client_id'] = $this->client()->id;
        $data['created_by'] = Auth::id();
        $data['assigned_to'] = $this->isOwner() ? ($data['assigned_to'] ?? null) : Auth::id();
        $data['custom_fields'] = $this->syncCustomFields($request);

        if (empty($data['assigned_to'])) {
            $next = $this->client()->nextAssignee();
            if ($next) $data['assigned_to'] = $next->id;
        }

        $lead = Lead::create($data);
        $lead->logActivity('created', 'Lead created via ' . $data['source']);
        if ($lead->assigned_to) {
            $lead->logActivity('assigned', $lead->assignee->name . ' assigned to this lead');
            $lead->assignee->notify(new LeadAssigned($lead));
        }
        $this->syncTags($lead, $request);
        $this->syncFollowUpDate($lead, $data['next_follow_up_at'] ?? null, null);
        $lead->recalculateScore();

        return redirect()->route('client.leads.index')->with('success', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $this->authorizeLead($lead);
        $lead->load('followUps', 'assignee', 'tags', 'activities.user', 'attachments.uploader', 'invoices');
        return view('client.leads.show', [
            'lead' => $lead,
            'duplicates' => $lead->duplicates(),
            'fieldDefinitions' => $this->client()->customFieldDefinitions()->get(),
        ]);
    }

    public function edit(Lead $lead)
    {
        $this->authorizeLead($lead);
        abort_unless(Auth::user()->can('edit_leads'), 403);
        return view('client.leads.edit', [
            'lead' => $lead,
            'assignees' => $this->isOwner() ? $this->assignableUsers() : collect(),
            'tags' => $this->client()->tags()->orderBy('name')->get(),
            'fieldDefinitions' => $this->client()->customFieldDefinitions()->get(),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        abort_unless(Auth::user()->can('edit_leads'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'company_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email'], 'phone' => ['required', 'string', 'max:30'],
            'source' => ['required', 'string', 'max:80'], 'status' => ['required', 'in:new,contacted,qualified,follow-up,won,lost'],
            'priority' => ['required', 'in:low,medium,high,hot'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'], 'next_follow_up_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);
        if (!$this->isOwner()) {
            unset($data['assigned_to']);
        }
        $data['custom_fields'] = $this->syncCustomFields($request);

        $originalStatus = $lead->status;
        $originalAssignee = $lead->assigned_to;
        $originalFollowUpAt = optional($lead->next_follow_up_at)->toIso8601String();

        $lead->update($data);

        if (isset($data['status']) && $data['status'] !== $originalStatus) {
            $lead->logActivity('status_change', ucfirst($originalStatus) . ' → ' . ucfirst($data['status']), ['from' => $originalStatus, 'to' => $data['status']]);
        }
        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] != $originalAssignee) {
            $lead->logActivity('assigned', $lead->assignee ? $lead->assignee->name . ' assigned to this lead' : 'Unassigned');
            if ($lead->assignee) {
                $lead->assignee->notify(new LeadAssigned($lead));
            }
        }
        $this->syncTags($lead, $request);
        $this->syncFollowUpDate($lead, $data['next_follow_up_at'] ?? null, $originalFollowUpAt);
        $lead->recalculateScore();

        return redirect()->route('client.leads.index')->with('success', 'Lead updated.');
    }

    public function status(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        abort_unless(Auth::user()->can('edit_leads'), 403);
        $request->validate(['status' => 'required|in:new,contacted,qualified,follow-up,won,lost']);
        $old = $lead->status;
        $lead->update(['status' => $request->status]);
        if ($old !== $request->status) {
            $lead->logActivity('status_change', ucfirst($old) . ' → ' . ucfirst($request->status), ['from' => $old, 'to' => $request->status]);
        }
        return back()->with('success', 'Lead status updated.');
    }

    public function addNote(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        $lead->logActivity('note', $data['note']);
        $lead->markFirstResponse();
        return back()->with('success', 'Note added.');
    }

    public const CALL_OUTCOMES = ['Connected', 'Not Answered', 'Busy', 'Switched Off', 'Wrong Number', 'Call Back Later'];

    public function logInteraction(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $data = $request->validate([
            'type' => ['required', 'in:call,whatsapp,email'],
            'outcome' => ['nullable', 'string', 'max:500'],
        ]);
        $lead->logActivity(
            $data['type'],
            ($data['outcome'] ?? null) ?: ucfirst($data['type']) . ' logged',
            $data['type'] === 'call' && !empty($data['outcome']) ? ['outcome' => $data['outcome']] : []
        );
        $lead->markFirstResponse();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', ucfirst($data['type']) . ' logged.');
    }

    public function sendEmail(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        abort_unless($lead->email, 422);
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Mail::to($lead->email)->send(new LeadEmail($data['subject'], $data['body'], Auth::user()->name));
        $lead->logActivity('email', $data['subject'], ['subject' => $data['subject']]);
        $lead->markFirstResponse();

        return back()->with('success', 'Email sent.');
    }

    public function sendWhatsApp(Request $request, Lead $lead, ElevenZaWhatsAppService $whatsApp)
    {
        $this->authorizeLead($lead);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $whatsApp->sendMessage($lead->phone, $data['message']);
        } catch (\Throwable $e) {
            return back()->withErrors(['whatsapp' => $e->getMessage()]);
        }

        $lead->logActivity('whatsapp', $data['message']);
        $lead->markFirstResponse();

        return back()->with('success', 'WhatsApp message sent.');
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeLead($lead);
        abort_unless(Auth::user()->can('delete_leads'), 403);
        AuditLog::record('lead.deleted', "Lead {$lead->name} ({$lead->lead_number}) deleted", [], $lead);
        $lead->delete();
        return back()->with('success', 'Lead deleted.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:delete,status,reassign'],
            'status' => ['required_if:action,status', 'nullable', 'in:new,contacted,qualified,follow-up,won,lost'],
            'assigned_to' => ['required_if:action,reassign', 'nullable', 'exists:users,id'],
        ]);

        $leads = $this->scopeVisible(Lead::query())->whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            abort_unless(Auth::user()->can('delete_leads'), 403);
            $count = $leads->count();
            AuditLog::record('lead.bulk_deleted', "$count lead(s) bulk deleted");
            $leads->delete();
            return back()->with('success', "$count lead(s) deleted.");
        }

        if ($data['action'] === 'reassign') {
            abort_unless($this->isOwner(), 403);
            $assignee = $this->assignableUsers()->firstWhere('id', (int) $data['assigned_to']);
            abort_unless($assignee, 422);
            $count = 0;
            foreach ($leads->get() as $lead) {
                $lead->update(['assigned_to' => $assignee->id]);
                $lead->logActivity('assigned', $assignee->name . ' assigned to this lead');
                $assignee->notify(new LeadAssigned($lead));
                $count++;
            }
            return back()->with('success', "$count lead(s) reassigned to {$assignee->name}.");
        }

        abort_unless(Auth::user()->can('edit_leads'), 403);
        $count = $leads->count();
        $leads->update(['status' => $data['status']]);
        return back()->with('success', "$count lead(s) updated to " . ucfirst($data['status']) . '.');
    }

    public function trash(Request $request)
    {
        abort_unless(Auth::user()->can('delete_leads'), 403);
        $query = Lead::onlyTrashed()->where('client_id', $this->client()->id)->latest('deleted_at');
        if (!$this->isOwner()) {
            $query->where(fn ($q) => $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to'));
        }
        $leads = $query->paginate(15);
        return view('client.leads.trash', compact('leads'));
    }

    public function restore(int $id)
    {
        abort_unless(Auth::user()->can('delete_leads'), 403);
        $lead = Lead::onlyTrashed()->where('client_id', $this->client()->id)->findOrFail($id);
        $lead->restore();
        AuditLog::record('lead.restored', "Lead {$lead->name} ({$lead->lead_number}) restored", [], $lead);
        return back()->with('success', 'Lead restored.');
    }

    public function forceDelete(int $id)
    {
        abort_unless($this->isOwner(), 403);
        $lead = Lead::onlyTrashed()->where('client_id', $this->client()->id)->findOrFail($id);
        AuditLog::record('lead.force_deleted', "Lead {$lead->name} ({$lead->lead_number}) permanently deleted", [], $lead);
        $lead->forceDelete();
        return back()->with('success', 'Lead permanently deleted.');
    }

    public function storeAttachment(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv'],
        ]);

        $file = $data['file'];
        $path = $file->store('lead-attachments/' . $this->client()->id . '/' . $lead->id, 'local');

        $attachment = $lead->attachments()->create([
            'uploaded_by' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        $lead->logActivity('note', 'File uploaded: ' . $attachment->original_name);

        return back()->with('success', 'File uploaded.');
    }

    public function downloadAttachment(Lead $lead, LeadAttachment $attachment)
    {
        $this->authorizeLead($lead);
        abort_unless($attachment->lead_id === $lead->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroyAttachment(Lead $lead, LeadAttachment $attachment)
    {
        $this->authorizeLead($lead);
        abort_unless($attachment->lead_id === $lead->id, 404);
        abort_unless($this->isOwner() || $attachment->uploaded_by === Auth::id(), 403);

        Storage::disk('local')->delete($attachment->path);
        $lead->logActivity('note', 'File removed: ' . $attachment->original_name);
        $attachment->delete();

        return back()->with('success', 'File removed.');
    }

    public function merge(Request $request, Lead $lead)
    {
        abort_unless($this->isOwner(), 403);
        $this->authorizeLead($lead);
        $data = $request->validate(['duplicate_id' => ['required', 'integer']]);
        $duplicate = Lead::where('client_id', $this->client()->id)->findOrFail($data['duplicate_id']);
        abort_if($duplicate->id === $lead->id, 422);

        $duplicate->followUps()->update(['lead_id' => $lead->id]);
        $duplicate->activities()->update(['lead_id' => $lead->id]);
        $lead->tags()->syncWithoutDetaching($duplicate->tags->pluck('id'));
        $lead->logActivity('note', 'Merged duplicate lead ' . $duplicate->lead_number . ' (' . $duplicate->name . ') into this one.');
        AuditLog::record('lead.merged', "Lead {$duplicate->lead_number} merged into {$lead->lead_number}", [], $lead);
        $duplicate->delete();

        return redirect()->route('client.leads.show', $lead)->with('success', 'Duplicate lead merged.');
    }
}
