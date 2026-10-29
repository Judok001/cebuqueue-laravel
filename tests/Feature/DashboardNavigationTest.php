<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_navigation_pages_render_for_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([
            'queue' => 'Queue',
            'patients' => 'Patients',
            'now-serving' => 'Now serving board',
        ] as $route => $title) {
            $this->get(route($route))
                ->assertOk()
                ->assertSeeText($title)
                ->assertSee('href="'.route($route).'"', false);
        }
    }
}
