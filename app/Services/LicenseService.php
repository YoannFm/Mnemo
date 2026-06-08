<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    const CACHE_KEY   = 'license_status_v1';
    const CACHE_TTL   = 10800; // 3 heures
    const SETTING_KEY = 'site_key';

    public static function isValid(): bool
    {
        return Cache::get(self::CACHE_KEY . '_valid', false);
    }

    public static function check(): array
    {
        $siteKey  = setting(self::SETTING_KEY);
        $cloudUrl = rtrim(config('mnemo.cloud_url', ''), '/');
        $domain   = parse_url(config('app.url'), PHP_URL_HOST) ?? request()->getHost();

        if (empty($siteKey) || empty($cloudUrl)) {
            $result = ['valid' => false, 'reason' => 'not_configured'];
            self::store($result);
            return $result;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->post($cloudUrl . '/api/v1/license/verify', [
                    'site_key' => $siteKey,
                    'domain'   => $domain,
                ]);

            if ($response->successful()) {
                $data   = $response->json();
                $result = [
                    'valid'      => (bool) ($data['valid'] ?? false),
                    'plan'       => $data['plan'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'reason'     => $data['reason'] ?? null,
                ];
            } else {
                $result = ['valid' => false, 'reason' => $response->json('reason') ?? 'server_error'];
            }
        } catch (\Throwable $e) {
            Log::warning('MnemoCloud license check failed: ' . $e->getMessage());
            // En cas d'erreur réseau, on garde le dernier statut connu
            $result = Cache::get(self::CACHE_KEY . '_last', ['valid' => false, 'reason' => 'unreachable']);
        }

        self::store($result);
        return $result;
    }

    private static function store(array $result): void
    {
        Cache::put(self::CACHE_KEY . '_valid', $result['valid'], self::CACHE_TTL);
        Cache::put(self::CACHE_KEY . '_data',  $result,          self::CACHE_TTL);
        if ($result['valid']) {
            // Sauvegarde du dernier statut valide comme fallback réseau
            Cache::forever(self::CACHE_KEY . '_last', $result);
        }
    }

    public static function getData(): array
    {
        return Cache::get(self::CACHE_KEY . '_data', ['valid' => false, 'reason' => 'not_checked']);
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY . '_valid');
        Cache::forget(self::CACHE_KEY . '_data');
    }
}
