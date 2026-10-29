<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_verified_admin_account(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $staff = User::where('email', 'staff@gmail.com')->firstOrFail();

        $this->assertSame('Admin', $admin->name);
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue(Hash::check('password123', $admin->password));
        $this->assertSame('Staff', $staff->name);
        $this->assertSame(User::ROLE_STAFF, $staff->role);
        $this->assertTrue($staff->hasVerifiedEmail());
        $this->assertTrue(Hash::check('password123', $staff->password));
    }

    public function test_database_seeder_can_be_run_more_than_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, User::count());
    }

    public function test_seeded_admin_and_staff_credentials_can_sign_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ([
            ['email' => 'admin@gmail.com', 'role' => 'Admin'],
            ['email' => 'staff@gmail.com', 'role' => 'Staff'],
        ] as $account) {
            $this->post('/login', [
                'email' => $account['email'],
                'password' => 'password123',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->get(route('dashboard'))
                ->assertOk()
                ->assertSeeText($account['role'].' dashboard');

            $this->post('/logout')->assertRedirect('/');
        }
    }
}
