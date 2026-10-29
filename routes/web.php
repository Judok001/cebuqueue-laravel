<?php

use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('/queue', 'profile.page', ['title' => 'Queue', 'text' => 'Manage today\'s queue and call the next patient.'])->name('queue');
    Route::view('/patients', 'profile.page', ['title' => 'Patients', 'text' => 'Register patients and manage their records.'])->name('patients');
    Route::view('/now-serving', 'profile.page', ['title' => 'Now serving board', 'text' => 'View the patient currently being served.'])->name('now-serving');

    Route::middleware('can:manage-users')->group(function () {
        Route::view('/appointments', 'profile.page', ['title' => 'Appointments', 'text' => 'Book and view appointments.'])->name('appointments');
        Route::view('/reports', 'profile.page', ['title' => 'Reports', 'text' => 'Daily patient count, wait times, and peak hours.'])->name('reports');
        Route::get('/services-staff', [StaffController::class, 'index'])->name('services-staff');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    });
});

// Backup logout for testing only. Delete this after your presentation.
Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
});

require __DIR__.'/auth.php';
