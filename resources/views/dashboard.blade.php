@extends('layouts.cebu-app')

@section('content')
    <div class="top dashboard-hero">
        <div>
            <span class="eyebrow">CLINIC OPERATIONS</span>
            <h2>Welcome, {{ auth()->user()->name }}</h2>
            <p>
            @if (auth()->user()->isAdmin())
                Admin dashboard · Your daily overview for queue operations and clinic activity.
            @else
                Staff dashboard · Keep today’s patient flow moving smoothly.
            @endif
            </p>
        </div>
        <a class="hero-action" href="{{ route('queue') }}">Open queue <span aria-hidden="true">→</span></a>
    </div>

    <div class="stats">
        <div class="stat metric-card metric-queue">
            <span class="metric-kicker">TODAY</span>
            <div class="num">{{ $queueCount }}</div>
            <div class="lbl">Patients in queue</div>
            <span class="metric-symbol" aria-hidden="true">Q</span>
        </div>
        <div class="stat metric-card metric-served">
            <span class="metric-kicker">COMPLETED</span>
            <div class="num">{{ $servedCount }}</div>
            <div class="lbl">Patients served</div>
            <span class="metric-symbol" aria-hidden="true">✓</span>
        </div>
        <div class="stat metric-card metric-wait">
            <span class="metric-kicker">TODAY’S AVERAGE</span>
            <div class="num">
                @if ($averageWait === null)
                    —
                @else
                    {{ $averageWait }}<small> min</small>
                @endif
            </div>
            <div class="lbl">Wait before being called</div>
            <span class="metric-symbol" aria-hidden="true">◷</span>
        </div>
    </div>

    <div class="dashboard-section-heading">
        <div>
            <span class="eyebrow">SHORTCUTS</span>
            <h3>Quick actions</h3>
        </div>
    </div>
    <div class="actions dashboard-actions">
        <a href="{{ route('queue') }}"><span class="action-icon" aria-hidden="true">↗</span>Manage queue</a>
        <a href="{{ route('patients') }}" class="alt"><span class="action-icon" aria-hidden="true">+</span>Register patient</a>
        <a href="{{ route('now-serving') }}" class="alt"><span class="action-icon" aria-hidden="true">◎</span>Now serving board</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('reports') }}" class="alt"><span class="action-icon" aria-hidden="true">▤</span>View reports</a>
            <a href="{{ route('services-staff') }}" class="alt"><span class="action-icon" aria-hidden="true">⚙</span>Services &amp; staff</a>
        @endif
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

    <div class="section-heading dashboard-queue-heading">
        <div>
            <span class="eyebrow">LIVE OVERVIEW</span>
            <h3>Today's queue</h3>
            <p class="muted">Call the next patient and manage the patient currently being served.</p>
        </div>
        <a class="text-link" href="{{ route('queue') }}">Full queue management <span aria-hidden="true">→</span></a>
    </div>

    <div class="queue-services">
        @forelse ($queueServices as $service)
            @php($serving = $service->queueTickets->firstWhere('status', \App\Models\QueueTicket::STATUS_SERVING))
            @php($waiting = $service->queueTickets->where('status', \App\Models\QueueTicket::STATUS_WAITING))
            <section class="stat queue-service-card">
                <div class="section-heading">
                    <div>
                        <h3>{{ $service->name }}</h3>
                        <span class="queue-count">{{ $waiting->count() }} waiting</span>
                    </div>
                    @unless ($serving)
                        <form method="POST" action="{{ route('queue.call-next', $service) }}">
                            @csrf
                            <button class="call-next-button" type="submit" @disabled($waiting->isEmpty())>Call next patient <span aria-hidden="true">→</span></button>
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
            <div class="stat empty-queue-card">
                <p>There are no active services yet, so patients cannot be queued or called.</p>
                <p class="muted">Activate a service before adding or calling patients.</p>
            </div>
        @endforelse
    </div>
@endsection