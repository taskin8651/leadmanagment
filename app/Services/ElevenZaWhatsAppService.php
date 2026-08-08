<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * DUMMY request shape — 11za's real outbound send-message API hasn't been confirmed yet.
 * Once you have 11za's API docs (or a working sample request from their dashboard),
 * update buildPayload() below to match their exact endpoint, auth header, and field names.
 * Until then this throws if ELEVENZA_API_URL / ELEVENZA_API_KEY are not set in .env.
 */
class ElevenZaWhatsAppService
{
    public function isConfigured(): bool
    {
        return filled(config('services.elevenza.base_url')) && filled(config('services.elevenza.api_key'));
    }

    public function sendMessage(string $phone, string $message): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('11za WhatsApp is not configured — set ELEVENZA_API_URL and ELEVENZA_API_KEY in .env.');
        }

        $phone = preg_replace('/\D/', '', $phone);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.elevenza.api_key'),
        ])->post(rtrim(config('services.elevenza.base_url'), '/') . '/send-message', [
            'sender' => config('services.elevenza.sender'),
            'phone' => $phone,
            'message' => $message,
        ]);

        if (!$response->successful()) {
            Log::warning('11za WhatsApp send failed', ['phone' => $phone, 'status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('WhatsApp message could not be sent (provider returned ' . $response->status() . ').');
        }

        return $response->json() ?? [];
    }
}
