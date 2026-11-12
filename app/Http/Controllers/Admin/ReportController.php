<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);
        $date = $validated['date'] ?? now()->toDateString();

        $tickets = QueueTicket::query()
            ->whereDate('queue_date', $date)
            ->when($validated['service_id'] ?? null, fn (Builder $query, int $serviceId) => $query->where('service_id', $serviceId))
            ->with('service')
            ->orderBy('created_at')
            ->get();

        $rows = Service::query()
            ->orderBy('name')
            ->get()
            ->map(function (Service $service) use ($tickets): array {
                $serviceTickets = $tickets->where('service_id', $service->getKey());
                $servedTickets = $serviceTickets->where('status', QueueTicket::STATUS_SERVED);
                $waitTimes = $serviceTickets
                    ->filter(fn (QueueTicket $ticket): bool => $ticket->called_at !== null)
                    ->map(fn (QueueTicket $ticket): int => (int) $ticket->created_at->diffInMinutes($ticket->called_at));

                return [
                    'service' => $service,
                    'total' => $serviceTickets->count(),
                    'served' => $servedTickets->count(),
                    'waiting' => $serviceTickets->where('status', QueueTicket::STATUS_WAITING)->count(),
                    'absent' => $serviceTickets->where('status', QueueTicket::STATUS_ABSENT)->count(),
                    'average_wait' => $waitTimes->isEmpty() ? null : (int) round($waitTimes->avg()),
                ];
            })
            ->filter(fn (array $row): bool => $row['total'] > 0 || ! isset($validated['service_id']))
            ->values();

        return view('admin.reports', [
            'date' => $date,
            'services' => Service::query()->orderBy('name')->get(),
            'rows' => $rows,
            'totalTickets' => $tickets->count(),
            'totalServed' => $tickets->where('status', QueueTicket::STATUS_SERVED)->count(),
            'totalWaiting' => $tickets->where('status', QueueTicket::STATUS_WAITING)->count(),
            'priorityCounts' => collect(QueueTicket::PRIORITIES)
                ->map(fn (string $label, string $priority): array => [
                    'label' => $label,
                    'count' => $tickets->where('priority', $priority)->count(),
                ]),
            'averageWait' => $tickets
                ->filter(fn (QueueTicket $ticket): bool => $ticket->called_at !== null)
                ->map(fn (QueueTicket $ticket): int => (int) $ticket->created_at->diffInMinutes($ticket->called_at))
                ->avg(),
        ]);
    }
}
