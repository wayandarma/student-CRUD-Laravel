<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student self-service profile
    Route::middleware('role:student')->group(function (): void {
        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [StudentProfileController::class, 'store'])->name('profile.store');
        Route::get('/profile/edit', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    });

    // Admin + Super Admin: student management (no ->except('show'))
    // Note: DELETE is reachable by both roles; StudentPolicy::delete is the sole enforcement layer.
    Route::middleware('role:super_admin,admin')->group(function (): void {
        Route::resource('students', StudentController::class);
    });

    // Super Admin: user management
    Route::middleware('role:super_admin')->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
