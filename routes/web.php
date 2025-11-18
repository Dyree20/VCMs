<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClampingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EnforcerController;
use App\Http\Controllers\PayMongoWebhookController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('auth.login');
});

// API Routes
Route::get('/api/notifications', [NotificationController::class, 'getNotifications'])->middleware('auth');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::get('/register', function () {
    return view('auth.register');
})->name('register.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/register', [AuthController::class, 'register'])->name('register');

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('forgot-password.form');
Route::post('/forgot-password', [AuthController::class, 'forgotPasswordEmail'])->name('forgot-password.email');
Route::get('/reset-password/{token}/{email}', [AuthController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Front Desk Dashboard
    Route::get('/front-desk/dashboard', [FrontDeskController::class, 'index'])->name('front-desk.dashboard');
    Route::get('/front-desk/violations', [FrontDeskController::class, 'violations'])->name('front-desk.violations');
    Route::get('/front-desk/payments', [FrontDeskController::class, 'payments'])->name('front-desk.payments');
    Route::get('/front-desk/payment/create', [FrontDeskController::class, 'createPayment'])->name('front-desk.payment.create');
    Route::post('/front-desk/payment/store', [FrontDeskController::class, 'storePayment'])->name('front-desk.payment.store');
    Route::get('/front-desk/search', [FrontDeskController::class, 'searchInquiries'])->name('front-desk.search');
    Route::get('/front-desk/inquiry/{id}', [FrontDeskController::class, 'showInquiry'])->name('front-desk.inquiry.show');
    Route::post('/front-desk/inquiry/{id}/mark-paid', [FrontDeskController::class, 'markAsPaid'])->name('front-desk.inquiry.mark-paid');
    Route::get('/front-desk/statistics', [FrontDeskController::class, 'getStatistics'])->name('front-desk.statistics');
    Route::get('/front-desk/archives', [FrontDeskController::class, 'frontDeskArchives'])->name('front-desk.archives');

    // Admin Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('admin.profile');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('admin.profile.update');

    // Profile Routes for all authenticated users (generic profile route)
    Route::get('/my-profile', function() {
        $user = auth()->user();
        if ($user->role->name === 'Admin') {
            return redirect()->route('admin.profile');
        } elseif ($user->role->name === 'Front Desk') {
            return redirect()->route('front-desk.profile');
        } elseif ($user->role->name === 'Enforcer') {
            return redirect()->route('enforcer.profile');
        }
        return redirect()->route('admin.profile');
    })->name('profile');

    // Front Desk Profile Routes
    Route::get('/front-desk/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('front-desk.profile');
    Route::get('/front-desk/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('front-desk.profile.edit');
    Route::put('/front-desk/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('front-desk.profile.update');

    // Teams Management (Admin only)
    Route::resource('teams', \App\Http\Controllers\TeamController::class);
    Route::post('/teams/{team}/add-enforcer', [\App\Http\Controllers\TeamController::class, 'addEnforcer'])->name('teams.add-enforcer');
    Route::post('/teams/{team}/remove-enforcer', [\App\Http\Controllers\TeamController::class, 'removeEnforcer'])->name('teams.remove-enforcer');

    // Device Manager - Security
    Route::prefix('devices')->name('devices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DeviceManagerController::class, 'index'])->name('index');
        Route::post('/{device}/logout', [\App\Http\Controllers\DeviceManagerController::class, 'logoutDevice'])->name('logout');
        Route::post('/logout-all-others', [\App\Http\Controllers\DeviceManagerController::class, 'logoutAllOtherDevices'])->name('logout-all-others');
        Route::post('/logout-all', [\App\Http\Controllers\DeviceManagerController::class, 'logoutAllDevices'])->name('logout-all');
    });

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/fetch', [UserController::class, 'fetchUsers'])->name('users.fetch');
    Route::post('/users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{id}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::post('/users/{id}/assign-area', [UserController::class, 'updateAssignedArea'])->name('users.assign-area');
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

    // Clamping
    Route::get('/clampings', [ClampingController::class, 'index'])->name('clampings');
    Route::post('/clampings', [ClampingController::class, 'store']);
    Route::get('/clampings/receipt/{id}', [ClampingController::class, 'print'])->name('clampings.print');
    Route::get('/clampings/{id}', [ClampingController::class, 'editShow'])->name('clampings.show');
    Route::get('/clampings/{id}/edit', [ClampingController::class, 'editForm'])->name('clampings.edit');
    Route::put('/clampings/{id}', [ClampingController::class, 'update'])->name('clampings.update');
    Route::post('/clampings/{id}/release', [ClampingController::class, 'release'])->name('clampings.release');
    Route::post('/clampings/{id}/cancel', [ClampingController::class, 'cancel'])->name('clampings.cancel');
    Route::delete('/clampings/{id}', [ClampingController::class, 'destroy'])->name('clampings.destroy');
    // Clamping actions - protect with EnsureEnforcer middleware (server-side)
    Route::middleware(\App\Http\Middleware\EnsureEnforcer::class)->group(function () {
        Route::post('/clampings/{id}/accept', [ClampingController::class, 'accept'])->name('clampings.accept');
        Route::post('/clampings/{id}/reject', [ClampingController::class, 'reject'])->name('clampings.reject');
        Route::post('/clampings/{id}/approve', [ClampingController::class, 'approve'])->name('clampings.approve');
    });

    // Enforcer add clamping
    Route::get('/add-clamping', [ClampingController::class, 'create'])->name('add.clamping');
    Route::get('/enforcer/dashboard', [EnforcerController::class, 'index'])->name('enforcer.dashboard');
    Route::get('/enforcer/summary', [EnforcerController::class, 'getSummary']);
    Route::get('/enforcer/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('enforcer.profile');
    
    // Profile Management Routes - For Enforcer specific pages (edit photo, transactions, etc)
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/update-photo', [EnforcerController::class, 'updatePhoto'])->name('profile.update-photo');
    Route::get('/transactions/history', [EnforcerController::class, 'transactionsHistory'])->name('transactions.history');
    Route::get('/notification/settings', [EnforcerController::class, 'notificationSettings'])->name('notification.settings');
    Route::put('/notification/update', [EnforcerController::class, 'updateNotificationSettings'])->name('notification.update');
    Route::get('/contact-us', [EnforcerController::class, 'contactUs'])->name('contact.us');
    Route::post('/contact/store', [EnforcerController::class, 'storeContact'])->name('contact.store');
    Route::get('/help-faqs', [EnforcerController::class, 'helpFaqs'])->name('help.faqs');
    
    // Enforcer notifications (also exposed at root path to match navigation.js)
    Route::get('/enforcer/notifications', [EnforcerController::class, 'notifications'])->name('enforcer.notifications');
    Route::get('/notifications', [EnforcerController::class, 'notifications']);
    
    // Redirect old records route to archives
    Route::get('/enforcer/records', function() {
        return redirect()->route('enforcer.archives');
    });
    Route::get('/records', function() {
        return redirect()->route('enforcer.archives');
    });
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::post('/payments', [PaymentController::class, 'store']);

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs');
    Route::get('/activity-logs/filter', [ActivityLogController::class, 'filter'])->name('activity-logs.filter');

    // Archives
    Route::get('/archives', [\App\Http\Controllers\ArchiveController::class, 'adminIndex'])->name('admin.archives');
    Route::get('/archives/filter', [\App\Http\Controllers\ArchiveController::class, 'filter'])->name('admin.archives.filter');
    Route::get('/enforcer/archives', [\App\Http\Controllers\ArchiveController::class, 'enforcerIndex'])->name('enforcer.archives');

});

Route::get('/verify/{id}', [ClampingController::class, 'verify'])->name('clampings.verify');

Route::get('/pay/{ticket_no}', [PaymentController::class, 'createCheckout']);
Route::post('/webhook/paymongo', [PaymentController::class, 'webhook']);
Route::get('/payment/success/{id}', [PaymentController::class, 'success']);
Route::get('/payment/cancel', [PaymentController::class, 'cancel']);

    Route::post('/webhook/paymongo', [PayMongoWebhookController::class, 'handle']);

// Route::get('/enforcers', function () {
//         return view('dashboards.overview'); 
//     })->name('overview');


