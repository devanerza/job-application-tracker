<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [ApplicationController::class, 'index'])
->middleware(['auth', 'verified'])
->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('applications', ApplicationController::class);
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'statusUpdate'])
        ->name('applications.statusUpdate');
    Route::patch('/applications/{application}/follow-up', [ApplicationController::class, 'followUp'])
        ->name('applications.followUp');
});

require __DIR__.'/auth.php';
