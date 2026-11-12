@extends(auth()->check() ? 'layouts.cebu-app' : 'layouts.public')

@section('title', 'Now serving | CebuQueue')

@section('head')
    <meta http-equiv="refresh" content="15">
@endsection

@section('content')
    <div class="board-heading">
        <div>
            <span class="eyebrow">LIVE QUEUE STATUS</span>
            <h1 class="page-title">Now serving</h1>
            <p class="muted">Ticket numbers only. This board refreshes automatically every 15 seconds.</p>
        </div>
        <div class="board-actions">
            @auth
                <a class="button" href="{{ route('dashboard') }}">Back to dashboard</a>
            @else
                <a class="button" href="{{ route('login') }}">Staff login</a>
            @endauth
        </div>
    </div>

    <div class="board-grid">
        @forelse ($services as $service)
            @php($serving = $service->queueTickets->firstWhere('status', \App\Models\QueueTicket::STATUS_SERVING))
            <section class="stat board-service-card">
                <div class="board-service-heading">
                    <span class="board-service-indicator" aria-hidden="true"></span>
                    <h2>{{ $service->name }}</h2>
                </div>
                <p class="board-label">NOW SERVING</p>
                @if ($serving)
                    <div class="board-ticket-code">{{ $serving->ticket_code }}</div>
                @else
                    <p class="board-empty">No patient is being served right now.</p>
                @endif
                <div class="board-waiting">
                    <span>Waiting</span>
                    <strong>{{ $service->queueTickets->where('status', \App\Models\QueueTicket::STATUS_WAITING)->count() }}</strong>
                </div>
            </section>
        @empty
            <section class="stat board-empty-state">
                <h2>No services available</h2>
                <p class="muted">There are no active services or queue tickets right now.</p>
            </section>
        @endforelse
    </div>
@endsection
