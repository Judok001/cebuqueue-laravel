<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_services_are_available_for_public_and_staff_queue_registration(): void
    {
        $inactiveServices = Service::factory()->createMany([
            ['name' => 'General Medical Consultations', 'code' => 'GENMED'],
            ['name' => 'Senior Citizen Vaccinations', 'code' => 'SCV'],
            ['name' => 'Senior Home Health Visits', 'code' => 'SHV'],
            ['name' => 'Prenatal Check-ups and Supplements', 'code' => 'PRENAT'],
            ['name' => 'Safe Birthing and Postnatal Care', 'code' => 'POSTN'],
        ]);
        $existingTicket = QueueTicket::factory()->for($inactiveServices->first())->create();

        $serviceNames = [
            'Childhood Immunizations',
            'Family Planning Services',
            'Basic Laboratory Diagnostics',
            'Infectious Disease Management',
            'Free Senior Maintenance Medicines',
        ];

        $this->seed(ServiceSeeder::class);

        $this->get(route('reserve.create'))
            ->assertOk()
            ->assertSeeText($serviceNames)
            ->assertDontSeeText('General Medical Consultations');

        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->get(route('patients'))
            ->assertOk()
            ->assertSeeText($serviceNames)
            ->assertDontSeeText('General Medical Consultations');

        $this->assertSame(5, Service::query()->where('is_active', true)->count());
        $this->assertModelExists($existingTicket);
        $this->assertDatabaseHas('services', [
            'id' => $inactiveServices->first()->id,
            'is_active' => false,
        ]);
    }

    public function test_public_patient_can_reserve_a_service_and_receive_a_ticket(): void
    {
        $service = Service::factory()->create([
            'code' => 'GEN',
            'estimated_minutes' => 12,
        ]);

        $response = $this->post(route('reserve.store'), [
            'patient_name' => 'Maya Santos',
            'address' => 'Cebu City',
            'phone' => '09171234567',
            'age' => 32,
            'service_id' => $service->id,
            'priority' => QueueTicket::PRIORITY_EMERGENCY,
        ]);

        $patient = Patient::query()->where('name', 'Maya Santos')->firstOrFail();
        $ticket = QueueTicket::query()->where('patient_id', $patient->id)->firstOrFail();

        $response->assertRedirect(route('ticket.show', $ticket->public_token));
        $this->assertDatabaseHas('queue_tickets', [
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'ticket_code' => $ticket->ticket_code,
            'status' => QueueTicket::STATUS_WAITING,
            'source' => QueueTicket::SOURCE_ONLINE,
            'sequence' => 1,
        ]);
        $this->assertSame('GEN-', substr($ticket->ticket_code, 0, 4));
        $this->assertSame('Cebu City', $patient->address);
        $this->assertSame(32, $patient->age);
        $this->assertSame(QueueTicket::PRIORITY_REGULAR, $ticket->priority);
    }

    public function test_public_reservation_rejects_an_inactive_service_without_creating_a_patient(): void
    {
        $service = Service::factory()->create(['is_active' => false]);

        $response = $this->from(route('reserve.create'))->post(route('reserve.store'), [
            'patient_name' => 'Maya Santos',
            'address' => 'Cebu City',
            'phone' => '09171234567',
            'age' => 32,
            'service_id' => $service->id,
        ]);

        $response->assertRedirect(route('reserve.create'));
        $response->assertSessionHasErrors('service_id');
        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('queue_tickets', 0);
    }

    public function test_public_ticket_displays_queue_position_and_estimated_wait(): void
    {
        $service = Service::factory()->create(['estimated_minutes' => 12]);
        $firstPatient = Patient::factory()->create();
        $secondPatient = Patient::factory()->create();

        QueueTicket::factory()->for($firstPatient)->for($service)->create([
            'sequence' => 1,
            'status' => QueueTicket::STATUS_SERVING,
            'ticket_code' => 'GEN-260101-0001',
            'called_at' => now(),
        ]);
        $waitingTicket = QueueTicket::factory()->for($secondPatient)->for($service)->create([
            'sequence' => 2,
            'status' => QueueTicket::STATUS_WAITING,
            'ticket_code' => 'GEN-260101-0002',
        ]);

        $this->get(route('ticket.show', $waitingTicket->public_token))
            ->assertOk()
            ->assertSeeText($waitingTicket->ticket_code)
            ->assertSeeText('Your queue position:')
            ->assertSeeText('about 12 minutes');
    }

    public function test_queue_position_and_estimated_wait_follow_priority_after_the_current_patient(): void
    {
        $service = Service::factory()->create(['estimated_minutes' => 12]);
        $servingPatient = Patient::factory()->create();
        $emergencyPatient = Patient::factory()->create();
        $regularPatient = Patient::factory()->create();

        QueueTicket::factory()->for($servingPatient)->for($service)->create([
            'status' => QueueTicket::STATUS_SERVING,
            'priority' => QueueTicket::PRIORITY_REGULAR,
            'created_at' => now()->subMinutes(3),
        ]);
        QueueTicket::factory()->for($emergencyPatient)->for($service)->create([
            'priority' => QueueTicket::PRIORITY_EMERGENCY,
            'created_at' => now()->subMinutes(2),
        ]);
        $regularTicket = QueueTicket::factory()->for($regularPatient)->for($service)->create([
            'priority' => QueueTicket::PRIORITY_REGULAR,
            'created_at' => now()->subMinute(),
        ]);

        $this->get(route('ticket.show', $regularTicket->public_token))
            ->assertOk()
            ->assertSeeText('Your queue position: 3')
            ->assertSeeText('about 24 minutes');
    }

    public function test_public_now_serving_board_shows_ticket_numbers_without_patient_names(): void
    {
        $service = Service::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Private Patient Name']);
        $ticket = QueueTicket::factory()->for($patient)->for($service)->create([
            'queue_date' => now()->toDateString(),
            'status' => QueueTicket::STATUS_SERVING,
            'ticket_code' => 'GEN-260101-0001',
        ]);

        $this->get(route('now-serving'))
            ->assertOk()
            ->assertSeeText($ticket->ticket_code)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSeeText('Staff login')
            ->assertDontSeeText($patient->name);
    }

    public function test_authenticated_staff_can_open_now_serving_board_in_the_staff_layout(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create();

        $this->actingAs($staff)
            ->get(route('now-serving'))
            ->assertOk()
            ->assertSeeText('Now serving')
            ->assertSeeText($service->name)
            ->assertSeeText('Back to dashboard')
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee('css/cebuqueue-ui.css', false);
    }
}
