<?php

use App\Http\Controllers\Auth\AlumniPasswordResetController;
use App\Http\Controllers\Auth\AlumniRegistrationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIPALA Web Routes
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC / LANDING & GUEST ROUTES
// =============================================

Route::get('/', [LandingController::class, 'index'])->middleware('prevent.back')->name('landing');

// Endpoint ringan untuk sinkronisasi ulang token CSRF antar-tab / setelah kembali dari BFCache
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->name('csrf.token');

// Submit login & cek status alumni ditempatkan di luar middleware 'guest' agar tetap berfungsi
// meskipun di tab browser lain masih ada sesi akun lain yang belum logout.
Route::middleware('prevent.back')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/alumni/status', [AlumniRegistrationController::class, 'showStatusForm'])->name('alumni.status');
    Route::post('/alumni/status', [AlumniRegistrationController::class, 'checkStatus'])->name('alumni.status.check');
});

Route::middleware(['guest', 'prevent.back'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

    // Pendaftaran Mandiri Alumni
    Route::get('/alumni/register', [AlumniRegistrationController::class, 'showRegistrationForm'])->name('alumni.register');
    Route::post('/alumni/register', [AlumniRegistrationController::class, 'register'])->name('alumni.register.submit');

    // Reset Kata Sandi Khusus Akun Alumni (auth_source = LOCAL)
    Route::get('/alumni/forgot-password', [AlumniPasswordResetController::class, 'showLinkRequestForm'])->name('alumni.password.request');
    Route::post('/alumni/forgot-password', [AlumniPasswordResetController::class, 'sendResetLinkEmail'])->name('alumni.password.email');
    Route::get('/alumni/reset-password/{token}', [AlumniPasswordResetController::class, 'showResetForm'])->name('alumni.password.reset');
    Route::post('/alumni/reset-password', [AlumniPasswordResetController::class, 'reset'])->name('alumni.password.update');
});

// Logout (metode POST murni dengan proteksi CSRF penuh)
Route::post('/logout', [LoginController::class, 'logout'])->middleware(['auth', 'prevent.back'])->name('logout');

// =============================================
// AUTHENTICATED ROUTES
// =============================================

