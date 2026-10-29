@extends('layouts.cebu-app')

@section('content')
    <div class="top">
        <h2>Services &amp; staff</h2>
        <p>Manage staff access for the health center.</p>
    </div>

    @if (session('status'))
        <div class="stat notice">{{ session('status') }}</div>
    @endif

    <section class="stat form-card">
        <h3>Create a staff account</h3>
        <form method="POST" action="{{ route('staff.store') }}">
            @csrf

            <label for="name">Staff name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <label for="password">Temporary password</label>
            <input id="password" name="password" type="password" required>
            @error('password') <div class="error">{{ $message }}</div> @enderror

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>

            <button type="submit">Create staff account</button>
        </form>
    </section>

@endsection
