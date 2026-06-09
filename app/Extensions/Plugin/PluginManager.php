<?php

namespace App\Extensions\Plugin;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PluginManager
{
    protected array $plugins = [];
    protected array $adminNavItems = [];
    protected array $userNavItems = [];

    public function __construct(protected Filesystem $files) {}

    public function loadPlugins(): void
    {
        $enabled = $this->getEnabledSlugs();

        foreach ($this->discoverPlugins() as $plugin) {
            if (in_array($plugin->id, $enabled)) {
                $this->bootPlugin($plugin);
            }
        }
    }

    public function discoverPlugins(): Collection
    {
        $pluginsPath = base_path('plugins');

        if (!$this->files->isDirectory($pluginsPath)) {
            return collect();
        }

        return collect($this->files->directories($pluginsPath))
            ->map(fn ($dir) => $this->readManifest($dir))
            ->filter();
    }

    public function findDescription(string $slug): ?object
    {
        return $this->readManifest(base_path("plugins/{$slug}"));
    }

    public function enable(string $slug): void
    {
        DB::table('plugins')->updateOrInsert(
            ['slug' => $slug],
            ['is_enabled' => true]
        );
    }

    public function disable(string $slug): void
    {
        DB::table('plugins')->updateOrInsert(
            ['slug' => $slug],
            ['is_enabled' => false]
        );
    }

    public function getAvailablePlugins(): Collection
    {
        $response = Http::withHeaders($this->cloudHeaders())
            ->get(config('mnemo.cloud_url') . '/api/v1/plugins');

        if (!$response->successful()) {
            return collect();
        }

        $installed = $this->discoverPlugins()->pluck('id')->toArray();

        return collect($response->json())->map(function ($p) use ($installed) {
            $p = (object) $p;
            $p->is_installed = in_array($p->slug, $installed);
            return $p;
        });
    }

    public function install(string $slug): void
    {
        $response = Http::withHeaders($this->cloudHeaders())
            ->get(config('mnemo.cloud_url') . "/api/v1/plugins/{$slug}");

        if (!$response->successful()) {
            throw new \RuntimeException("Plugin introuvable sur MnemoCloud.");
        }

        $plugin = $response->json();
        $version = collect($plugin['versions'] ?? [])->first()['version'] ?? null;

        if (!$version) {
            throw new \RuntimeException("Aucune version disponible pour ce plugin.");
        }

        $download = Http::withHeaders($this->cloudHeaders())
            ->get(config('mnemo.cloud_url') . "/api/v1/plugins/{$slug}/download/{$version}");

        if (!$download->successful()) {
            throw new \RuntimeException("Échec du téléchargement du plugin.");
        }

        $tmpZip = storage_path("app/plugins/{$slug}-{$version}.zip");
        $this->files->ensureDirectoryExists(storage_path('app/plugins'));
        $this->files->put($tmpZip, $download->body());

        $archive = new \ZipArchive();
        if ($archive->open($tmpZip) !== true) {
            throw new \RuntimeException("Impossible d'ouvrir l'archive du plugin.");
        }

        $archive->extractTo(base_path("plugins/{$slug}"));
        $archive->close();
        $this->files->delete($tmpZip);
    }

    public function delete(string $slug): void
    {
        $path = base_path("plugins/{$slug}");
        if ($this->files->isDirectory($path)) {
            $this->files->deleteDirectory($path);
        }
        DB::table('plugins')->where('slug', $slug)->delete();
    }

    protected function cloudHeaders(): array
    {
        return [
            'X-Site-Key'      => setting('site_key', ''),
            'X-Mnemo-Version' => \App\Mnemo::version(),
        ];
    }

    public function isEnabled(string $slug): bool
    {
        return in_array($slug, $this->getEnabledSlugs());
    }

    public function path(string $slug, string $path = ''): string
    {
        return base_path("plugins/{$slug}") . ($path ? "/{$path}" : '');
    }

    public function addAdminNavItem(callable $callback): void
    {
        $this->adminNavItems[] = $callback;
    }

    public function addUserNavItem(callable $callback): void
    {
        $this->userNavItems[] = $callback;
    }

    public function getAdminNavItems(): array
    {
        return array_merge(...array_map(fn ($cb) => (array) $cb(), $this->adminNavItems));
    }

    public function getUserNavItems(): array
    {
        return array_merge(...array_map(fn ($cb) => (array) $cb(), $this->userNavItems));
    }

    protected function bootPlugin(object $plugin): void
    {
        $providerClass = $plugin->provider ?? null;
        if (!$providerClass) {
            return;
        }

        $this->registerAutoloader($plugin->id);

        if (!class_exists($providerClass)) {
            return;
        }

        /** @var BasePluginServiceProvider $provider */
        $provider = new $providerClass(app());
        $provider->setPlugin($plugin);

        app()->register($provider);

        $this->plugins[$plugin->id] = $plugin;
    }

    protected function registerAutoloader(string $slug): void
    {
        $baseDir = base_path("plugins/{$slug}/");
        $srcDir  = base_path("plugins/{$slug}/src/");
        $prefix = 'Plugins\\' . ucfirst($slug) . '\\';
        $prefixLen = strlen($prefix);

        spl_autoload_register(function (string $class) use ($prefix, $prefixLen, $baseDir, $srcDir) {
            if (strncmp($prefix, $class, $prefixLen) !== 0) {
                return;
            }
            $relative = str_replace('\\', '/', substr($class, $prefixLen)) . '.php';
            foreach ([$baseDir, $srcDir] as $dir) {
                $file = $dir . $relative;
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        });
    }

    protected function getEnabledSlugs(): array
    {
        try {
            return DB::table('plugins')
                ->where('is_enabled', true)
                ->pluck('slug')
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function readManifest(string $dir): ?object
    {
        $json = $dir . '/plugin.json';
        if (!$this->files->exists($json)) {
            return null;
        }

        $data = json_decode($this->files->get($json));
        if (!$data || empty($data->id)) {
            return null;
        }

        return $data;
    }
}
