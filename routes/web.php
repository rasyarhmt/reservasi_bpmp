<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationOpsController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\WalkInBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/katalog', [CatalogController::class, 'index'])->name('katalog');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/reservasi', [ReservationController::class, 'index'])->name('reservations.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/reservasi/baru', [WalkInBookingController::class, 'chooser'])->name('walkin.chooser');
        Route::get('/reservasi/baru/kamar', [WalkInBookingController::class, 'roomForm'])->name('walkin.room');
        Route::get('/reservasi/baru/kamar/ketersediaan', [WalkInBookingController::class, 'roomAvailability'])->name('walkin.room.availability');
        Route::post('/reservasi/baru/kamar', [WalkInBookingController::class, 'store'])->name('walkin.store');
        Route::get('/reservasi/baru/aula', [WalkInBookingController::class, 'hallForm'])->name('walkin.hall');
        Route::get('/reservasi/baru/aula/ketersediaan', [WalkInBookingController::class, 'hallAvailability'])->name('walkin.hall.availability');
        Route::post('/reservasi/baru/aula', [WalkInBookingController::class, 'storeHall'])->name('walkin.storeHall');
        Route::get('/pelanggan', [UserController::class, 'index'])->name('users.index');
        Route::put('/pelanggan/{user}/password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/reservasi/{reservation}/check-in', [ReservationOpsController::class, 'checkIn'])
            ->name('reservations.check-in');
        Route::post('/reservasi/{reservation}/check-out', [ReservationOpsController::class, 'checkOut'])
            ->name('reservations.check-out');
        Route::get('/kasir/{reservation}', [CashierController::class, 'show'])->name('cashier.show');
        Route::post('/kasir/{reservation}/bayar', [CashierController::class, 'pay'])->name('cashier.pay');
        Route::get('/kamar/{room}', [RoomController::class, 'show'])->name('rooms.show');
        Route::get('/kamar/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/kamar/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::get('/aula', [HallController::class, 'show'])->name('halls.show');
        Route::get('/aula/edit', [HallController::class, 'edit'])->name('halls.edit');
        Route::put('/aula', [HallController::class, 'update'])->name('halls.update');
    });

    Route::middleware('role:customer')->group(function (): void {
        Route::get('/booking', [ReservationController::class, 'chooser'])->name('booking.create');
        Route::get('/booking/kamar', [ReservationController::class, 'roomForm'])->name('booking.room');
        Route::get('/booking/kamar/ketersediaan', [ReservationController::class, 'roomAvailability'])->name('booking.room.availability');
        Route::post('/booking/kamar', [ReservationController::class, 'store'])->name('booking.store');
        Route::get('/booking/aula', [ReservationController::class, 'hallForm'])->name('booking.hall');
        Route::get('/booking/aula/ketersediaan', [ReservationController::class, 'hallAvailability'])->name('booking.hall.availability');
        Route::post('/booking/aula', [ReservationController::class, 'storeHall'])->name('booking.storeHall');
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });

    Route::get('/reservasi/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
});
