<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_service(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('services.store'), [
                'name' => 'General Consultation',
                'code' => 'gen',
                'estimated_minutes' => 20,
            ])
            ->assertRedirect(route('services-staff'));

        $service = Service::query()->where('name', 'General Consultation')->firstOrFail();
        $this->assertSame('GEN', $service->code);
        $this->assertSame(20, $service->estimated_minutes);

        $this->patch(route('services.update', $service), [
            'name' => 'General Consultation',
            'code' => 'GEN',
            'estimated_minutes' => 25,
            'is_active' => false,
        ])->assertRedirect(route('services-staff'));

        $service->refresh();
        $this->assertSame(25, $service->estimated_minutes);
        $this->assertFalse($service->is_active);
    }

    public function test_staff_cannot_create_services_or_view_admin_reports(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->post(route('services.store'), [
                'name' => 'Restricted Service',
                'code' => 'RST',
                'estimated_minutes' => 10,
            ])
            ->assertForbidden();

        $this->get(route('reports'))->assertForbidden();
        $this->assertDatabaseCount('services', 0);
    }

    public function test_service_code_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Service::factory()->create(['code' => 'GEN']);

        $this->actingAs($admin)
            ->from(route('services-staff'))
            ->post(route('services.store'), [
                'name' => 'Another Service',
                'code' => 'GEN',
                'estimated_minutes' => 10,
            ])
            ->assertRedirect(route('services-staff'))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('services', ['name' => 'Another Service']);
    }
}