Route::middleware(['auth', 'active', 'prevent.back', 'log.activity'])->group(function () {

    // Dashboard (auto-routes by role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Akses terproteksi untuk lampiran tiket (memvalidasi pemilik/staf/admin)
    Route::get('/tickets/{ticket}/attachments/{attachment}', [\App\Http\Controllers\User\TicketController::class, 'showAttachment'])
        ->name('tickets.attachments.show');

    // =============================================
    // USER ROUTES (Mahasiswa, Alumni, Dosen/Pegawai)
    // =============================================
    Route::middleware('role:MAHASISWA,ALUMNI,DOSEN_PEGAWAI')->prefix('user')->name('user.')->group(function () {
        Route::resource('tickets', \App\Http\Controllers\User\TicketController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('tickets/{ticket}/messages', [\App\Http\Controllers\User\TicketController::class, 'storeMessage'])->name('tickets.messages.store');
        Route::get('ratings', [\App\Http\Controllers\User\RatingController::class, 'index'])->name('ratings.index');
        Route::post('tickets/{ticket}/rating', [\App\Http\Controllers\User\TicketRatingController::class, 'store'])->name('tickets.rating.store');

        // Profil & Ganti Kata Sandi (Khusus Alumni — Mahasiswa & Dosen dikelola via SSO Portal)
        Route::middleware('role:ALUMNI')->group(function () {
            Route::get('alumni-profile', [\App\Http\Controllers\User\AlumniProfileController::class, 'edit'])->name('alumni-profile.edit');
            Route::put('alumni-profile', [\App\Http\Controllers\User\AlumniProfileController::class, 'updateProfile'])->name('alumni-profile.update');
            Route::put('alumni-profile/password', [\App\Http\Controllers\User\AlumniProfileController::class, 'updatePassword'])->name('alumni-profile.password');
        });
    });

    // =============================================
    // STAFF ROUTES
    // =============================================
    Route::middleware('role:STAFF')->prefix('staff')->name('staff.')->group(function () {
        Route::get('tickets', [\App\Http\Controllers\Staff\TicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{ticket}', [\App\Http\Controllers\Staff\TicketController::class, 'show'])->name('tickets.show');
        Route::patch('tickets/{ticket}/status', [\App\Http\Controllers\Staff\TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::post('tickets/{ticket}/messages', [\App\Http\Controllers\Staff\TicketController::class, 'storeMessage'])->name('tickets.messages.store');
    });

    // =============================================
    // ADMIN ROUTES
    // =============================================
    Route::middleware('role:ADMIN,SUPER_ADMIN')->prefix('admin')->name('admin.')->group(function () {
        // Ticket Management & Assignment
        Route::get('tickets', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('tickets.show');
        Route::patch('tickets/{ticket}/verify', [\App\Http\Controllers\Admin\TicketController::class, 'verify'])->name('tickets.verify');
        Route::patch('tickets/{ticket}/reject', [\App\Http\Controllers\Admin\TicketController::class, 'reject'])->name('tickets.reject');
        Route::post('tickets/{ticket}/assign', [\App\Http\Controllers\Admin\TicketController::class, 'assign'])->name('tickets.assign');
        Route::post('tickets/{ticket}/messages', [\App\Http\Controllers\Admin\TicketController::class, 'storeMessage'])->name('tickets.messages.store');
        Route::post('tickets/{ticket}/remind-rating', [\App\Http\Controllers\Admin\TicketController::class, 'remindRating'])->name('tickets.remind-rating');

        // Master Data Management
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->except(['show']);
        
        // Nested resources for Services
        Route::post('services/{service}/requirements', [\App\Http\Controllers\Admin\ServiceRequirementController::class, 'store'])->name('services.requirements.store');
        Route::put('services/{service}/requirements/{requirement}', [\App\Http\Controllers\Admin\ServiceRequirementController::class, 'update'])->name('services.requirements.update');
        Route::delete('services/{service}/requirements/{requirement}', [\App\Http\Controllers\Admin\ServiceRequirementController::class, 'destroy'])->name('services.requirements.destroy');

        Route::post('services/{service}/fields', [\App\Http\Controllers\Admin\ServiceFieldController::class, 'store'])->name('services.fields.store');
        Route::put('services/{service}/fields/{field}', [\App\Http\Controllers\Admin\ServiceFieldController::class, 'update'])->name('services.fields.update');
        Route::delete('services/{service}/fields/{field}', [\App\Http\Controllers\Admin\ServiceFieldController::class, 'destroy'])->name('services.fields.destroy');

        // Staff & Specialties Management
        Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class)->except(['show']);

        // Alumni Verification & Account Management
        Route::get('alumni-verifications', [\App\Http\Controllers\Admin\AlumniVerificationController::class, 'index'])->name('alumni.index');
        Route::get('alumni-verifications/{registration}/proof', [\App\Http\Controllers\Admin\AlumniVerificationController::class, 'showProof'])->name('alumni.proof');
        Route::post('alumni-verifications/{registration}/approve', [\App\Http\Controllers\Admin\AlumniVerificationController::class, 'approve'])->name('alumni.approve');
        Route::post('alumni-verifications/{registration}/reject', [\App\Http\Controllers\Admin\AlumniVerificationController::class, 'reject'])->name('alumni.reject');
        Route::patch('alumni-accounts/{alumni}/toggle-status', [\App\Http\Controllers\Admin\AlumniVerificationController::class, 'toggleStatus'])->name('alumni.accounts.toggle');
        Route::post('alumni-accounts/{alumni}/reset-password', [\App\Http\Controllers\Admin\AlumniVerificationController::class, 'resetPassword'])->name('alumni.accounts.reset-password');

        // Activity Logs (Audit Trail)
        Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Customer Satisfaction & Rating
        Route::get('ratings', [\App\Http\Controllers\Admin\RatingController::class, 'index'])->name('ratings.index');
        Route::post('ratings/remind-all', [\App\Http\Controllers\Admin\RatingController::class, 'sendAllReminders'])->name('ratings.remind-all');

        // =============================================
        // SUPER ADMIN ONLY (Pustikom)
        // =============================================
        Route::middleware('role:SUPER_ADMIN')->group(function () {
            Route::get('maintenance', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('maintenance.index');
            Route::post('maintenance/toggle', [\App\Http\Controllers\Admin\MaintenanceController::class, 'toggleMaintenance'])->name('maintenance.toggle');
            Route::post('maintenance/cache', [\App\Http\Controllers\Admin\MaintenanceController::class, 'clearCache'])->name('maintenance.cache');
            Route::resource('admins', \App\Http\Controllers\Admin\AdminUserController::class)->except(['create', 'show', 'edit']);
        });
    });
});
