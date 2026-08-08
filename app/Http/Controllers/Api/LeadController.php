<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function index(Request $request)
    {
        $leads = $this->scopeVisible(Lead::query())->with('assignee')->latest()->paginate(20);
        return response()->json($leads);
    }

    public function show(Lead $lead)
    {
        abort_unless($lead->client_id === $this->client()->id, 404);
        $lead->load('followUps', 'assignee');
        return response()->json($lead);
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
            'status' => ['sometimes', 'in:new,contacted,qualified,follow-up,won,lost'],
            'priority' => ['sometimes', 'in:low,medium,high,hot'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['lead_number'] = 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT);
        $data['status'] = $data['status'] ?? 'new';
        $data['priority'] = $data['priority'] ?? 'medium';
        $data['client_id'] = $this->client()->id;
        $data['created_by'] = Auth::id();
        $data['assigned_to'] = $this->isOwner() ? null : Auth::id();

        $lead = Lead::create($data);
        return response()->json($lead, 201);
    }

    public function update(Request $request, Lead $lead)
    {
        abort_unless($lead->client_id === $this->client()->id, 404);
        abort_unless(Auth::user()->can('edit_leads'), 403);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email'],
            'phone' => ['sometimes', 'string', 'max:30'],
            'source' => ['sometimes', 'string', 'max:80'],
            'status' => ['sometimes', 'in:new,contacted,qualified,follow-up,won,lost'],
            'priority' => ['sometimes', 'in:low,medium,high,hot'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
        $lead->update($data);
        return response()->json($lead);
    }

    public function destroy(Lead $lead)
    {
        abort_unless($lead->client_id === $this->client()->id, 404);
        abort_unless(Auth::user()->can('delete_leads'), 403);
        $lead->delete();
        return response()->json(['success' => true]);
    }
}
