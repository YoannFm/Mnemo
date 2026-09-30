<?php

namespace Tests\Feature;

use App\Extensions\UpdateManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateManagerTest extends TestCase
{
    use RefreshDatabase;

    private const RELEASE_URL = 'https://api.github.com/repos/YoannFm/Mnemo/releases/latest';
    private const ARCHIVE_URL = 'https://github.com/YoannFm/Mnemo/releases/download/v9.0.0/mnemo.zip';

    private function fakeRelease(array $assets): void
    {
        Http::fake([
            self::RELEASE_URL => Http::response(['tag_name' => 'v9.0.0', 'assets' => $assets]),
            self::ARCHIVE_URL => Http::response('zip-content'),
        ]);
    }

    private function releaseAsset(): array
    {
        return ['name' => 'mnemo.zip', 'browser_download_url' => self::ARCHIVE_URL];
    }

    public function test_latest_version_is_read_from_the_github_release(): void
    {
        $this->fakeRelease([$this->releaseAsset()]);

        $updates = app(UpdateManager::class);

        $this->assertSame('9.0.0', $updates->getLatestVersion());
        $this->assertTrue($updates->hasUpdate());
        Http::assertSent(fn (Request $request) => $request->hasHeader('User-Agent', 'Mnemo/' . config('mnemo.version')));
    }

    public function test_release_without_the_archive_is_ignored(): void
    {
        $this->fakeRelease([['name' => 'autre.zip', 'browser_download_url' => self::ARCHIVE_URL]]);

        $updates = app(UpdateManager::class);

        $this->assertNull($updates->getLatestVersion());
        $this->assertFalse($updates->hasUpdate());
        // Une release inexploitable n'est demandée qu'une fois par requête
        Http::assertSentCount(1);
    }

    public function test_unreachable_github_means_no_update(): void
    {
        Http::fake([self::RELEASE_URL => Http::failedConnection()]);

        $this->assertNull(app(UpdateManager::class)->getLatestVersion());
    }

    public function test_download_stores_the_release_archive(): void
    {
        $this->fakeRelease([$this->releaseAsset()]);
        $zip = storage_path('app/updates/mnemo-9.0.0.zip');

        try {
            $updates = app(UpdateManager::class);
            $updates->download();

            $this->assertTrue($updates->isDownloaded());
            $this->assertSame('zip-content', File::get($zip));
        } finally {
            File::delete($zip);
        }
    }

    public function test_update_page_shows_the_latest_release(): void
    {
        $this->fakeRelease([$this->releaseAsset()]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/update')
            ->assertOk()
            ->assertSee('v9.0.0')
            ->assertSee('Télécharger la mise à jour');
    }
}
