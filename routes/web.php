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

// Front Desk Routes - Protected by authentication and role
Route::middleware(['auth'])->prefix('frontdesk')->name('frontdesk.')->group(function () {
    // Dashboard
    Route::get('/', [FrontDeskController::class, 'index'])->name('index');

    // Check-in process
    Route::get('/checkin', [FrontDeskController::class, 'showCheckinForm'])->name('checkin');
    Route::post('/checkin/search', [FrontDeskController::class, 'searchBooking'])->name('checkin.search');
    Route::post('/checkin', [FrontDeskController::class, 'checkinOnlineBooking'])->name('checkin.process');

    // Walk-in registration
    Route::get('/walkin', [FrontDeskController::class, 'showWalkinForm'])->name('walkin');
    Route::post('/walkin', [FrontDeskController::class, 'registerWalkin'])->name('walkin.register');


  // Collection pages
  Route::get('/collection', [FrontDeskController::class, 'collectionIndex'])->name('collection');
  Route::post('/collection/search', [FrontDeskController::class, 'searchDevice'])->name('collection.search');
  Route::post('/collection/{task}', [FrontDeskController::class, 'processCollection'])->name('collection.process');
  Route::get('/collection/{task}/receipt', [FrontDeskController::class, 'collectionReceipt'])->name('collection-receipt');

  // Payment processing at front desk
  Route::post('/payment/{invoice}', [FrontDeskController::class, 'processPayment'])->name('payment.process');

    // Invoice payment
    Route::post('/invoice/{invoice}/pay', [FrontDeskController::class, 'processPayment'])->name('invoice.pay');

    // Label printing
    Route::get('/print-label/{task}', [FrontDeskController::class, 'printLabel'])->name('print-label');
    Route::get('/label-data/{task}', [FrontDeskController::class, 'getLabelData'])->name('label-data');

    // Search tasks
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
 // Technicians CRUD
 Route::get('/technicians', [ManagerController::class, 'technicians'])->name('technicians');
 Route::get('/technicians/create', [ManagerController::class, 'createTechnician'])->name('technicians.create');
 Route::post('/technicians', [ManagerController::class, 'storeTechnician'])->name('technicians.store');
 Route::get('/technicians/{technician}/edit', [ManagerController::class, 'editTechnician'])->name('technicians.edit');
 Route::put('/technicians/{technician}', [ManagerController::class, 'updateTechnician'])->name('technicians.update');
 Route::delete('/technicians/{technician}', [ManagerController::class, 'deleteTechnician'])->name('technicians.delete');
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

    // Technicians CRUD
    Route::get('/technicians', [\App\Http\Controllers\ManagerController::class, 'technicians'])->name('technicians');
    Route::get('/technicians/create', [\App\Http\Controllers\ManagerController::class, 'createTechnician'])->name('technicians.create');
    Route::post('/technicians', [\App\Http\Controllers\ManagerController::class, 'storeTechnician'])->name('technicians.store');
    Route::get('/technicians/{technician}/edit', [\App\Http\Controllers\ManagerController::class, 'editTechnician'])->name('technicians.edit');
    Route::put('/technicians/{technician}', [\App\Http\Controllers\ManagerController::class, 'updateTechnician'])->name('technicians.update');
    Route::delete('/technicians/{technician}', [\App\Http\Controllers\ManagerController::class, 'deleteTechnician'])->name('technicians.delete');

    // Device Categories CRUD
    Route::get('/categories', [\App\Http\Controllers\ManagerController::class, 'categories'])->name('categories');
    Route::post('/categories', [\App\Http\Controllers\ManagerController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\ManagerController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\ManagerController::class, 'deleteCategory'])->name('categories.delete');

    // Users Management
    Route::get('/users', [\App\Http\Controllers\ManagerController::class, 'users'])->name('users');
    Route::get('/users/create', [\App\Http\Controllers\ManagerController::class, 'createUser'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\ManagerController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\ManagerController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [\App\Http\Controllers\ManagerController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\ManagerController::class, 'deleteUser'])->name('users.delete');

    // Revenue
    Route::get('/revenue', [\App\Http\Controllers\ManagerController::class, 'revenue'])->name('revenue');

    // Customers
    Route::get('/customers', [\App\Http\Controllers\ManagerController::class, 'customers'])->name('customers');

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\ManagerController::class, 'analytics'])->name('analytics');
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
