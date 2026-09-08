<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PrintableController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;

// Deployment Routes: admin login AND the deploy token are both required.
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/deploy', [DeployController::class, 'showForm'])->name('deploy.form');
    Route::post('/deploy', [DeployController::class, 'deploy'])->middleware('throttle:5,1')->name('deploy');
});

// Webhook Routes (shared-secret authenticated in controller, CSRF exempted, throttled)
Route::post('/webhook/student-registration', [WebhookController::class, 'handleStudentRegistration'])
    ->middleware('throttle:30,1')
    ->name('webhook.student.registration');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Redirect root to students
    Route::get('/', function () {
        return redirect()->route('students.index');
    });

    // Own profile (every signed-in user)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:10,1')->name('profile.password');
    Route::post('/profile/sign-out-others', [ProfileController::class, 'signOutOtherDevices'])->middleware('throttle:10,1')->name('profile.sign-out-others');

    // Student Management Routes
    // (registered before the resource so "students-trash" isn't captured by students/{student})
    Route::get('/students-trash', [StudentController::class, 'trash'])->name('students.trash');
    Route::get('/students-promotion', [PromotionController::class, 'index'])->name('students.promotion');
    Route::post('/students-promotion', [PromotionController::class, 'store'])->name('students.promotion.run');
    Route::get('/students-photos', [PhotoController::class, 'index'])->name('students.photos');
    Route::post('/students-photos', [PhotoController::class, 'store'])->middleware('throttle:20,1')->name('students.photos.store');
    Route::post('/students/{student}/restore', [StudentController::class, 'restore'])
        ->withTrashed()
        ->name('students.restore');
    Route::resource('students', StudentController::class);

    // PDF Generation Routes
    Route::get('/students/{student}/pdf', [StudentController::class, 'generatePdf'])->middleware('can:view-sensitive')->name('students.pdf');
    Route::get('/students/{student}/print', [StudentController::class, 'print'])->middleware('can:view-sensitive')->name('students.print');

    // Uploaded photos and documents are served from private storage through this route
    Route::get('/documents/{path}', [DocumentController::class, 'show'])->where('path', '.*')->name('documents.show');
    Route::get('/students-print', [StudentController::class, 'printAll'])->name('students.print-all');
    Route::get('/students-bulk-pdf', [StudentController::class, 'generateBulkPdf'])->name('students.bulk-pdf');
    Route::get('/students-bulk-pdf-progress', [StudentController::class, 'getBulkPdfProgress'])->name('students.bulk-pdf-progress');
    Route::get('/students-bulk-pdf-download', [StudentController::class, 'downloadBulkPdf'])->name('students.bulk-pdf-download');

    // Reports Routes (Admin/Staff only - authorization in controller)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/all-students/export', [ReportController::class, 'allStudents'])->name('reports.all-students.export');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    // Printables for teachers / office (Admin/Staff only - authorization in controller)
    Route::get('/printables', [PrintableController::class, 'index'])->name('printables.index');
    Route::get('/printables/{printable}', [PrintableController::class, 'show'])->name('printables.show');

    // Audit trail (Admin only - authorization in controller)
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');

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
