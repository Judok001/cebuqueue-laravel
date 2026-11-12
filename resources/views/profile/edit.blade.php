@extends('layouts.cebu-app')

@section('content')
    <div class="top profile-heading">
        <span class="eyebrow">ACCOUNT SETTINGS</span>
        <h2>Your profile</h2>
        <p>Manage your personal information and account security.</p>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="stat notice" role="status">Profile information saved.</div>
    @elseif (session('status') === 'password-updated')
        <div class="stat notice" role="status">Password updated successfully.</div>
    @endif

    @if ($errors->any())
        <div class="stat error profile-errors" role="alert">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <div class="profile-sections">
        <section class="stat profile-card">
            <div class="profile-card-heading">
                <span class="profile-card-icon" aria-hidden="true">01</span>
                <div>
                    <h3>Profile information</h3>
                    <p class="muted">Update the name and email address associated with your account.</p>
                </div>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <form id="resend-verification" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                </form>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="username">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="profile-verification">
                        <p>Your email address is not verified.</p>
                        <button type="submit" form="resend-verification" class="profile-secondary-button">Resend verification email</button>
                    </div>
                @endif

                <button type="submit">Save profile</button>
            </form>
        </section>

        <section class="stat profile-card">
            <div class="profile-card-heading">
                <span class="profile-card-icon" aria-hidden="true">02</span>
                <div>
                    <h3>Change password</h3>
                    <p class="muted">Use your current password to set a new one.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="profile-form">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                    @error('current_password', 'updatePassword') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password">
                    @error('password', 'updatePassword') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword') <div class="error">{{ $message }}</div> @enderror
                </div>

                <button type="submit">Update password</button>
            </form>
        </section>

        <section class="stat profile-card profile-danger-card">
            <div class="profile-card-heading">
                <span class="profile-card-icon" aria-hidden="true">03</span>
                <div>
                    <h3>Delete account</h3>
                    <p class="muted">Permanently remove your account. This action cannot be undone.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}" class="profile-form">
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password">Confirm your password to delete this account</label>
                    <input id="delete_password" name="password" type="password" required autocomplete="current-password">
                    @error('password', 'userDeletion') <div class="error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="profile-delete-button" onclick="return confirm('Are you sure you want to permanently delete your account?')">
                    Delete account
                </button>
            </form>
        </section>
    </div>
@endsection
