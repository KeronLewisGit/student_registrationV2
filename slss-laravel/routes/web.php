<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ImportController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Redirect root to students
    Route::get('/', function () {
        return redirect()->route('students.index');
    });

    // Student Management Routes
    Route::resource('students', StudentController::class);

    // PDF Generation Routes
    Route::get('/students/{student}/pdf', [StudentController::class, 'generatePdf'])->name('students.pdf');
    Route::get('/students/{student}/print', [StudentController::class, 'print'])->name('students.print');
    Route::get('/students-bulk-pdf', [StudentController::class, 'generateBulkPdf'])->name('students.bulk-pdf');

    // CSV Import Routes (Admin/Staff only)
    Route::middleware(['can:edit-students'])->group(function () {
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import', [ImportController::class, 'import'])->name('import.store');
    });
});
