<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'Clinic Staff',
            'email' => 'clinic.staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('services-staff'));
        $this->assertDatabaseHas('users', [
            'name' => 'Clinic Staff',
            'email' => 'clinic.staff@example.com',
            'role' => User::ROLE_STAFF,
        ]);
        $this->assertTrue(Hash::check('password123', User::where('email', 'clinic.staff@example.com')->firstOrFail()->password));
    }

    public function test_staff_cannot_access_admin_only_pages_or_create_staff(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('services-staff'))
            ->assertForbidden();

        $this->post(route('staff.store'), [
            'name' => 'Unauthorized Staff',
            'email' => 'unauthorized@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
    }

    public function test_staff_dashboard_hides_admin_navigation(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Staff dashboard')
            ->assertSee(route('queue'), false)
            ->assertSee(route('patients'), false)
            ->assertSee(route('now-serving'), false)
            ->assertDontSee(route('services-staff'), false)
            ->assertDontSee(route('reports'), false);
    }

    public function test_admin_dashboard_shows_admin_navigation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Admin dashboard')
            ->assertSee(route('services-staff'), false)
            ->assertSee(route('reports'), false);
    }
}
