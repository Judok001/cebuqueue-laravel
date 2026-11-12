<?php

namespace App\Http\Controllers;

use App\Actions\CreateQueueTicket;
use App\Http\Requests\StorePatientRequest;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $patients = Patient::query()
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%');
                });
            })
            ->with(['queueTickets' => function (HasMany $query) use ($today): void {
                $query->whereDate('queue_date', $today)
                    ->whereIn('status', [QueueTicket::STATUS_WAITING, QueueTicket::STATUS_SERVING])
                    ->with('service')
                    ->inPriorityOrder();
            }])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
            'priorities' => QueueTicket::PRIORITIES,
        ]);
    }

    public function store(StorePatientRequest $request, CreateQueueTicket $createQueueTicket): RedirectResponse
    {
        $validated = $request->validated();
        $ticket = DB::transaction(function () use ($validated, $request, $createQueueTicket): QueueTicket {
            $patient = Patient::create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'age' => $validated['age'],
                'registered_by' => $request->user()->getKey(),
            ]);

            return $createQueueTicket->handle(
                $patient,
                Service::query()->findOrFail($validated['service_id']),
                QueueTicket::SOURCE_WALK_IN,
                $validated['priority'],
            );
        });

        return redirect()
            ->route('ticket.show', $ticket->public_token)
            ->with('status', 'Patient registered and added to the queue.');
    }
}
