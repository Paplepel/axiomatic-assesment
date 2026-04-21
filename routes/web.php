<?php

use App\Http\Controllers\CommissionNoteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('commission-notes.index');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/commission-notes', [CommissionNoteController::class, 'index'])->name('commission-notes.index');
    Route::post('/commission-notes', [CommissionNoteController::class, 'store'])->name('commission-notes.store');
    Route::put('/commission-notes/{commissionNote}', [CommissionNoteController::class, 'update'])->name('commission-notes.update');
    Route::delete('/commission-notes/{commissionNote}', [CommissionNoteController::class, 'destroy'])->name('commission-notes.destroy');
});

require __DIR__.'/auth.php';
