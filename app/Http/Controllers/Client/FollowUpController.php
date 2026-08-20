<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    private function authorizeLead(Lead $lead): void
    {
        $client = Auth::user()->client;
        $isOwner = Auth::user()->hasRole('Admin');
        $visible = $lead->client_id === $client->id
            && ($isOwner || $lead->assigned_to === Auth::id() || is_null($lead->assigned_to));
        abort_unless($visible, 403);
    }

    public function index(Request $request)
    {
        $clientId = Auth::user()->client->id;
        $isOwner = Auth::user()->hasRole('Admin');
        $query = FollowUp::with('lead')
            ->whereHas('lead', fn ($q) => $q->where('client_id', $clientId));
        if (!$isOwner) {
            $query->where(fn ($q) => $q->where('assigned_to', Auth::id())->orWhereNull('assigned_to'));
        }
        $query->when($request->status, fn ($q, $s) => $q->where('status', $s));
        match ($request->get('when')) {
            'today' => $query->whereDate('follow_up_at', today()),
            'overdue' => $query->where('follow_up_at', '<', now())->where('status', 'pending'),
            'upcoming' => $query->where('follow_up_at', '>', now()->endOfDay()),
            default => null,
        };
        $followUps = $query->orderBy('follow_up_at')->paginate(20)->withQueryString();
        return view('client.follow-ups.index', compact('followUps'));
    }

    public function calendar(Request $request)
    {
        $clientId = Auth::user()->client->id;
        $month = $request->filled('month') ? \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth() : now()->startOfMonth();

        $followUps = FollowUp::with('lead')
            ->whereHas('lead', fn ($q) => $q->where('client_id', $clientId))
            ->whereBetween('follow_up_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderBy('follow_up_at')
            ->get()
            ->groupBy(fn ($f) => $f->follow_up_at->format('Y-m-d'));

        return view('client.follow-ups.calendar', compact('followUps', 'month'));
    }

    public function store(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        abort_unless(Auth::user()->can('edit_leads'), 403);

        $data = $request->validate([
            'follow_up_at' => ['required', 'date'],
            'type' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['lead_id'] = $lead->id;
        $data['assigned_to'] = $lead->assigned_to ?: Auth::id();
        $data['created_by'] = Auth::id();
        $data['status'] = 'pending';

        FollowUp::create($data);

        if (!$lead->next_follow_up_at || $data['follow_up_at'] < $lead->next_follow_up_at) {
            $lead->update(['next_follow_up_at' => $data['follow_up_at']]);
        }
        $lead->logActivity('note', 'Follow-up scheduled for ' . \Carbon\Carbon::parse($data['follow_up_at'])->format('d M Y, h:i A'));

        return back()->with('success', 'Follow-up scheduled.');
    }

    public function reschedule(Request $request, FollowUp $followUp)
    {
        abort_unless($followUp->lead->client_id === Auth::user()->client->id, 403);
        abort_unless(Auth::user()->can('edit_leads'), 403);
        abort_unless($followUp->status === 'pending', 422);

        $data = $request->validate([
            'follow_up_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $followUp->follow_up_at;
        $followUp->update([
            'follow_up_at' => $data['follow_up_at'],
            'reschedule_count' => $followUp->reschedule_count + 1,
        ]);

        $lead = $followUp->lead;
        if (!$lead->next_follow_up_at || $old->equalTo($lead->next_follow_up_at) || $data['follow_up_at'] < $lead->next_follow_up_at) {
            $lead->update(['next_follow_up_at' => $data['follow_up_at']]);
        }
        $lead->logActivity(
            'follow_up_rescheduled',
            'Follow-up rescheduled from ' . $old->format('d M Y, h:i A') . ' to ' . \Carbon\Carbon::parse($data['follow_up_at'])->format('d M Y, h:i A') . ($data['reason'] ? ' — ' . $data['reason'] : ''),
            ['from' => $old->toIso8601String(), 'to' => $data['follow_up_at']]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Follow-up rescheduled.');
    }

    public function complete(FollowUp $followUp)
    {
        abort_unless($followUp->lead->client_id === Auth::user()->client->id, 403);
        abort_unless(Auth::user()->can('complete_followups'), 403);
        $followUp->update(['status' => 'completed', 'completed_at' => now(), 'completed_by' => Auth::id()]);
        $followUp->lead->logActivity('follow_up_completed', ($followUp->subject ?: $followUp->type) . ' follow-up completed');
        $followUp->lead->markFirstResponse();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Follow-up completed.');
    }
}
