<?php

namespace Tests\Feature;

use App\Extensions\Plugin\PluginManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class PluginCatalogTest extends TestCase
{
    use RefreshDatabase;

    private const ARCHIVE_URL = 'https://github.com/exemple/demo-plugin/releases/download/v1.2.0/demo-plugin.zip';

    private function fakeCatalog(array $plugins, ?string $archive = null): void
    {
        Http::fake([
            config('mnemo.github.plugins_catalog') => Http::response(['plugins' => $plugins]),
            self::ARCHIVE_URL => Http::response($archive ?? ''),
        ]);
    }

    private function demoPlugin(array $overrides = []): array
    {
        return array_merge([
            'slug'         => 'demo-plugin',
            'name'         => 'Démo',
            'description'  => 'Plugin de démonstration',
            'author'       => 'Exemple',
            'version'      => '1.2.0',
            'download_url' => self::ARCHIVE_URL,
        ], $overrides);
    }

    private function pluginArchive(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'plugin');
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('plugin.json', json_encode(['id' => 'demo-plugin', 'name' => 'Démo', 'version' => '1.2.0']));
        $zip->close();

        $content = file_get_contents($path);
        unlink($path);

        return $content;
    }

    public function test_available_plugins_are_read_from_the_catalog(): void
    {
        $this->fakeCatalog([
            $this->demoPlugin(),
            $this->demoPlugin(['slug' => 'streak', 'name' => 'Streak']),
            $this->demoPlugin(['slug' => '../evil']),
            $this->demoPlugin(['slug' => 'no-https', 'download_url' => 'http://exemple.com/p.zip']),
            $this->demoPlugin(['slug' => 'no-version', 'version' => null]),
        ]);

        $available = app(PluginManager::class)->getAvailablePlugins()->keyBy('slug');

        $this->assertSame(['demo-plugin', 'streak'], $available->keys()->all());
        $this->assertFalse($available['demo-plugin']->is_installed);
        $this->assertTrue($available['streak']->is_installed);
    }

    public function test_unreachable_catalog_means_no_plugin(): void
    {
        Http::fake([config('mnemo.github.plugins_catalog') => Http::failedConnection()]);

        $this->assertTrue(app(PluginManager::class)->getAvailablePlugins()->isEmpty());
    }

    public function test_install_extracts_the_plugin_archive(): void
    {
        $this->fakeCatalog([$this->demoPlugin()], $this->pluginArchive());
        $pluginDir = base_path('plugins/demo-plugin');

        try {
            app(PluginManager::class)->install('demo-plugin');

            $this->assertFileExists($pluginDir . '/plugin.json');
            $this->assertSame('demo-plugin', app(PluginManager::class)->findDescription('demo-plugin')->id);
        } finally {
            File::deleteDirectory($pluginDir);
        }
    }

    public function test_install_rejects_a_plugin_missing_from_the_catalog(): void
    {
        $this->fakeCatalog([]);

        $this->expectException(RuntimeException::class);
        app(PluginManager::class)->install('demo-plugin');
    }

    public function test_plugins_page_lists_the_catalog(): void
    {
        $this->fakeCatalog([$this->demoPlugin()]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/plugins')
            ->assertOk()
            ->assertSee('Plugin de démonstration')
            ->assertSee(route('admin.plugins.install', 'demo-plugin'), false)
            // Les plugins fournis avec Mnemo, absents du catalogue, n'ont pas de bouton de mise à jour
            ->assertDontSee(route('admin.plugins.update', 'streak'), false);
    }
}
