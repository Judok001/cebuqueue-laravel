@extends('layouts.cebu')

@section('content')
    <h1>Welcome back</h1>
    <p class="intro">Log in to continue to your CebuQueue account.</p>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label class="remember" for="remember">
            <input type="checkbox" id="remember" name="remember">
            Remember me
        </label>

        <a class="forgot-link" href="{{ route('password.request') }}">Forgot your password?</a>
        <button type="submit">Log in</button>
    </form>

    <p class="link">Need an account? Ask your administrator to create a staff login.</p>
@endsection
