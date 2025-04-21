<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');


Route::get('/testrole', function () {
    return Inertia::render('testrole');
})->middleware(['auth', 'role:admin']);

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('about', function () {
    return Inertia::render('About');
})->middleware(['auth', 'verified'])->name('about');

Route::get('dra', function () {
    return Inertia::render('DRA');
})->middleware(['auth', 'verified'])->name('dra');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
