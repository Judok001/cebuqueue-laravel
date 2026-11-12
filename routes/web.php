<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicQueueController;
use App\Http\Controllers\QueueController;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicQueueController::class, 'create'])->name('home');
Route::get('/reserve', [PublicQueueController::class, 'create'])->name('reserve.create');
Route::post('/reserve', [PublicQueueController::class, 'store'])->middleware('throttle:10,1')->name('reserve.store');
Route::get('/ticket/{queueTicket:public_token}', [PublicQueueController::class, 'ticket'])->name('ticket.show');
Route::get('/now-serving', [PublicQueueController::class, 'nowServing'])->name('now-serving');

Route::get('/dashboard', function () {
    $todayTickets = QueueTicket::query()
        ->whereDate('queue_date', now()->toDateString())
        ->get(['status', 'created_at', 'called_at']);
    $waitTimes = $todayTickets
        ->filter(fn (QueueTicket $ticket): bool => $ticket->called_at !== null)
        ->map(fn (QueueTicket $ticket): int => (int) $ticket->created_at->diffInMinutes($ticket->called_at));
    $today = now()->toDateString();
    $queueServices = Service::query()
        ->where(function (Builder $query) use ($today): void {
            $query->where('is_active', true)
                ->orWhereHas('queueTickets', function (Builder $query) use ($today): void {
                    $query->whereDate('queue_date', $today)
                        ->whereIn('status', [QueueTicket::STATUS_WAITING, QueueTicket::STATUS_SERVING]);
                });
        })
        ->with(['queueTickets' => function (HasMany $query) use ($today): void {
            $query->whereDate('queue_date', $today)
                ->whereIn('status', [QueueTicket::STATUS_WAITING, QueueTicket::STATUS_SERVING])
                ->with('patient')
                ->inPriorityOrder();
        }])
        ->orderBy('name')
        ->get();

    return view('dashboard', [
        'queueCount' => $todayTickets->count(),
        'servedCount' => $todayTickets->where('status', QueueTicket::STATUS_SERVED)->count(),
        'averageWait' => $waitTimes->isEmpty() ? null : (int) round($waitTimes->avg()),
        'queueServices' => $queueServices,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/queue', [QueueController::class, 'index'])->name('queue');
    Route::post('/queue', [QueueController::class, 'store'])->name('queue.store');
    Route::post('/queue/services/{service}/next', [QueueController::class, 'callNext'])->name('queue.call-next');
    Route::post('/queue/tickets/{queueTicket}/served', [QueueController::class, 'markServed'])->name('queue.served');
    Route::post('/queue/tickets/{queueTicket}/absent', [QueueController::class, 'markAbsent'])->name('queue.absent');

    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');

    Route::middleware('can:manage-users')->group(function () {
        Route::view('/appointments', 'profile.page', ['title' => 'Appointments', 'text' => 'Book and view appointments.'])->name('appointments');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/services-staff', [ServiceController::class, 'index'])->name('services-staff');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
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
