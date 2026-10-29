@extends('layouts.cebu-app')

@section('content')
    <div class="top">
        <h2>Welcome, {{ auth()->user()->name }}</h2>
        <p>
            @if (auth()->user()->isAdmin())
                Admin dashboard — manage queue operations and staff access.
            @else
                Staff dashboard — manage the queue and patient records.
            @endif
        </p>
    </div>

    <div class="stats">
        <div class="stat"><div class="num">0</div><div class="lbl">Today's queue count</div></div>
        <div class="stat"><div class="num">0</div><div class="lbl">Patients served</div></div>
        <div class="stat"><div class="num">0 min</div><div class="lbl">Average wait time</div></div>
    </div>

    <h3>Quick actions</h3>
    <div class="actions">
        <a href="{{ route('queue') }}">Open queue</a>
        <a href="{{ route('patients') }}" class="alt">View patient records</a>
        <a href="{{ route('now-serving') }}" class="alt">Now serving board</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('reports') }}" class="alt">View reports</a>
            <a href="{{ route('services-staff') }}" class="alt">Manage staff</a>
        @endif
    </div>
@endsection