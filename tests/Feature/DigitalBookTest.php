<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DigitalBookTest extends TestCase
{
    public function test_homepage_shows_bookshelf_and_seeded_book(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pustaka Digital Sekolah');
        $response->assertSee('Petualangan Sains: Menguak Rahasia Tata Surya');
    }

    public function test_interactive_reader_page_is_accessible(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/baca/petualangan-sains-mengenal-tata-surya');

        $response->assertStatus(200);
        $response->assertSee('Matahari: Sang Bintang Raksasa Bercahaya');
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::where('email', 'admin@sekolah.id')->first();

        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Buku Interaktif 3D');
    }
}
