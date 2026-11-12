@extends('layouts.cebu-app')

@section('content')
    <div class="top">
        <h2>Patient registration</h2>
        <p>Register a patient and add them to today's queue in one step.</p>
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
        <h3>Register a patient</h3>
        <form method="POST" action="{{ route('patients.store') }}">
            @csrf
            <label for="name">Patient full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255">
            @error('name') <div class="error">{{ $message }}</div> @enderror

            <label for="address">Address</label>
            <input id="address" name="address" type="text" value="{{ old('address') }}" required maxlength="1000" autocomplete="street-address">
            @error('address') <div class="error">{{ $message }}</div> @enderror

            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel">
            @error('phone') <div class="error">{{ $message }}</div> @enderror

            <label for="age">Age</label>
            <input id="age" name="age" type="number" value="{{ old('age') }}" required min="0" max="120">
            @error('age') <div class="error">{{ $message }}</div> @enderror

            <label for="service_id">Service</label>
            <select id="service_id" name="service_id" required @disabled($services->isEmpty())>
                <option value="">Choose a service</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
            @error('service_id') <div class="error">{{ $message }}</div> @enderror
            @if ($services->isEmpty())
                <p class="muted">No active services. Ask an admin to activate a service before registering a queue visit.</p>
            @endif

            <label for="priority">Priority category</label>
            <select id="priority" name="priority" required>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', \App\Models\QueueTicket::PRIORITY_REGULAR) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('priority') <div class="error">{{ $message }}</div> @enderror

            <button type="submit" @disabled($services->isEmpty())>Register and get queue ticket</button>
        </form>
    </section>

    <section class="stat">
        <div class="section-heading">
            <h3>Registered patients</h3>
            <form method="GET" action="{{ route('patients') }}" class="search-form">
                <label class="sr-only" for="search">Search patients</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Name, phone, or address">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Phone</th><th>Age</th><th>Registered</th><th>Queue</th></tr></thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td>{{ $patient->name }}</td>
                            <td>{{ $patient->phone ?: '—' }}</td>
                            <td>{{ $patient->age ?? '—' }}</td>
                            <td>{{ $patient->created_at->format('M j, Y') }}</td>
                            <td>
                                @if ($patient->queueTickets->isNotEmpty())
                                    @foreach ($patient->queueTickets as $ticket)
                                        <div>{{ $ticket->ticket_code }} · {{ ucfirst($ticket->status) }} · {{ $ticket->priorityLabel() }}</div>
                                    @endforeach
                                    <a href="{{ route('dashboard') }}">View today's queue</a>
                                @elseif ($services->isNotEmpty())
                                    <form method="POST" action="{{ route('queue.store') }}" class="patient-queue-form">
                                        @csrf
                                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                                        <label class="sr-only" for="service-{{ $patient->id }}">Service for {{ $patient->name }}</label>
                                        <select id="service-{{ $patient->id }}" name="service_id" required>
                                            <option value="">Choose service</option>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                        <label class="sr-only" for="priority-{{ $patient->id }}">Priority for {{ $patient->name }}</label>
                                        <select id="priority-{{ $patient->id }}" name="priority" required>
                                            @foreach ($priorities as $value => $label)
                                                <option value="{{ $value }}" @selected(old('priority', \App\Models\QueueTicket::PRIORITY_REGULAR) === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit">Add to queue</button>
                                    </form>
                                @else
                                    <span class="muted">No active services.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $patients->links() }}
    </section>
@endsection
