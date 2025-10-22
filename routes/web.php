<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontDeskController;

Route::get('/', function () {
    return view('welcome');
});
// Dashboard route - redirects based on role
Route::middleware(['auth'])->get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Front Desk Routes - Protected by authentication and role
Route::middleware(['auth'])->prefix('frontdesk')->name('frontdesk.')->group(function () {

    // Dashboard
    Route::get('/', [FrontDeskController::class, 'index'])->name('index');

    // Online Booking Check-in
    Route::get('/checkin', [FrontDeskController::class, 'showCheckinForm'])->name('checkin');
    Route::post('/checkin/search', [FrontDeskController::class, 'searchBooking'])->name('checkin.search');
    Route::post('/checkin/process', [FrontDeskController::class, 'checkinOnlineBooking'])->name('checkin.process');

    // Walk-in Customer Registration
    Route::get('/walkin', [FrontDeskController::class, 'showWalkinForm'])->name('walkin');
    Route::post('/walkin/register', [FrontDeskController::class, 'registerWalkin'])->name('walkin.register');

    // Print Label
    Route::get('/print-label/{task}', [FrontDeskController::class, 'printLabel'])->name('print-label');
    Route::get('/label-data/{task}', [FrontDeskController::class, 'generateLabelData'])->name('label-data');

    // Search Tasks
    Route::get('/search', [FrontDeskController::class, 'searchTasks'])->name('search');
});
// Online Booking Routes - Protected by authentication
Route::middleware(['auth'])->prefix('bookings')->name('bookings.')->group(function () {
    // Booking type selection
    Route::get('/', [\App\Http\Controllers\BookingController::class, 'index'])->name('index');

    // Service booking
    Route::get('/service', [\App\Http\Controllers\BookingController::class, 'showServiceForm'])->name('service');
    Route::post('/service', [\App\Http\Controllers\BookingController::class, 'storeService'])->name('service.store');
    Route::post('/service/cost', [\App\Http\Controllers\BookingController::class, 'getServiceCost'])->name('service.cost');

    // Repair booking
    Route::get('/repair', [\App\Http\Controllers\BookingController::class, 'showRepairForm'])->name('repair');
    Route::post('/repair', [\App\Http\Controllers\BookingController::class, 'storeRepair'])->name('repair.store');

    // Payment
    Route::get('/payment/{booking}', [\App\Http\Controllers\BookingController::class, 'showPayment'])->name('payment');
    Route::post('/payment/{booking}', [\App\Http\Controllers\BookingController::class, 'processPayment'])->name('payment.process');

    // Success page
    Route::get('/success/{booking}', [\App\Http\Controllers\BookingController::class, 'success'])->name('success');

    // My bookings
    Route::get('/my-bookings', [\App\Http\Controllers\BookingController::class, 'myBookings'])->name('my-bookings');
});
// Task Tracking Routes - Public and authenticated
Route::prefix('track')->name('tracking.')->group(function () {
    // Public tracking search
    Route::get('/', [\App\Http\Controllers\TaskTrackingController::class, 'index'])->name('index');
    Route::post('/search', [\App\Http\Controllers\TaskTrackingController::class, 'search'])->name('search');

    // Track specific task
    Route::get('/{taskId}', [\App\Http\Controllers\TaskTrackingController::class, 'show'])->name('show');

    // AJAX endpoints for real-time updates
    Route::get('/{taskId}/timeline', [\App\Http\Controllers\TaskTrackingController::class, 'getTimeline'])->name('timeline');
    Route::get('/{taskId}/status', [\App\Http\Controllers\TaskTrackingController::class, 'getStatus'])->name('status');
});

// Technician Dashboard Routes - Protected by authentication
Route::middleware(['auth'])->prefix('technician')->name('technician.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\TechnicianController::class, 'index'])->name('index');

    // Task details
    Route::get('/task/{task}', [\App\Http\Controllers\TechnicianController::class, 'showTask'])->name('task.show');

    // Update task status
    Route::post('/task/{task}/status', [\App\Http\Controllers\TechnicianController::class, 'updateStatus'])->name('task.update-status');

    // Add progress update
    Route::post('/task/{task}/progress', [\App\Http\Controllers\TechnicianController::class, 'addProgress'])->name('task.add-progress');

    // Add material
    Route::post('/task/{task}/material', [\App\Http\Controllers\TechnicianController::class, 'addMaterial'])->name('task.add-material');

    // Complete task
    Route::post('/task/{task}/complete', [\App\Http\Controllers\TechnicianController::class, 'completeTask'])->name('task.complete');

    // Mark ready for collection
    Route::post('/task/{task}/ready', [\App\Http\Controllers\TechnicianController::class, 'markReady'])->name('task.mark-ready');
});

// Manager/Supervisor Dashboard Routes - Protected by authentication
Route::middleware(['auth'])->prefix('manager')->name('manager.')->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\ManagerController::class, 'index'])->name('index');

    // Tasks Management
    Route::get('/tasks', [\App\Http\Controllers\ManagerController::class, 'tasks'])->name('tasks');
    Route::get('/tasks/{task}/review', [\App\Http\Controllers\ManagerController::class, 'reviewComplexity'])->name('tasks.review');
    Route::post('/tasks/{task}/complexity', [\App\Http\Controllers\ManagerController::class, 'updateComplexity'])->name('tasks.update-complexity');
    Route::post('/tasks/{task}/reassign', [\App\Http\Controllers\ManagerController::class, 'reassignTask'])->name('tasks.reassign');

    // Technicians
    Route::get('/technicians', [\App\Http\Controllers\ManagerController::class, 'technicians'])->name('technicians');

    // Revenue
    Route::get('/revenue', [\App\Http\Controllers\ManagerController::class, 'revenue'])->name('revenue');

    // Customers
    Route::get('/customers', [\App\Http\Controllers\ManagerController::class, 'customers'])->name('customers');

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\ManagerController::class, 'analytics'])->name('analytics');
});
