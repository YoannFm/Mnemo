<?php

namespace App\Extensions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class UpdateManager
{
    private string $cacheKey = 'mnemo_latest_version';
    private ?array $latestData = null;

    public function __construct(private Filesystem $files) {}

    public function getLatestVersion(): ?string
    {
        return $this->fetchData()['version'] ?? null;
    }

    public function hasUpdate(): bool
    {
        $latest = $this->getLatestVersion();
        if (!$latest) return false;
        return version_compare($latest, \App\Mnemo::version(), '>');
    }

    public function isDownloaded(): bool
    {
        $latest = $this->getLatestVersion();
        if (!$latest) return false;
        return $this->files->exists($this->zipPath($latest));
    }

    public function fetch(bool $force = false): void
    {
        if ($force) {
            cache()->forget($this->cacheKey);
            $this->latestData = null;
        }
        $this->fetchData();
    }

    public function download(): void
    {
        $data = $this->fetchData();
        if (!$data) throw new \RuntimeException('Impossible de récupérer les informations de mise à jour.');

        $version = $data['version'];
        $url = config('mnemo.cloud_url') . '/api/v1/updates/' . $version . '/download';

        $response = Http::withHeaders($this->headers())->get($url);
        if (!$response->successful()) {
            throw new \RuntimeException('Échec du téléchargement : ' . $response->status());
        }

        $dir = storage_path('app/updates');
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }

        $this->files->put($this->zipPath($version), $response->body());
    }

    public function backupFiles(): string
    {
        $dir = storage_path('app/backups');
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }

        $filename = 'backup-files-' . now()->format('Y-m-d-His') . '.zip';
        $path     = $dir . '/' . $filename;

        $archive = new \ZipArchive();
        if ($archive->open($path, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Impossible de créer l\'archive.');
        }

        $base    = base_path();
        $exclude = ['vendor', 'node_modules', '.git', 'storage/app/backups', 'storage/app/updates', 'storage/logs'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relative = ltrim(str_replace($base, '', $file->getPathname()), DIRECTORY_SEPARATOR);

            foreach ($exclude as $ex) {
                if (str_starts_with($relative, $ex)) continue 2;
            }

            if ($file->isDir()) {
                $archive->addEmptyDir($relative);
            } else {
                $archive->addFile($file->getPathname(), $relative);
            }
        }

        $archive->close();
        return $path;
    }

    public function backupDatabase(): string
    {
        $dir = storage_path('app/backups');
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }

        $filename = 'backup-db-' . now()->format('Y-m-d-His') . '.sql';
        $path     = $dir . '/' . $filename;

        $db       = config('database.connections.' . config('database.default'));
        $host     = $db['host'];
        $port     = $db['port'] ?? 3306;
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        exec($cmd, $output, $code);

        if ($code !== 0 || !$this->files->exists($path) || $this->files->size($path) === 0) {
            throw new \RuntimeException('Échec de l\'export de la base de données.');
        }

        return $path;
    }

    public function install(): void
    {
        $latest = $this->getLatestVersion();
        if (!$latest) throw new \RuntimeException('Aucune version disponible.');

        $zip = $this->zipPath($latest);
        if (!$this->files->exists($zip)) {
            throw new \RuntimeException('Mise à jour non téléchargée.');
        }

        $archive = new \ZipArchive();
        if ($archive->open($zip) !== true) {
            throw new \RuntimeException('Impossible d\'ouvrir l\'archive.');
        }
        $archive->extractTo(base_path());
        $archive->close();

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        $this->files->delete($zip);
    }

    private function fetchData(): ?array
    {
        if ($this->latestData) return $this->latestData;

        $this->latestData = cache()->remember($this->cacheKey, now()->addMinutes(30), function () {
            $response = Http::withHeaders($this->headers())
                ->get(config('mnemo.cloud_url') . '/api/v1/updates/check');

            if (!$response->successful()) return null;
            return $response->json();
        });

        return $this->latestData;
    }

    private function headers(): array
    {
        return [
            'X-Site-Key'      => setting('site_key', ''),
            'X-Mnemo-Version' => \App\Mnemo::version(),
        ];
    }

    private function zipPath(string $version): string
    {
        return storage_path('app/updates/mnemo-' . $version . '.zip');
    }
}
