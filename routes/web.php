<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogReadController;
use App\Http\Controllers\MarkdownDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/scan/{path}', [MarkdownDirectoryController::class, 'scanMarkdownDirectory']);

Route::get('/cards/{path}', function (string $path) {
    return view('card', compact('path'));
});

Route::get('/read/{path}/{fileName}', function (string $path, string $fileName) {
    return view('read', compact('path', 'fileName'));
});

Route::get('/log-reads', [LogReadController::class, 'index']);


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
