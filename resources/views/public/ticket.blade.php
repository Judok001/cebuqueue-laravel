@extends('layouts.public')

@section('title', 'Your queue ticket | CebuQueue')

@section('head')
    <meta http-equiv="refresh" content="30">
@endsection

@section('content')
    <section class="card">
        <h1 class="page-title">Your queue ticket</h1>
        <p class="muted">{{ $ticket->service->name }}</p>
        <div class="ticket-code">{{ $ticket->ticket_code }}</div>
        <p>Queue type: <strong>{{ $ticket->priorityLabel() }}</strong></p>
        <p>Status: <strong>{{ ucfirst($ticket->status) }}</strong></p>

        @if ($ticket->status === \App\Models\QueueTicket::STATUS_WAITING)
            <p>Your queue position: <strong>{{ $ticket->queuePosition() }}</strong></p>
            <p>Estimated wait: <strong>about {{ $ticket->estimatedWaitMinutes() }} minutes</strong></p>
            <p class="muted">Wait estimates are approximate and may change as the queue moves.</p>
        @elseif ($ticket->status === \App\Models\QueueTicket::STATUS_SERVING)
            <p>Please proceed to the service counter.</p>
        @elseif ($ticket->status === \App\Models\QueueTicket::STATUS_SERVED)
            <p>Your visit is marked as served.</p>
        @else
            <p>This ticket is marked absent. Please speak with clinic staff if you need help.</p>
        @endif

        <div class="ticket-actions">
            <button type="button" onclick="window.print()">Print ticket</button>
            <a class="button" href="{{ route('now-serving') }}">View Now Serving Board</a>
        </div>
    </section>
@endsection
