@extends('layouts.public')

@section('title', 'Get a queue ticket | CebuQueue')

@section('content')
    <section class="card">
        <h1 class="page-title">Reserve your place in the queue</h1>
        <p class="muted">Choose a service to receive a same-day queue ticket. Your ticket number and estimated wait will appear after submission.</p>

        @if ($errors->any())
            <div class="error" role="alert">
                <p>Please review the highlighted information.</p>
                @foreach ($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        @if ($services->isEmpty())
            <p class="muted">Online queue reservations are unavailable because no services are currently accepting patients.</p>
        @else
            <form method="POST" action="{{ route('reserve.store') }}">
                @csrf
                <label for="patient_name">Patient full name</label>
                <input id="patient_name" name="patient_name" type="text" value="{{ old('patient_name') }}" required maxlength="255" autocomplete="name">

                <label for="address">Address</label>
                <input id="address" name="address" type="text" value="{{ old('address') }}" required maxlength="1000" autocomplete="street-address">

                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel">

                <label for="age">Age</label>
                <input id="age" name="age" type="number" value="{{ old('age') }}" required min="0" max="120">

                <label for="service_id">Service</label>
                <select id="service_id" name="service_id" required>
                    <option value="">Choose a service</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                            {{ $service->name }} (about {{ $service->estimated_minutes }} min per patient)
                        </option>
                    @endforeach
                </select>

                <button type="submit">Get my ticket</button>
            </form>
        @endif
    </section>
@endsection
