<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\Sectors\Create as SectorCreate;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboards\Create as DashboardCreate;
use App\Models\Dashboard;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * A basic test example.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboards');
        $this->get('/dashboard')->assertRedirect('/dashboards');
        $this->get('/dashboards')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_see_dashboard(): void
    {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)
            ->get('/dashboards')
            ->assertOk()
            ->assertSee('Dashboards do setor');
    }

    public function test_admin_middleware_blocks_setor_users(): void
    {
        $user = User::factory()->create(['role' => UserRole::Setor]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_create_sector_with_registration_code(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(SectorCreate::class)
            ->set('name', 'SUENG')
            ->set('description', 'Setor de obras e engenharia.')
            ->call('save')
            ->assertRedirect(route('admin.sectors.index'));

        $sector = Sector::query()->where('name', 'SUENG')->first();

        $this->assertNotNull($sector);
        $this->assertNotEmpty($sector->registration_code);
        $this->assertTrue($sector->is_active);
    }

    public function test_setor_user_cannot_access_sector_admin_pages(): void
    {
        $user = User::factory()->create(['role' => UserRole::Setor]);

        $this->actingAs($user)
            ->get('/admin/setores')
            ->assertForbidden();
    }

    public function test_user_registers_with_active_sector_code(): void
    {
        $sector = Sector::factory()->create([
            'registration_code' => 'SEDUC-ABC12345',
            'is_active' => true,
        ]);

        Livewire::test(Register::class)
            ->set('name', 'Maria Silva')
            ->set('email', 'maria@example.com')
            ->set('registrationCode', 'seduc-abc12345')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('dashboards.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => UserRole::Setor->value,
            'sector_id' => $sector->id,
        ]);
    }

    public function test_user_cannot_register_with_invalid_sector_code(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Carlos Souza')
            ->set('email', 'carlos@example.com')
            ->set('registrationCode', 'CODIGO-INVALIDO')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['registrationCode']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'carlos@example.com',
        ]);
    }

    public function test_sector_user_can_create_dashboard(): void
    {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user);

        Livewire::test(DashboardCreate::class)
            ->set('name', 'Panorama de Obras')
            ->set('description', 'Acompanhamento inicial de obras do setor.')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('dashboards', [
            'name' => 'Panorama de Obras',
            'sector_id' => $sector->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);
    }

    public function test_sector_user_only_sees_dashboards_from_own_sector(): void
    {
        $sector = Sector::factory()->create();
        $otherSector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $otherUser = User::factory()->create(['sector_id' => $otherSector->id]);

        Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
            'name' => 'Dashboard do Meu Setor',
        ]);

        Dashboard::factory()->create([
            'sector_id' => $otherSector->id,
            'user_id' => $otherUser->id,
            'name' => 'Dashboard de Outro Setor',
        ]);

        $this->actingAs($user)
            ->get('/dashboards')
            ->assertOk()
            ->assertSee('Dashboard do Meu Setor')
            ->assertDontSee('Dashboard de Outro Setor');
    }

    public function test_sector_user_cannot_open_dashboard_from_another_sector(): void
    {
        $sector = Sector::factory()->create();
        $otherSector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $otherUser = User::factory()->create(['sector_id' => $otherSector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $otherSector->id,
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboards.show', $dashboard))
            ->assertForbidden();
    }
}
