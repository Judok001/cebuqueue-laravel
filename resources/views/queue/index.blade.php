@extends('layouts.cebu-app')

@section('content')
    <div class="top">
        <h2>Today's queue</h2>
        <p>Register walk-ins, call the next patient, and update ticket status.</p>
    </div>

    @if (session('status'))
        <div class="stat notice">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="stat error" role="alert">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <section class="stat form-card">
        <h3>Add a walk-in to the queue</h3>
        @if ($patients->isEmpty() || $services->isEmpty())
            <p>Register a patient and activate a service before adding a walk-in.</p>
            <div class="actions">
                <a href="{{ route('patients') }}" class="alt">Register patient</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('services-staff') }}" class="alt">Manage services</a>
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('queue.store') }}">
                @csrf
                <label for="patient_id">Patient</label>
                <select id="patient_id" name="patient_id" required>
                    <option value="">Choose a patient</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                            {{ $patient->name }}{{ $patient->phone ? ' · '.$patient->phone : '' }}
                        </option>
                    @endforeach
                </select>
                @error('patient_id') <div class="error">{{ $message }}</div> @enderror

                <label for="service_id">Service</label>
                <select id="service_id" name="service_id" required>
                    <option value="">Choose a service</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
                @error('service_id') <div class="error">{{ $message }}</div> @enderror

                <label for="priority">Priority category</label>
                <select id="priority" name="priority" required>
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}" @selected(old('priority', \App\Models\QueueTicket::PRIORITY_REGULAR) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority') <div class="error">{{ $message }}</div> @enderror

                <button type="submit">Add walk-in and print ticket</button>
            </form>
        @endif
    </section>

    <div class="queue-services">
        @forelse ($queueServices as $service)
            @php($serving = $service->queueTickets->firstWhere('status', \App\Models\QueueTicket::STATUS_SERVING))
            @php($waiting = $service->queueTickets->where('status', \App\Models\QueueTicket::STATUS_WAITING))
            <section class="stat">
                <div class="section-heading">
                    <div>
                        <h3>{{ $service->name }}</h3>
                        @unless ($service->is_active)
                            <div class="lbl">Inactive service · finish existing tickets</div>
                        @endunless
                        <div class="lbl">{{ $waiting->count() }} waiting</div>
                    </div>
                    @unless ($serving)
                        <form method="POST" action="{{ route('queue.call-next', $service) }}">
                            @csrf
                            <button type="submit">Call next</button>
                        </form>
                    @endunless
                </div>

                @if ($serving)
                    <div class="serving-card">
                        <div>
                            <span class="lbl">Now serving</span>
                            <strong>{{ $serving->ticket_code }} · {{ $serving->patient->name }} · {{ $serving->priorityLabel() }}</strong>
                        </div>
                        <div class="actions">
                            <form method="POST" action="{{ route('queue.served', $serving) }}">
                                @csrf
                                <button type="submit">Mark served</button>
                            </form>
                            <form method="POST" action="{{ route('queue.absent', $serving) }}">
                                @csrf
                                <button type="submit" class="button-secondary">Mark absent</button>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Ticket</th><th>Patient</th><th>Priority</th><th>Added</th></tr></thead>
                        <tbody>
                            @forelse ($waiting as $ticket)
                                <tr>
                                    <td>{{ $ticket->ticket_code }}</td>
                                    <td>{{ $ticket->patient->name }}</td>
                                    <td>{{ $ticket->priorityLabel() }}</td>
                                    <td>{{ $ticket->created_at->format('g:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">No patients waiting.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="stat">There are no active services today.</div>
        @endforelse
    </div>
@endsection
