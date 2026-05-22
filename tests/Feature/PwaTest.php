<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_accessible()
    {
        $response = $this->get('/manifest.json');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'short_name',
            'icons',
            'start_url',
            'display',
            'theme_color',
            'background_color',
        ]);

        $json = $response->json();
        $this->assertEquals('/', $json['id']);
        $this->assertEquals('/', $json['start_url']);
        $this->assertEquals('/', $json['scope']);
    }

    public function test_manifest_reflects_tenant_data()
    {
        $tenant = Tenant::factory()->create([
            'domain' => 'test.localhost',
            'app_name' => 'Test App',
            'theme_color' => '#123456',
        ]);

        // Simulating the IdentifyTenant middleware behavior or helper
        session(['tenant_id' => $tenant->id]);
        app()->instance('tenant', $tenant);

        $response = $this->get('/manifest.json');

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals('Test App', $json['name']);
        $this->assertEquals('#123456', $json['theme_color']);
    }

    public function test_service_worker_is_accessible()
    {
        // Skip in testing if we can't easily serve public files through the application
        // In many Laravel setups, public files are served by the web server (Nginx/Apache)
        // and might not be routed through Laravel in a feature test unless we use a specific approach.

        $path = public_path('sw.js');
        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('self.addEventListener(\'install\'', $content);
        $this->assertStringContainsString('manifest.json', $content);
    }

    public function test_head_contains_pwa_install_logic()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)
            ->get('/workout-logs')
            ->assertStatus(200)
            ->assertSee('window.deferredPrompt = null;', false)
            ->assertSee('window.addEventListener(\'beforeinstallprompt\'', false)
            ->assertSee('function installPwa()', false);
    }

    public function test_header_contains_install_button_markup()
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)
            ->get('/workout-logs');

        $response->assertStatus(200);
        $response->assertSee('pwa-installable', false);
        $response->assertSee('installPwa', false);
        $response->assertSee('pwa-install-label', false);
    }
}
