@extends('layouts.cebu-app')

@section('content')
    <div class="top">
        <h2>Queue reports</h2>
        <p>Daily patient volume and average wait by service.</p>
    </div>

    <section class="stat">
        <form method="GET" action="{{ route('reports') }}" class="report-filter">
            <label for="date">Queue date</label>
            <input id="date" name="date" type="date" value="{{ $date }}" max="{{ now()->toDateString() }}">
            <label for="service_id">Service</label>
            <select id="service_id" name="service_id">
                <option value="">All services</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
            <button type="submit">Apply filters</button>
        </form>
    </section>

    <div class="stats">
        <div class="stat"><div class="num">{{ $totalTickets }}</div><div class="lbl">Tickets issued</div></div>
        <div class="stat"><div class="num">{{ $totalServed }}</div><div class="lbl">Patients served</div></div>
        <div class="stat"><div class="num">{{ $totalWaiting }}</div><div class="lbl">Still waiting</div></div>
        <div class="stat"><div class="num">{{ $averageWait === null ? '—' : (int) round($averageWait).' min' }}</div><div class="lbl">Average wait to be called</div></div>
    </div>

    <section class="stat">
        <h3>Service breakdown for {{ \Illuminate\Support\Carbon::parse($date)->format('F j, Y') }}</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Service</th><th>Tickets</th><th>Served</th><th>Waiting</th><th>Absent</th><th>Average wait</th></tr></thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['service']->name }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>{{ $row['served'] }}</td>
                            <td>{{ $row['waiting'] }}</td>
                            <td>{{ $row['absent'] }}</td>
                            <td>{{ $row['average_wait'] === null ? '—' : $row['average_wait'].' min' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No queue activity for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="stat">
        <h3>Priority category totals</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Priority category</th><th>Tickets</th></tr></thead>
                <tbody>
                    @foreach ($priorityCounts as $priority)
                        <tr>
                            <td>{{ $priority['label'] }}</td>
                            <td>{{ $priority['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
