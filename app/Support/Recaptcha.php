<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class Recaptcha
{
    public static function enabled(): bool
    {
        return filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret_key'));
    }

    public static function verify(?string $response): bool
    {
        if (!self::enabled()) {
            // No keys configured yet — don't block the form, just skip verification.
            return true;
        }

        if (blank($response)) {
            return false;
        }

        try {
            $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $response,
            ])->json();

            return (bool) ($result['success'] ?? false);
        } catch (\Throwable $e) {
            report($e);
            return true; // don't lock users out if Google's API is unreachable
        }
    }
}
