<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\ReportController;

// Deployment Routes (token-authenticated in controller, CSRF exempted, throttled against brute force)
Route::get('/deploy', [DeployController::class, 'showForm'])->name('deploy.form');
Route::post('/deploy', [DeployController::class, 'deploy'])->middleware('throttle:5,1')->name('deploy');

// Webhook Routes (shared-secret authenticated in controller, CSRF exempted, throttled)
Route::post('/webhook/student-registration', [WebhookController::class, 'handleStudentRegistration'])
    ->middleware('throttle:30,1')
    ->name('webhook.student.registration');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Redirect root to students
    Route::get('/', function () {
        return redirect()->route('students.index');
    });

    // Student Management Routes
    // (registered before the resource so "students-trash" isn't captured by students/{student})
    Route::get('/students-trash', [StudentController::class, 'trash'])->name('students.trash');
    Route::post('/students/{student}/restore', [StudentController::class, 'restore'])
        ->withTrashed()
        ->name('students.restore');
    Route::resource('students', StudentController::class);

    // PDF Generation Routes
    Route::get('/students/{student}/pdf', [StudentController::class, 'generatePdf'])->name('students.pdf');
    Route::get('/students/{student}/print', [StudentController::class, 'print'])->name('students.print');
    Route::get('/students-bulk-pdf', [StudentController::class, 'generateBulkPdf'])->name('students.bulk-pdf');
    Route::get('/students-bulk-pdf-progress', [StudentController::class, 'getBulkPdfProgress'])->name('students.bulk-pdf-progress');
    Route::get('/students-bulk-pdf-download', [StudentController::class, 'downloadBulkPdf'])->name('students.bulk-pdf-download');

    // Reports Routes (Admin/Staff only - authorization in controller)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/all-students/export', [ReportController::class, 'allStudents'])->name('reports.all-students.export');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    // CSV Import Routes (Admin/Staff only)
    Route::middleware(['can:edit-students'])->group(function () {
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::get('/import/template', [ImportController::class, 'template'])->name('import.template');
        Route::post('/import', [ImportController::class, 'import'])->middleware('throttle:10,1')->name('import.store');
    });

    // User Management Routes (Admin only - authorization in controller)
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // Admin-only diagnostics (lists export archives and server paths)
    Route::get('/storage-diagnostics', [DeployController::class, 'storageDiagnostics'])
        ->middleware('can:admin')
        ->name('storage.diagnostics');
});
