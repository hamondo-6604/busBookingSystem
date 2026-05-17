<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\StopController;
use App\Http\Controllers\Admin\TerminalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\BusController;
use App\Http\Controllers\Admin\BusTypeController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\CmsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Stub routes for the sidebar links
Route::view('/analytics', 'admin.coming_soon')->name('analytics');

// Operations
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
Route::resource('trips', TripController::class);
Route::resource('routes', RouteController::class);
Route::put('routes/{route}/stops', [RouteController::class, 'syncStops'])->name('routes.stops.sync');
Route::resource('stops', StopController::class)->except(['show', 'create', 'edit']);
Route::resource('terminals', TerminalController::class)->except(['show', 'create', 'edit']);
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::resource('promotions', PromotionController::class);

// Fleet
Route::resource('buses', BusController::class);
Route::resource('bus-types', BusTypeController::class)->parameters(['bus-types' => 'busType']);
Route::get('/seats', [SeatController::class, 'index'])->name('seats.index');
Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');

// People
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
Route::put('/drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');

Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');

// System
Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
Route::resource('cities', CityController::class);
Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
Route::post('/trash/{type}/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
Route::delete('/trash/{type}/{id}/force-delete', [TrashController::class, 'forceDelete'])->name('trash.force-delete');

// CMS
Route::get('/cms/landing', [CmsController::class, 'landing'])->name('cms.landing');
Route::post('/cms/landing', [CmsController::class, 'updateLanding'])->name('cms.landing.update');