<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QueueTicket>
 */
class QueueTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_token' => Str::uuid()->toString(),
            'patient_id' => Patient::factory(),
            'service_id' => Service::factory(),
            'queue_date' => now()->toDateString(),
            'sequence' => fake()->unique()->numberBetween(1, 9999),
            'ticket_code' => fake()->unique()->bothify('GEN-######'),
            'status' => QueueTicket::STATUS_WAITING,
            'source' => QueueTicket::SOURCE_WALK_IN,
            'priority' => QueueTicket::PRIORITY_REGULAR,
        ];
    }
}
