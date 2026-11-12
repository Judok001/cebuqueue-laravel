<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_register_a_patient_and_automatically_create_a_priority_queue_ticket(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create();
        $this->actingAs($staff);

        $response = $this->post(route('patients.store'), [
            'name' => 'Walk-in Patient',
            'address' => 'Cebu City',
            'phone' => '09170000000',
            'age' => 68,
            'service_id' => $service->id,
            'priority' => QueueTicket::PRIORITY_SENIOR,
        ]);

        $patient = Patient::query()->where('name', 'Walk-in Patient')->firstOrFail();
        $ticket = QueueTicket::query()->where('patient_id', $patient->id)->firstOrFail();

        $response->assertRedirect(route('ticket.show', $ticket->public_token));
        $this->assertSame('Cebu City', $patient->address);
        $this->assertSame(68, $patient->age);
        $this->assertDatabaseHas('queue_tickets', [
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'source' => QueueTicket::SOURCE_WALK_IN,
            'status' => QueueTicket::STATUS_WAITING,
            'priority' => QueueTicket::PRIORITY_SENIOR,
        ]);
    }

    public function test_staff_registration_requires_patient_details_service_and_priority(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)
            ->from(route('patients'))
            ->post(route('patients.store'), ['name' => 'Incomplete Patient'])
            ->assertRedirect(route('patients'))
            ->assertSessionHasErrors(['address', 'phone', 'age', 'service_id', 'priority']);

        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('queue_tickets', 0);
    }

    public function test_staff_cannot_register_a_patient_with_an_unknown_priority(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create();

        $this->actingAs($staff)
            ->from(route('patients'))
            ->post(route('patients.store'), [
                'name' => 'Invalid Priority Patient',
                'address' => 'Cebu City',
                'phone' => '09170000000',
                'age' => 40,
                'service_id' => $service->id,
                'priority' => 'unknown',
            ])
            ->assertRedirect(route('patients'))
            ->assertSessionHasErrors('priority');

        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('queue_tickets', 0);
    }

    public function test_registered_patient_can_be_queued_from_patient_list_and_called_from_dashboard(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $patient = Patient::factory()->create(['name' => 'Dashboard Patient']);
        $service = Service::factory()->create(['name' => 'General Consultation']);
        $this->actingAs($staff);

        $this->get(route('patients'))
            ->assertOk()
            ->assertSeeText($patient->name)
            ->assertSeeText('Add to queue')
            ->assertSeeText($service->name);

        $this->post(route('queue.store'), [
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'priority' => QueueTicket::PRIORITY_REGULAR,
        ])->assertRedirect();

        $ticket = QueueTicket::query()->where('patient_id', $patient->id)->firstOrFail();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText($ticket->ticket_code)
            ->assertSeeText($patient->name)
            ->assertSeeText('Call next patient');

        $this->from(route('dashboard'))
            ->post(route('queue.call-next', $service))
            ->assertRedirect(route('dashboard'));

        $ticket->refresh();
        $this->assertSame(QueueTicket::STATUS_SERVING, $ticket->status);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Now serving')
            ->assertSeeText($ticket->ticket_code)
            ->assertSeeText('Mark served');
    }

    public function test_patient_list_and_dashboard_explain_when_no_services_are_active(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $patient = Patient::factory()->create();

        $this->actingAs($admin)
            ->get(route('patients'))
            ->assertOk()
            ->assertSeeText('No active services.')
            ->assertSeeText('Ask an admin to activate a service');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('There are no active services yet')
            ->assertSeeText('Activate a service before adding or calling patients.');
    }

    public function test_staff_can_call_next_and_mark_the_patient_as_served(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create();
        $patient = Patient::factory()->create();
        $ticket = QueueTicket::factory()->for($patient)->for($service)->create([
            'sequence' => 1,
            'ticket_code' => 'GEN-260101-0001',
        ]);

        $this->actingAs($staff)
            ->post(route('queue.call-next', $service))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame(QueueTicket::STATUS_SERVING, $ticket->status);
        $this->assertSame($staff->id, $ticket->handled_by);
        $this->assertNotNull($ticket->called_at);

        $this->post(route('queue.served', $ticket))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame(QueueTicket::STATUS_SERVED, $ticket->status);
        $this->assertNotNull($ticket->served_at);
    }

    public function test_call_next_selects_emergency_before_older_regular_tickets(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create();
        $regularPatient = Patient::factory()->create();
        $emergencyPatient = Patient::factory()->create();
        $regularTicket = QueueTicket::factory()->for($regularPatient)->for($service)->create([
            'priority' => QueueTicket::PRIORITY_REGULAR,
            'created_at' => now()->subMinutes(2),
        ]);
        $emergencyTicket = QueueTicket::factory()->for($emergencyPatient)->for($service)->create([
            'priority' => QueueTicket::PRIORITY_EMERGENCY,
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($staff)
            ->post(route('queue.call-next', $service))
            ->assertRedirect();

        $this->assertDatabaseHas('queue_tickets', [
            'id' => $emergencyTicket->id,
            'status' => QueueTicket::STATUS_SERVING,
        ]);
        $this->assertDatabaseHas('queue_tickets', [
            'id' => $regularTicket->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_staff_can_mark_a_called_patient_as_absent(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $ticket = QueueTicket::factory()->create([
            'status' => QueueTicket::STATUS_SERVING,
            'called_at' => now(),
        ]);

        $this->actingAs($staff)
            ->post(route('queue.absent', $ticket))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame(QueueTicket::STATUS_ABSENT, $ticket->status);
        $this->assertNotNull($ticket->served_at);
    }

    public function test_staff_cannot_call_another_patient_while_one_is_being_served(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create();
        QueueTicket::factory()->for($service)->create([
            'sequence' => 1,
            'status' => QueueTicket::STATUS_SERVING,
            'ticket_code' => 'GEN-260101-0001',
        ]);
        $waitingTicket = QueueTicket::factory()->for($service)->create([
            'sequence' => 2,
            'ticket_code' => 'GEN-260101-0002',
        ]);

        $this->actingAs($staff)
            ->from(route('queue'))
            ->post(route('queue.call-next', $service))
            ->assertRedirect(route('queue'))
            ->assertSessionHasErrors('queue');

        $this->assertDatabaseHas('queue_tickets', [
            'id' => $waitingTicket->id,
            'status' => QueueTicket::STATUS_WAITING,
        ]);
    }

    public function test_staff_can_finish_a_queue_after_admin_deactivates_its_service(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $service = Service::factory()->create(['is_active' => false]);
        $ticket = QueueTicket::factory()->for($service)->create([
            'queue_date' => now()->toDateString(),
            'sequence' => 1,
            'ticket_code' => 'GEN-260101-0001',
        ]);

        $this->actingAs($staff)
            ->get(route('queue'))
            ->assertOk()
            ->assertSeeText($ticket->ticket_code);

        $this->post(route('queue.call-next', $service))
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame(QueueTicket::STATUS_SERVING, $ticket->status);

        $this->get(route('now-serving'))
            ->assertOk()
            ->assertSeeText($ticket->ticket_code);
    }

    public function test_report_is_available_to_admin_and_summarizes_queue_activity(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $service = Service::factory()->create();
        QueueTicket::factory()->for($service)->create([
            'queue_date' => now()->toDateString(),
            'status' => QueueTicket::STATUS_SERVED,
            'created_at' => now()->subMinutes(20),
            'called_at' => now()->subMinutes(10),
            'served_at' => now(),
            'sequence' => 1,
            'ticket_code' => 'GEN-260101-0001',
        ]);

        $this->actingAs($admin)
            ->get(route('reports'))
            ->assertOk()
            ->assertSeeText($service->name)
            ->assertSeeText('1')
            ->assertSeeText('Priority category totals')
            ->assertSeeText('Regular')
            ->assertSeeText('10 min');
    }
}
