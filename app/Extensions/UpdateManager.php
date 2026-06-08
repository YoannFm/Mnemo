<?php

namespace App\Extensions;

use App\Mnemo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ZipArchive;

class UpdateManager
{
    const CACHE_KEY = 'mnemo_update_info';
    const CACHE_TTL = 3600; // 1h

    public function __construct(protected Filesystem $files) {}

    // ── Vérification ─────────────────────────────────────────────────

    public function hasUpdate(bool $force = false): bool
    {
        $latest = $this->getLatestVersion($force);

        if (!$latest) {
            return false;
        }

        return version_compare($latest['version'], Mnemo::version(), '>');
    }

    public function getLatestVersion(bool $force = false): ?array
    {
        return $this->fetch($force)['latest'] ?? null;
    }

    public function fetch(bool $force = false): array
    {
        if (!$force && Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        $siteKey  = setting('site_key');
        $cloudUrl = rtrim(setting('mnemocloud_url', ''), '/');

        if (!$siteKey || !$cloudUrl) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Site-Key'       => $siteKey,
                    'X-Mnemo-Version'  => Mnemo::version(),
                    'X-PHP-Version'    => PHP_VERSION,
                ])
                ->get($cloudUrl . '/api/v1/updates/check', [
                    'current_version' => Mnemo::version(),
                ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            Cache::put(self::CACHE_KEY, $data, self::CACHE_TTL);

            return $data;
        } catch (\Throwable) {
            return [];
        }
    }

    // ── Téléchargement ────────────────────────────────────────────────

    public function isDownloaded(): bool
    {
        $latest = $this->getLatestVersion();

        if (!$latest) {
            return false;
        }

        return $this->files->exists($this->updateFilePath($latest['version']));
    }

    public function download(): void
    {
        $latest = $this->getLatestVersion(true);

        if (!$latest || !$this->hasUpdate()) {
            throw new RuntimeException('Aucune mise à jour disponible.');
        }

        $downloadUrl = $latest['download_url'] ?? null;

        if (!$downloadUrl) {
            throw new RuntimeException('URL de téléchargement manquante.');
        }

        $siteKey = setting('site_key');

        $updatesDir = storage_path('app/updates/');

        if (!$this->files->isDirectory($updatesDir)) {
            $this->files->makeDirectory($updatesDir, 0755, true);
        }

        $path = $this->updateFilePath($latest['version']);

        $response = Http::timeout(120)
            ->withHeaders(['X-Site-Key' => $siteKey])
            ->sink($path)
            ->get($downloadUrl);

        if (!$response->successful()) {
            if ($this->files->exists($path)) {
                $this->files->delete($path);
            }
            throw new RuntimeException('Échec du téléchargement.');
        }
    }

    // ── Installation ──────────────────────────────────────────────────

    public function install(): void
    {
        $latest = $this->getLatestVersion();

        if (!$latest) {
            throw new RuntimeException('Aucune mise à jour disponible.');
        }

        if (!is_writable(base_path())) {
            throw new RuntimeException('Permission d\'écriture manquante sur ' . base_path());
        }

        $zipPath = $this->updateFilePath($latest['version']);

        if (!$this->files->exists($zipPath)) {
            throw new RuntimeException('Fichier de mise à jour non téléchargé.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le fichier ZIP.');
        }

        if (!$zip->extractTo(base_path())) {
            $zip->close();
            throw new RuntimeException('Impossible d\'extraire le ZIP.');
        }

        $zip->close();
        $this->files->delete($zipPath);

        Cache::flush();
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        Cache::forget(self::CACHE_KEY);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    protected function updateFilePath(string $version): string
    {
        return storage_path('app/updates/mnemo-' . $version . '.zip');
    }
}
