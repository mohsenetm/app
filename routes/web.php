<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogReadController;
use App\Http\Controllers\MarkdownDirectoryController;
use App\Http\Controllers\StudyController;
use App\Models\LogRead;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/scan/{path}', [MarkdownDirectoryController::class, 'scanMarkdownDirectory']);

    Route::get('/cards/{path}', function (string $path) {
        return view('card', compact('path'));
    });

    Route::get('/read/{path}/{fileName}', [LogReadController::class, 'read'])->name('read');

    Route::get('/history', [LogReadController::class, 'index'])->name('history');
});


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('chart',[LogReadController::class, 'chart']);



// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/timer',[LogReadController::class, 'timer']);
    Route::get('/worker',[LogReadController::class, 'worker']);
    Route::post('/log-read/start',[LogReadController::class, 'start']);
    Route::post('/log-read/start_worker',[LogReadController::class, 'startWorker']);
    Route::post('/log-read/end',[LogReadController::class, 'end']);
    Route::post('/log-read/end',[LogReadController::class, 'endWorker']);
});

Route::post('api/cards/{path}', [StudyController::class, 'study'])->name('cards.study')->withoutMiddleware(VerifyCsrfToken::class);

Route::post('api/file/{path}/{fileName}', [StudyController::class, 'read'])->name('cards.read')->withoutMiddleware(VerifyCsrfToken::class);

Route::post('api/track-card', [StudyController::class, 'track'])->withoutMiddleware(VerifyCsrfToken::class);
