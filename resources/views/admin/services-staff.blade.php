@extends('layouts.cebu-app')

@section('content')
    <div class="top">
        <h2>Services &amp; staff</h2>
        <p>Manage the clinic services and staff access.</p>
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

    <section class="stat">
        <h3>Staff accounts</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($staffs as $staff)
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->created_at->format('M j, Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('staff.destroy', $staff) }}" onsubmit="return confirm('Remove this staff account? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-danger" aria-label="Delete {{ $staff->name }}">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No staff accounts have been created.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="stat form-card">
        <h3>Add a service</h3>
        <form method="POST" action="{{ route('services.store') }}">
            @csrf
            <label for="service-name">Service name</label>
            <input id="service-name" name="name" type="text" value="{{ old('name') }}" required maxlength="255">
            @error('name') <div class="error">{{ $message }}</div> @enderror

            <label for="service-code">Ticket prefix</label>
            <input id="service-code" name="code" type="text" value="{{ old('code') }}" required maxlength="8" pattern="[A-Za-z0-9]{2,8}" placeholder="e.g. GEN">
            @error('code') <div class="error">{{ $message }}</div> @enderror

            <label for="estimated-minutes">Estimated minutes per patient</label>
            <input id="estimated-minutes" name="estimated_minutes" type="number" min="1" max="240" value="{{ old('estimated_minutes', 15) }}" required>
            @error('estimated_minutes') <div class="error">{{ $message }}</div> @enderror

            <button type="submit">Add service</button>
        </form>
    </section>

    <section class="stat">
        <h3>Manage services</h3>
        <div class="service-list">
            @forelse ($services as $service)
                <form method="POST" action="{{ route('services.update', $service) }}" class="service-row">
                    @csrf
                    @method('PATCH')
                    <div class="service-fields">
                        <div>
                            <label for="service-name-{{ $service->id }}">Service name</label>
                            <input id="service-name-{{ $service->id }}" name="name" type="text" value="{{ $service->name }}" required maxlength="255">
                        </div>
                        <div>
                            <label for="service-code-{{ $service->id }}">Ticket prefix</label>
                            <input id="service-code-{{ $service->id }}" name="code" type="text" value="{{ $service->code }}" required maxlength="8" pattern="[A-Za-z0-9]{2,8}">
                        </div>
                        <div>
                            <label for="service-minutes-{{ $service->id }}">Minutes per patient</label>
                            <input id="service-minutes-{{ $service->id }}" name="estimated_minutes" type="number" min="1" max="240" value="{{ $service->estimated_minutes }}" required>
                        </div>
                        <label class="checkbox-label" for="service-active-{{ $service->id }}">
                            <input type="hidden" name="is_active" value="0">
                            <input id="service-active-{{ $service->id }}" name="is_active" type="checkbox" value="1" @checked($service->is_active)>
                            Accepting queue tickets
                        </label>
                    </div>
                    <div class="service-actions">
                        <span class="muted">{{ $service->queue_tickets_count }} tickets issued</span>
                        <button type="submit">Save service</button>
                    </div>
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                    @error('code') <div class="error">{{ $message }}</div> @enderror
                    @error('estimated_minutes') <div class="error">{{ $message }}</div> @enderror
                    @error('is_active') <div class="error">{{ $message }}</div> @enderror
                </form>
            @empty
                <p>No services have been configured yet.</p>
            @endforelse
        </div>
    </section>
@endsection