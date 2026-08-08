<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Notifications\NewPublicLeadReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Facebook (Meta) Lead Ads webhook.
 *
 * Setup:
 * 1. Create a Meta app with the "Webhooks" + "Lead Ads" products, subscribe your
 *    Page to `leadgen` events, and point the webhook URL at
 *    {APP_URL}/api/webhooks/facebook-leads.
 * 2. Set FACEBOOK_LEADS_VERIFY_TOKEN in .env to any random string and enter the
 *    same value in the Meta webhook setup screen (this answers Meta's GET
 *    verification handshake below).
 * 3. Set FACEBOOK_LEADS_APP_SECRET (from the Meta app dashboard) — used to verify
 *    the X-Hub-Signature-256 header on incoming POSTs.
 * 4. Set FACEBOOK_LEADS_PAGE_ACCESS_TOKEN — a Page access token with
 *    `leads_retrieval` permission, used to fetch the actual lead fields from the
 *    Graph API (Meta's webhook only tells you a leadgen_id, not the answers).
 *
 * DUMMY field mapping — Meta lead form question names vary per advertiser. Once
 * you see a real lead payload from the Graph API, update mapFields() below to
 * match your form's actual field names (e.g. 'full_name', 'phone_number').
 */
class FacebookLeadsWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $token = config('services.facebook_leads.verify_token');
        if ($token && $request->query('hub_mode') === 'subscribe' && hash_equals((string) $token, (string) $request->query('hub_verify_token'))) {
            return response($request->query('hub_challenge'), 200);
        }
        abort(403);
    }

    public function handle(Request $request)
    {
        if (!$this->verifySignature($request)) {
            abort(403);
        }

        $client = Client::first();
        if (!$client) {
            return response()->json(['message' => 'no client account configured'], 500);
        }

        $leadgenIds = [];
        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? null) === 'leadgen' && !empty($change['value']['leadgen_id'])) {
                    $leadgenIds[] = $change['value']['leadgen_id'];
                }
            }
        }

        $created = [];
        foreach ($leadgenIds as $leadgenId) {
            $lead = $this->importLeadgenId($client, $leadgenId);
            if ($lead) $created[] = $lead->id;
        }

        return response()->json(['message' => 'processed', 'lead_ids' => $created]);
    }

    private function verifySignature(Request $request): bool
    {
        $secret = config('services.facebook_leads.app_secret');
        if (!$secret) return true; // no secret configured yet — accept during initial setup/testing

        $signature = $request->header('X-Hub-Signature-256', '');
        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        return hash_equals($expected, $signature);
    }

    private function importLeadgenId(Client $client, string $leadgenId): ?Lead
    {
        $token = config('services.facebook_leads.page_access_token');
        if (!$token) {
            Log::warning('Facebook Lead Ads: FACEBOOK_LEADS_PAGE_ACCESS_TOKEN not set, cannot fetch lead ' . $leadgenId);
            return null;
        }

        $response = Http::get("https://graph.facebook.com/v19.0/{$leadgenId}", ['access_token' => $token]);
        if (!$response->successful()) {
            Log::warning('Facebook Lead Ads: failed to fetch lead ' . $leadgenId, ['body' => $response->body()]);
            return null;
        }

        $fields = collect($response->json('field_data', []))->mapWithKeys(fn ($f) => [$f['name'] => $f['values'][0] ?? null]);

        $phone = $fields->get('phone_number') ?? $fields->get('phone');
        if (!$phone) return null;
        $phone = preg_replace('/\D/', '', (string) $phone);

        $existing = Lead::where('client_id', $client->id)->where('phone', $phone)->first();
        if ($existing) return $existing;

        $lead = Lead::create([
            'lead_number' => 'LD-' . str_pad((string) (Lead::withTrashed()->max('id') + 1), 6, '0', STR_PAD_LEFT),
            'client_id' => $client->id,
            'name' => $fields->get('full_name') ?? $fields->get('first_name') ?? 'Facebook Lead',
            'email' => $fields->get('email'),
            'phone' => $phone,
            'source' => 'Facebook Lead Ads',
            'status' => 'new',
            'priority' => 'medium',
            'assigned_to' => $client->nextAssignee()?->id,
        ]);

        $lead->logActivity('created', 'Received via Facebook Lead Ads webhook');
        if ($lead->assigned_to) {
            $lead->logActivity('assigned', $lead->assignee->name . ' assigned to this lead (auto)');
        }
        $client->user?->notify(new NewPublicLeadReceived($lead));

        return $lead;
    }
}
