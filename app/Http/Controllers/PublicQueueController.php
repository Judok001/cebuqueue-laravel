<?php

namespace App\Http\Controllers;

use App\Actions\CreateQueueTicket;
use App\Http\Requests\ReserveQueueRequest;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicQueueController extends Controller
{
    public function create(): View
    {
        return view('public.reserve', [
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ReserveQueueRequest $request, CreateQueueTicket $createQueueTicket): RedirectResponse
    {
        $validated = $request->validated();
        $ticket = DB::transaction(function () use ($validated, $createQueueTicket): QueueTicket {
            $patient = Patient::create([
                'name' => $validated['patient_name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'age' => $validated['age'],
            ]);

            return $createQueueTicket->handle(
                $patient,
                Service::query()->findOrFail($validated['service_id']),
                QueueTicket::SOURCE_ONLINE,
            );
        });

        return redirect()->route('ticket.show', $ticket->public_token);
    }

    public function ticket(QueueTicket $queueTicket): View
    {
        $queueTicket->load('service');

        return view('public.ticket', ['ticket' => $queueTicket]);
    }

    public function nowServing(): View
    {
        $today = now()->toDateString();
        $services = Service::query()
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
                    ->inPriorityOrder();
            }])
            ->orderBy('name')
            ->get();

        return view('public.now-serving', ['services' => $services]);
    }
}
