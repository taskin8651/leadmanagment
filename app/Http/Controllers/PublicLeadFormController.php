<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Support\Recaptcha;
use Illuminate\Http\Request;

class PublicLeadFormController extends Controller
{
    public function show(Client $client)
    {
        abort_unless($client->status === 'active', 404);
        return view('public.lead-form', ['client' => $client, 'recaptchaEnabled' => Recaptcha::enabled()]);
    }

    public function store(Request $request, Client $client)
    {
        abort_unless($client->status === 'active', 404);

        if (!Recaptcha::verify($request->input('g-recaptcha-response'))) {
            return back()->withErrors(['name' => 'Please confirm you are not a robot.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['lead_number'] = 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT);
        $data['client_id'] = $client->id;
        $data['source'] = 'Public Form';
        $data['status'] = 'new';
        $data['priority'] = 'medium';

        $existing = Lead::where('client_id', $client->id)->where('phone', $data['phone'])->first();

        $next = $client->nextAssignee();
        $data['assigned_to'] = $next?->id;

        $lead = Lead::create($data);
        $lead->logActivity('created', 'Submitted via public lead form' . ($existing ? ' (possible duplicate of ' . $existing->lead_number . ')' : ''), $existing ? ['possible_duplicate_of' => $existing->id] : []);
        if ($lead->assigned_to) {
            $lead->logActivity('assigned', $lead->assignee->name . ' assigned to this lead (auto)');
        }

        $client->user?->notify(new \App\Notifications\NewPublicLeadReceived($lead));

        return redirect()->route('public.lead-form', $client)->with('submitted', true);
    }
}
