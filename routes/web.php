<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\TaskTrackingController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\SmsTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public tracking routes (no authentication required)
Route::prefix('tracking')->name('tracking.')->group(function () {
    Route::get('/', [TaskTrackingController::class, 'index'])->name('index');
    Route::post('/search', [TaskTrackingController::class, 'search'])->name('search');
    Route::get('/{taskId}', [TaskTrackingController::class, 'show'])->name('show');
    Route::get('/{taskId}/timeline', [TaskTrackingController::class, 'getTimeline'])->name('timeline');
    Route::get('/{taskId}/status', [TaskTrackingController::class, 'getStatus'])->name('status');
});

// Authentication routes
require __DIR__.'/auth.php';

// Dashboard route - redirects based on role
Route::middleware(['auth', 'verified'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Client Booking Routes
Route::middleware(['auth'])->prefix('bookings')->name('bookings.')->group(function () {
    // Booking creation
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/create', [BookingController::class, 'create'])->name('create');
    Route::post('/store', [BookingController::class, 'store'])->name('store');
    Route::get('/success/{booking}', [BookingController::class, 'success'])->name('success');

    // My bookings
    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('my-bookings');
    Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
});

// Front Desk Routes
Route::middleware(['auth'])->prefix('frontdesk')->name('frontdesk.')->group(function () {
    // Dashboard
    Route::get('/', [FrontDeskController::class, 'index'])->name('index');

    // Check-in process
    Route::get('/checkin', [FrontDeskController::class, 'checkinForm'])->name('checkin');
    Route::post('/checkin', [FrontDeskController::class, 'processCheckin'])->name('checkin.process');

    // Walk-in registration
    Route::get('/walkin', [FrontDeskController::class, 'walkinForm'])->name('walkin');
    Route::post('/walkin', [FrontDeskController::class, 'createWalkin'])->name('walkin.create');

    // Device collection
    Route::get('/collection', [FrontDeskController::class, 'collectionForm'])->name('collection');
    Route::post('/collection/{task}', [FrontDeskController::class, 'processCollection'])->name('collection.process');

    // Invoice payment
    Route::post('/invoice/{invoice}/pay', [FrontDeskController::class, 'processPayment'])->name('invoice.pay');
});

// Technician Routes
Route::middleware(['auth'])->prefix('technician')->name('technician.')->group(function () {
    // Dashboard
    Route::get('/', [TechnicianController::class, 'index'])->name('index');

    // Task details
    Route::get('/task/{task}', [TechnicianController::class, 'showTask'])->name('task.show');

    // Update task status
    Route::post('/task/{task}/status', [TechnicianController::class, 'updateStatus'])->name('task.update-status');

    // Add progress update
    Route::post('/task/{task}/progress', [TechnicianController::class, 'addProgress'])->name('task.add-progress');

    // Add material
    Route::post('/task/{task}/material', [TechnicianController::class, 'addMaterial'])->name('task.add-material');

    // Complete task and generate invoice
    Route::post('/task/{task}/complete', [TechnicianController::class, 'complete'])->name('task.complete');

    // Mark ready for collection
    Route::post('/task/{task}/ready', [TechnicianController::class, 'markReady'])->name('task.mark-ready');
});

// Manager/Supervisor Dashboard Routes
Route::middleware(['auth'])->prefix('manager')->name('manager.')->group(function () {
    // Dashboard
    Route::get('/', [ManagerController::class, 'index'])->name('index');

    // Tasks Management
    Route::get('/tasks', [ManagerController::class, 'tasks'])->name('tasks');
    Route::get('/tasks/{task}/review', [ManagerController::class, 'reviewComplexity'])->name('tasks.review');
    Route::post('/tasks/{task}/complexity', [ManagerController::class, 'updateComplexity'])->name('tasks.update-complexity');
    Route::post('/tasks/{task}/reassign', [ManagerController::class, 'reassignTask'])->name('tasks.reassign');

    // Technicians
    Route::get('/technicians', [ManagerController::class, 'technicians'])->name('technicians');

    // Revenue
    Route::get('/revenue', [ManagerController::class, 'revenue'])->name('revenue');

    // Customers
    Route::get('/customers', [ManagerController::class, 'customers'])->name('customers');

    // Analytics
    Route::get('/analytics', [ManagerController::class, 'analytics'])->name('analytics');
});

// SMS Testing & Management Routes (Manager/Admin only)
Route::middleware(['auth'])->prefix('sms')->name('sms.')->group(function () {
    // SMS Test Panel
    Route::get('/test', [SmsTestController::class, 'index'])->name('test');

    // Send Test SMS
    Route::post('/send-test', [SmsTestController::class, 'sendTest'])->name('send-test');

    // Test Day 3 Reminder
    Route::get('/test-day3-reminder/{taskId}', [SmsTestController::class, 'testDay3Reminder'])->name('test-day3');

    // Test Day 4 Reminder
    Route::get('/test-day4-reminder/{taskId}', [SmsTestController::class, 'testDay4Reminder'])->name('test-day4');

    // Check API Balance
    Route::get('/balance', [SmsTestController::class, 'getBalance'])->name('balance');

    // View SMS Logs
    Route::get('/logs', [SmsTestController::class, 'logs'])->name('logs');
});
