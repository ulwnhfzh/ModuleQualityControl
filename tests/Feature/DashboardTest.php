<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_operational_summary_instead_of_quick_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Komposisi Daftar Induk')
            ->assertSee('Rekapitulasi Data Tahun')
            ->assertDontSee('Akses Cepat');
    }

    public function test_user_dashboard_shows_operational_summary_instead_of_shortcuts(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Komposisi Daftar Induk')
            ->assertSee('Ringkasan Data')
            ->assertDontSee('Shortcut (Lihat Data)');
    }
}
