<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Notifications\NewPublicLeadReceived;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    /**
     * DUMMY field mapping — 11za's real webhook payload hasn't been confirmed yet.
     * Once you share a sample payload from 11za, update the field names below
     * (phone/name/message) to match it exactly.
     */
    public function handle(Request $request)
    {
        $secret = config('services.whatsapp.webhook_secret');
        if ($secret && !hash_equals((string) $secret, (string) $request->query('token'))) {
            abort(403);
        }

        $phone = $request->input('phone') ?? $request->input('from') ?? $request->input('sender') ?? $request->input('mobile');
        $name = $request->input('name') ?? $request->input('sender_name') ?? $request->input('pushname');
        $message = $request->input('message') ?? $request->input('text') ?? $request->input('body');

        if (!$phone) {
            return response()->json(['message' => 'phone field missing from payload'], 422);
        }

        $client = Client::first();
        if (!$client) {
            return response()->json(['message' => 'no client account configured'], 500);
        }

        $phone = preg_replace('/\D/', '', (string) $phone);

        $existing = Lead::where('client_id', $client->id)->where('phone', $phone)->first();
        if ($existing) {
            return response()->json(['message' => 'duplicate phone, skipped', 'lead_id' => $existing->id]);
        }

        $lead = Lead::create([
            'lead_number' => 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT),
            'client_id' => $client->id,
            'name' => $name ?: 'WhatsApp Lead',
            'phone' => $phone,
            'source' => 'WhatsApp Ads',
            'status' => 'new',
            'priority' => 'medium',
            'notes' => $message,
            'assigned_to' => $client->nextAssignee()?->id,
        ]);

        $lead->logActivity('created', 'Received via WhatsApp Ads webhook');
        if ($lead->assigned_to) {
            $lead->logActivity('assigned', $lead->assignee->name . ' assigned to this lead (auto)');
        }

        $client->user?->notify(new NewPublicLeadReceived($lead));

        return response()->json(['message' => 'lead created', 'lead_id' => $lead->id], 201);
    }
}
