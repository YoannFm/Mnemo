<?php

namespace App\Extensions\Plugin;

use App\Mnemo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
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

    /**
     * Plugins proposés par le catalogue GitHub (voir PLUGINS.md), avec leur état d'installation.
     */
    public function getAvailablePlugins(): Collection
    {
        $installed = $this->discoverPlugins()->pluck('id')->toArray();

        return $this->fetchCatalog()->map(function (object $plugin) use ($installed) {
            $plugin->is_installed = in_array($plugin->slug, $installed);
            return $plugin;
        });
    }

    public function install(string $slug): void
    {
        $plugin = $this->fetchCatalog()->firstWhere('slug', $slug);

        if (!$plugin) {
            throw new \RuntimeException("Plugin introuvable dans le catalogue.");
        }

        $download = Http::withUserAgent(Mnemo::userAgent())->timeout(60)->get($plugin->download_url);

        if (!$download->successful()) {
            throw new \RuntimeException("Échec du téléchargement du plugin.");
        }

        $tmpZip = storage_path("app/plugins/{$slug}-{$plugin->version}.zip");
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

    /**
     * Lit le catalogue JSON des plugins. Les entrées incomplètes ou dont le slug
     * ou l'URL ne sont pas sûrs sont ignorées.
     */
    protected function fetchCatalog(): Collection
    {
        try {
            $response = Http::withUserAgent(Mnemo::userAgent())
                ->acceptJson()
                ->timeout(15)
                ->get(config('mnemo.github.plugins_catalog'));
        } catch (ConnectionException) {
            return collect();
        }

        if (!$response->successful()) {
            return collect();
        }

        return collect($response->json('plugins', []))
            ->filter(fn ($p) => is_array($p)
                && preg_match('/^[a-z0-9-]+$/', $p['slug'] ?? '')
                && !empty($p['name'])
                && !empty($p['version'])
                && str_starts_with($p['download_url'] ?? '', 'https://'))
            ->map(fn (array $p) => (object) $p)
            ->values();
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
        $prefix = 'Plugins\\' . str_replace('-', '', ucwords($slug, '-')) . '\\';
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
