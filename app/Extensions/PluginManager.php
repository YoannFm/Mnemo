<?php

namespace App\Extensions;

use App\Services\LicenseService;
use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class PluginManager
{
    protected array $plugins = [];
    protected string $pluginsPath;
    protected string $pluginsPublicPath;
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files       = $files;
        $this->pluginsPath = base_path('plugins/');
        $this->pluginsPublicPath = public_path('assets/plugins/');
    }

    public function loadPlugins(\Illuminate\Contracts\Foundation\Application $app): void
    {
        $plugins = $this->getEnabledPlugins();

        if (empty($plugins)) {
            return;
        }

        $composer = $this->files->getRequire(base_path('vendor/autoload.php'));

        foreach ($plugins as $pluginId => $plugin) {
            try {
                if (isset($plugin->composer)) {
                    $this->autoloadPlugin($pluginId, $composer, $plugin->composer);
                }

                foreach ($plugin->providers ?? [] as $providerClass) {
                    $provider = new $providerClass($app);

                    if (method_exists($provider, 'bindPlugin')) {
                        $provider->bindPlugin($plugin);
                    }

                    $app->register($provider);
                }
            } catch (Throwable $t) {
                if (!$app->isProduction()) {
                    throw $t;
                }

                report($t);
                $this->disable($pluginId);
            }
        }
    }

    public function pluginsPath(string $path = ''): string
    {
        return $this->pluginsPath . $path;
    }

    public function path(string $plugin, string $path = ''): string
    {
        return $this->pluginsPath . $plugin . '/' . $path;
    }

    public function publicPath(string $plugin, string $path = ''): string
    {
        return $this->pluginsPublicPath . $plugin . '/' . $path;
    }

    public function getCachedPluginsPath(): string
    {
        return base_path('bootstrap/cache/plugins.php');
    }

    // ── Découverte ──────────────────────────────────────────────────

    public function findPluginsDescriptions(): Collection
    {
        if (!$this->files->isDirectory($this->pluginsPath)) {
            return collect();
        }

        $plugins = [];
        foreach ($this->files->directories($this->pluginsPath) as $dir) {
            $name = $this->files->basename($dir);
            $desc = $this->findDescription($name);
            if ($desc) {
                $plugins[$name] = $desc;
            }
        }

        return collect($plugins);
    }

    public function findDescription(string $plugin): ?object
    {
        $path = $this->path($plugin, 'plugin.json');

        if (!$this->files->exists($path)) {
            return null;
        }

        $json = json_decode($this->files->get($path));

        if ($json === null || ($json->id ?? null) !== $plugin) {
            return null;
        }

        $composerPath = $this->path($plugin, 'composer.json');
        if ($this->files->exists($composerPath)) {
            $json->composer = json_decode($this->files->get($composerPath), true);
        }

        $json->is_enabled = $this->isEnabled($plugin);

        return $json;
    }

    // ── État ─────────────────────────────────────────────────────────

    public function plugins(): array
    {
        return $this->plugins;
    }

    public function isEnabled(string $plugin): bool
    {
        return array_key_exists($plugin, $this->plugins);
    }

    // ── Activation ───────────────────────────────────────────────────

    public function enable(string $plugin): bool
    {
        if (!$this->setEnabled($plugin, true)) {
            return false;
        }

        $this->runMigrations($plugin);
        $this->createAssetsLink($plugin);

        return true;
    }

    public function disable(string $plugin): bool
    {
        return $this->setEnabled($plugin, false);
    }

    // ── Installation depuis MnemoCloud ───────────────────────────────

    public function install(string $slug): void
    {
        $siteKey  = setting('site_key');
        $cloudUrl = rtrim(config('mnemo.cloud_url', ''), '/');

        if (!$siteKey || !$cloudUrl) {
            throw new \RuntimeException('MnemoCloud non configuré (clé ou URL manquante).');
        }

        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withHeaders(['X-Site-Key' => $siteKey])
            ->get($cloudUrl . '/api/v1/plugins/' . $slug);

        if (!$response->successful()) {
            throw new \RuntimeException('Plugin introuvable ou accès refusé : ' . $response->json('error', 'erreur inconnue'));
        }

        $pluginData   = $response->json('plugin');
        $latestVersion = $response->json('versions.0');

        if (!$latestVersion || empty($latestVersion['download_url'])) {
            throw new \RuntimeException('Aucune version téléchargeable disponible.');
        }

        $zipResponse = \Illuminate\Support\Facades\Http::timeout(60)
            ->withHeaders(['X-Site-Key' => $siteKey])
            ->get($latestVersion['download_url']);

        if (!$zipResponse->successful()) {
            throw new \RuntimeException('Échec du téléchargement du plugin.');
        }

        $tmpZip = tempnam(sys_get_temp_dir(), 'mnemo_plugin_') . '.zip';
        file_put_contents($tmpZip, $zipResponse->body());

        $pluginDir = $this->path($slug);

        if (!$this->files->isDirectory($pluginDir)) {
            $this->files->makeDirectory($pluginDir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmpZip) !== true) {
            unlink($tmpZip);
            throw new \RuntimeException('Impossible d\'ouvrir le ZIP du plugin.');
        }

        $zip->extractTo($pluginDir);
        $zip->close();
        unlink($tmpZip);

        $this->createAssetsLink($slug);

        if ($this->isEnabled($slug)) {
            $this->runMigrations($slug);
        }

        Cache::forget('mnemo_available_plugins');
    }

    public function delete(string $plugin): void
    {
        if ($this->isEnabled($plugin)) {
            throw new \RuntimeException('Désactivez le plugin avant de le supprimer.');
        }

        $this->files->deleteDirectory($this->publicPath($plugin));
        $this->files->deleteDirectory($this->path($plugin));

        Cache::forget('mnemo_available_plugins');
    }

    // ── MnemoCloud : plugins disponibles ─────────────────────────────

    public function getAvailablePlugins(bool $force = false): array
    {
        $cacheKey = 'mnemo_available_plugins';

        if (!$force && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $siteKey  = setting('site_key');
        $cloudUrl = rtrim(config('mnemo.cloud_url', ''), '/');

        if (!$siteKey || !$cloudUrl) {
            return [];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['X-Site-Key' => $siteKey])
                ->get($cloudUrl . '/api/v1/plugins');

            if (!$response->successful()) {
                return [];
            }

            $plugins = $response->json('plugins', []);
            Cache::put($cacheKey, $plugins, 3600);

            return $plugins;
        } catch (\Throwable) {
            return [];
        }
    }

    public function getPluginsToUpdate(): Collection
    {
        $available = collect($this->getAvailablePlugins());

        return $this->findPluginsDescriptions()->filter(function ($installed) use ($available) {
            $remote = $available->firstWhere('slug', $installed->id);

            if (!$remote) {
                return false;
            }

            return version_compare($remote['latest_version']['version'] ?? '0', $installed->version ?? '0', '>');
        });
    }

    // ── Cache interne ─────────────────────────────────────────────────

    public function cachePlugins(?array $enabledList = null): Collection
    {
        if ($enabledList === null) {
            $json = $this->files->exists($this->pluginsPath('plugins.json'))
                ? json_decode($this->files->get($this->pluginsPath('plugins.json')), true)
                : [];
            $enabledList = $json ?? [];
        }

        $plugins = $this->findPluginsDescriptions()
            ->filter(fn($desc, $id) => in_array($id, $enabledList, true));

        $this->plugins = $plugins->all();

        $cache = $plugins->map(fn($p) => (array) $p)->all();

        if (function_exists('is_installed') ? is_installed() : true) {
            $this->files->put(
                $this->getCachedPluginsPath(),
                '<?php return ' . var_export($cache, true) . ';'
            );
        }

        return $plugins;
    }

    public function purgeCache(): void
    {
        if ($this->files->exists($this->getCachedPluginsPath())) {
            $this->files->delete($this->getCachedPluginsPath());
        }

        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
    }

    // ── Privé ─────────────────────────────────────────────────────────

    protected function getEnabledPlugins(): array
    {
        try {
            $cached = $this->files->getRequire($this->getCachedPluginsPath());
            $this->plugins = array_map(fn($a) => (object) $a, $cached);
        } catch (FileNotFoundException) {
            $this->plugins = $this->cachePlugins()->all();
        }

        return $this->plugins;
    }

    protected function setEnabled(string $plugin, bool $enabled): bool
    {
        $desc = $this->findDescription($plugin);
        if (!$desc) {
            return false;
        }

        $list = array_keys($this->plugins);

        if ($enabled) {
            $list[] = $plugin;
        } else {
            $list = array_values(array_filter($list, fn($p) => $p !== $plugin));
        }

        $list = array_unique($list);

        $res = $this->files->put(
            $this->pluginsPath('plugins.json'),
            json_encode(array_values($list))
        );

        if ($res === false) {
            return false;
        }

        $this->cachePlugins($list);

        return true;
    }

    protected function autoloadPlugin(string $plugin, ClassLoader $composer, array $composerJson): void
    {
        foreach ($composerJson['autoload']['psr-4'] ?? [] as $namespace => $path) {
            if (!array_key_exists($namespace, $composer->getClassMap())) {
                $composer->addPsr4($namespace, $this->path($plugin, $path));
            }
        }

        foreach ($composerJson['autoload']['files'] ?? [] as $file) {
            $this->files->getRequire($this->path($plugin, $file));
        }
    }

    protected function runMigrations(string $plugin): void
    {
        $migrationsPath = $this->path($plugin, 'database/migrations');

        if ($this->files->isDirectory($migrationsPath)) {
            app('migrator')->run([$migrationsPath]);
        }
    }

    protected function createAssetsLink(string $plugin): void
    {
        $assetsPath = $this->path($plugin, 'assets');
        $linkPath   = $this->pluginsPublicPath . $plugin;

        if ($this->files->exists($assetsPath) && !$this->files->exists($linkPath)) {
            if (!$this->files->isDirectory($this->pluginsPublicPath)) {
                $this->files->makeDirectory($this->pluginsPublicPath, 0755, true);
            }
            symlink($assetsPath, $linkPath);
        }
    }
}
