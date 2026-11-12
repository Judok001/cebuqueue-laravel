<?php

namespace App\Http\Controllers;

use App\Actions\CreateQueueTicket;
use App\Http\Requests\StoreQueueTicketRequest;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $queueServices = Service::query()
            ->where(function (Builder $query) use ($today): void {
                $query->where('is_active', true)
                    ->orWhereHas('queueTickets', function (Builder $query) use ($today): void {
                        $query->whereDate('queue_date', $today)
                            ->whereIn('status', [QueueTicket::STATUS_WAITING, QueueTicket::STATUS_SERVING]);
                    });
            })
            ->with(['queueTickets' => function (HasMany $query) use ($today): void {
                $query->whereDate('queue_date', $today)
                    ->whereIn('status', [QueueTicket::STATUS_WAITING, QueueTicket::STATUS_SERVING])
                    ->with('patient')
                    ->inPriorityOrder();
            }])
            ->orderBy('name')
            ->get();

        return view('queue.index', [
            'patients' => Patient::query()->orderByDesc('id')->limit(500)->get(),
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
            'priorities' => QueueTicket::PRIORITIES,
            'queueServices' => $queueServices,
        ]);
    }

    public function store(StoreQueueTicketRequest $request, CreateQueueTicket $createQueueTicket): RedirectResponse
    {
        $validated = $request->validated();
        $ticket = $createQueueTicket->handle(
            Patient::query()->findOrFail($validated['patient_id']),
            Service::query()->findOrFail($validated['service_id']),
            QueueTicket::SOURCE_WALK_IN,
            $validated['priority'],
        );

        return redirect()->route('ticket.show', $ticket->public_token);
    }

    public function callNext(Service $service): RedirectResponse
    {
        $ticket = DB::transaction(function () use ($service): ?QueueTicket {
            Service::query()->whereKey($service->getKey())->lockForUpdate()->firstOrFail();

            $currentlyServing = QueueTicket::query()
                ->where('service_id', $service->getKey())
                ->whereDate('queue_date', now()->toDateString())
                ->where('status', QueueTicket::STATUS_SERVING)
                ->lockForUpdate()
                ->exists();

            if ($currentlyServing) {
                return null;
            }

            $ticket = QueueTicket::query()
                ->where('service_id', $service->getKey())
                ->whereDate('queue_date', now()->toDateString())
                ->where('status', QueueTicket::STATUS_WAITING)
                ->inPriorityOrder()
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                return null;
            }

            $ticket->update([
                'status' => QueueTicket::STATUS_SERVING,
                'handled_by' => auth()->id(),
                'called_at' => now(),
            ]);

            return $ticket;
        });

        if ($ticket === null) {
            return back()->withErrors(['queue' => 'There is already a patient being served, or no patients are waiting.']);
        }

        return back()->with('status', 'Now serving '.$ticket->ticket_code.'.');
    }

    public function markServed(QueueTicket $queueTicket): RedirectResponse
    {
        return $this->finishTicket($queueTicket, QueueTicket::STATUS_SERVED);
    }

    public function markAbsent(QueueTicket $queueTicket): RedirectResponse
    {
        return $this->finishTicket($queueTicket, QueueTicket::STATUS_ABSENT);
    }

    private function finishTicket(QueueTicket $ticket, string $status): RedirectResponse
    {
        $updated = DB::transaction(function () use ($ticket, $status): bool {
            $lockedTicket = QueueTicket::query()
                ->whereKey($ticket->getKey())
                ->whereDate('queue_date', now()->toDateString())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTicket->status !== QueueTicket::STATUS_SERVING) {
                return false;
            }

            $lockedTicket->update([
                'status' => $status,
                'served_at' => now(),
            ]);

            return true;
        });

        if (! $updated) {
            return back()->withErrors(['queue' => 'Only the currently serving patient can be marked served or absent.']);
        }

        return back()->with('status', $status === QueueTicket::STATUS_SERVED
            ? 'Patient marked as served.'
            : 'Patient marked as absent.');
    }
}
