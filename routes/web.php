<?php

use App\Http\Controllers\CommissionNoteController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
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

    // Companies (manager only)
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    // Branches (nested under company)
    Route::post('/companies/{company}/branches', [CompanyController::class, 'storeBranch'])->name('companies.branches.store');
    Route::put('/companies/{company}/branches/{branch}', [CompanyController::class, 'updateBranch'])->name('companies.branches.update');
    Route::delete('/companies/{company}/branches/{branch}', [CompanyController::class, 'destroyBranch'])->name('companies.branches.destroy');

    // Employees (manager only)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

require __DIR__.'/auth.php';
