<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectionDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:service achat|service cf'])->group(function () {

Route::get('/directiondashboard', [DirectionDashboardController::class, 'index'])->name('directiondashboard');

});

Route::middleware(['auth', 'role:service centre'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});
