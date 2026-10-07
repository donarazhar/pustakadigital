<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

class PwaAndOfflineModeTest extends TestCase
{
    public function test_manifest_json_is_valid_and_accessible(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $jsonContent = file_get_contents($manifestPath);
        $manifest = json_decode($jsonContent, true);

        $this->assertIsArray($manifest);
        $this->assertEquals('Pustaka Digital Sekolah', $manifest['name']);
        $this->assertEquals('PustakaDigital', $manifest['short_name']);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertEquals('/', $manifest['start_url']);
        $this->assertNotEmpty($manifest['icons']);
        $this->assertNotEmpty($manifest['shortcuts']);
    }

    public function test_service_worker_and_offline_manager_files_exist(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);
        $swContent = file_get_contents($swPath);
        $this->assertStringContainsString('Pustaka Digital Sekolah - Service Worker', $swContent);
        $this->assertStringContainsString('pustaka-offline-books', $swContent);

        $offlineManagerPath = public_path('js/offline-manager.js');
        $this->assertFileExists($offlineManagerPath);
        $managerContent = file_get_contents($offlineManagerPath);
        $this->assertStringContainsString('PustakaOfflineDB', $managerContent);
        $this->assertStringContainsString('OfflineManager', $managerContent);
    }

    public function test_pwa_icons_exist_in_public_directory(): void
    {
        $iconSizes = ['72x72', '96x96', '128x128', '144x144', '152x152', '192x192', '384x384', '512x512'];
        foreach ($iconSizes as $size) {
            $iconFile = public_path("images/icons/icon-{$size}.png");
            $this->assertFileExists($iconFile, "Ikon PWA resolusi {$size} harus tersedia di public/images/icons/");
        }
    }

    public function test_offline_page_route_renders_correctly(): void
    {
        $response = $this->get('/offline');
        $response->assertStatus(200);
        $response->assertSee('Mode Offline - Pustaka Digital Sekolah');
        $response->assertSee('Mode Tanpa Internet (Offline)');
        $response->assertSee('offline-books-container', false);
        $response->assertSee('offline-manager.js', false);
        $response->assertSee('manifest.json', false);
    }

    public function test_homepage_includes_pwa_manifest_and_install_controls(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('rel="manifest" href="/manifest.json"', false);
        $response->assertSee('offline-manager.js', false);
        $response->assertSee('btn-pwa-install', false);
        $response->assertSee('offline-network-banner', false);
    }

    public function test_book_reader_includes_offline_save_button_and_payload(): void
    {
        $book = Book::first();
        $this->assertNotNull($book);

        $user = User::first() ?? User::factory()->create();
        $response = $this->actingAs($user)->get("/baca/{$book->slug}");
        $response->assertStatus(200);

        // Harus memuat meta manifest dan offline-manager.js di reader
        $response->assertSee('manifest.json', false);
        $response->assertSee('offline-manager.js', false);

        // Harus ada tombol Simpan Offline dengan attribute ID buku
        $response->assertSee('btn-offline-save', false);
        $response->assertSee("data-offline-book-id=\"{$book->id}\"", false);
        $response->assertSee('Simpan Offline');

        // Harus ada payload data buku offline dan fungsi save
        $response->assertSee('offline-book-payload', false);
        $response->assertSee('window.saveCurrentBookOffline', false);
        $response->assertSee('offline-network-banner', false);
    }
}
