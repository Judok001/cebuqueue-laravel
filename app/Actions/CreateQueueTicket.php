<?php

namespace App\Actions;

use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateQueueTicket
{
    public function handle(
        Patient $patient,
        Service $service,
        string $source,
        string $priority = QueueTicket::PRIORITY_REGULAR,
    ): QueueTicket {
        return DB::transaction(function () use ($patient, $service, $source, $priority): QueueTicket {
            $lockedService = Service::query()
                ->whereKey($service->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedService->is_active) {
                throw ValidationException::withMessages([
                    'service_id' => 'This service is not currently accepting queue tickets.',
                ]);
            }

            $queueDate = now()->toDateString();
            $sequence = (int) QueueTicket::query()
                ->where('service_id', $lockedService->getKey())
                ->where('queue_date', $queueDate)
                ->max('sequence') + 1;

            return QueueTicket::create([
                'public_token' => (string) Str::uuid(),
                'patient_id' => $patient->getKey(),
                'service_id' => $lockedService->getKey(),
                'queue_date' => $queueDate,
                'sequence' => $sequence,
                'ticket_code' => sprintf('%s-%s-%04d', $lockedService->code, now()->format('ymd'), $sequence),
                'status' => QueueTicket::STATUS_WAITING,
                'source' => $source,
                'priority' => $priority,
            ]);
        });
    }
}
