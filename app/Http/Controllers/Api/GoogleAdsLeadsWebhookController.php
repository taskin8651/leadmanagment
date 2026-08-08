<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Notifications\NewPublicLeadReceived;
use Illuminate\Http\Request;

/**
 * Google Ads lead form extension webhook.
 *
 * Setup: in Google Ads → Lead form extension → Webhook, set the URL to
 * {APP_URL}/api/webhooks/google-ads-leads and the "webhook key" to the same
 * value as GOOGLE_ADS_LEADS_WEBHOOK_SECRET in .env. Google sends that key back
 * in the `google_key` field of every submission so you can verify the request.
 *
 * Payload shape follows Google's documented lead form webhook format
 * (google_key, api_version, user_column_data[] of {column_id, string_value}).
 * The column_id values below (FULL_NAME, PHONE_NUMBER, EMAIL) are Google's
 * built-in question IDs — if your form uses custom questions, extend
 * mapColumns() to handle their column_id values too.
 */
class GoogleAdsLeadsWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.google_ads_leads.webhook_secret');
        if ($secret && !hash_equals((string) $secret, (string) $request->input('google_key'))) {
            abort(403);
        }

        if ($request->boolean('is_test')) {
            return response()->json(['message' => 'test payload received']);
        }

        $client = Client::first();
        if (!$client) {
            return response()->json(['message' => 'no client account configured'], 500);
        }

        $columns = collect($request->input('user_column_data', []))->mapWithKeys(
            fn ($c) => [$c['column_id'] ?? '' => $c['string_value'] ?? null]
        );

        $phone = $columns->get('PHONE_NUMBER');
        if (!$phone) {
            return response()->json(['message' => 'PHONE_NUMBER missing from payload'], 422);
        }
        $phone = preg_replace('/\D/', '', (string) $phone);

        $existing = Lead::where('client_id', $client->id)->where('phone', $phone)->first();
        if ($existing) {
            return response()->json(['message' => 'duplicate phone, skipped', 'lead_id' => $existing->id]);
        }

        $lead = Lead::create([
            'lead_number' => 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT),
            'client_id' => $client->id,
            'name' => $columns->get('FULL_NAME') ?? 'Google Ads Lead',
            'email' => $columns->get('EMAIL'),
            'phone' => $phone,
            'source' => 'Google Ads Lead Form',
            'status' => 'new',
            'priority' => 'medium',
            'assigned_to' => $client->nextAssignee()?->id,
        ]);

        $lead->logActivity('created', 'Received via Google Ads lead form webhook');
        if ($lead->assigned_to) {
            $lead->logActivity('assigned', $lead->assignee->name . ' assigned to this lead (auto)');
        }
        $client->user?->notify(new NewPublicLeadReceived($lead));

        return response()->json(['message' => 'lead created', 'lead_id' => $lead->id], 201);
    }
}
