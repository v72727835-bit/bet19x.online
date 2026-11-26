<?php

namespace Tests\Feature;

use App\Http\Controllers\AppDownloadController;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AppInstallTest extends TestCase
{
    private string $apk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apk = AppDownloadController::path();
        $this->backup = is_file($this->apk) ? file_get_contents($this->apk) : null;
    }

    protected function tearDown(): void
    {
        // leave public/downloads exactly as we found it
        if ($this->backup === null) {
            @unlink($this->apk);
        } else {
            file_put_contents($this->apk, $this->backup);
        }
        parent::tearDown();
    }

    private ?string $backup = null;

    private function putApk(): void
    {
        File::ensureDirectoryExists(dirname($this->apk));
        file_put_contents($this->apk, str_repeat('A', 2048));
    }

    public function test_manifest_is_valid_and_installable(): void
    {
        $r = $this->get('/manifest.webmanifest')->assertOk();
        $this->assertStringContainsString('application/manifest+json', $r->headers->get('Content-Type'));
        $m = $r->json();

        $this->assertSame('standalone', $m['display']);
        $this->assertSame('/', $m['scope']);
        $this->assertNotEmpty($m['name']);
        $sizes = collect($m['icons'])->pluck('sizes')->all();
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);
        $this->assertContains('maskable', collect($m['icons'])->pluck('purpose')->all());
        foreach ($m['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_service_worker_and_offline_page_exist_and_never_cache_pages(): void
    {
        $sw = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("req.mode === 'navigate'", $sw);
        $this->assertStringContainsString("req.method !== 'GET'", $sw);
        // pages are fetched from the network and never put in a cache
        $this->assertDoesNotMatchRegularExpression("/navigate'\\)\\s*\\{[^}]*cache\\.put/s", $sw);
        $this->assertFileExists(public_path('offline.html'));
    }

    public function test_pages_link_the_manifest_and_register_the_service_worker(): void
    {
        $this->get('/dashboard')->assertOk()->assertSee('rel="manifest"', false)->assertSee('/sw.js', false)->assertSee('apple-touch-icon', false);
        $this->get('/login')->assertOk()->assertSee('rel="manifest"', false);
    }

    public function test_download_serves_the_apk_with_the_right_headers(): void
    {
        $this->putApk();

        $r = $this->get('/app/download')->assertOk();
        $this->assertSame('application/vnd.android.package-archive', $r->headers->get('Content-Type'));
        $this->assertStringContainsString('BET19X.apk', $r->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $r->headers->get('X-Content-Type-Options'));
    }

    public function test_download_without_a_file_redirects_home_with_a_message(): void
    {
        @unlink($this->apk);
        $this->get('/app/download')->assertRedirect(route('dashboard'))->assertSessionHas('error');
    }

    public function test_home_page_shows_the_download_button_only_when_the_apk_exists(): void
    {
        @unlink($this->apk);
        $this->get('/dashboard')->assertOk()->assertSee('bx-appcard', false)->assertDontSee('Download APK');

        $this->putApk();
        $this->get('/dashboard')->assertOk()->assertSee('Download APK')->assertSee(route('app.download'), false)->assertSee('Install app');
    }
}
