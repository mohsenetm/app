<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogReadController;
use App\Http\Controllers\MarkdownDirectoryController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\WordTranslationController;
use App\Models\LogRead;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

// Authenticated routes for reading and scanning markdown files
Route::middleware('auth')->group(function () {
    Route::get('/scan/{path}', [MarkdownDirectoryController::class, 'scanMarkdownDirectory'])
        ->name('scan.directory');

    Route::get('/cards/{path}', function (string $path) {
        return view('card', compact('path'));
    })->name('cards.show');

    Route::get('/read/{path}/{fileName}', [LogReadController::class, 'read'])
        ->name('read');

    Route::get('/history', [LogReadController::class, 'index'])
        ->name('history');
});


// Guest routes for authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});


// Authenticated routes for dashboard, timer, worker, and study tracking
Route::middleware('auth')->group(function () {
    // Authentication management
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Timer and worker views
    Route::get('/timer', [LogReadController::class, 'timer'])->name('timer');
    Route::get('/worker', [LogReadController::class, 'worker'])->name('worker');

    // Log reading tracking routes
    Route::prefix('log-read')->group(function () {
        Route::post('/start', [LogReadController::class, 'start'])->name('log-read.start');
        Route::post('/start_worker', [LogReadController::class, 'startWorker'])->name('log-read.start-worker');
        Route::post('/end', [LogReadController::class, 'end'])->name('log-read.end');
        Route::post('/end-worker', [LogReadController::class, 'endWorker'])->name('log-read.end-worker');
    });

    // Charts and analytics
    Route::get('chart/{type}', [LogReadController::class, 'chart'])->name('chart');
    Route::get('due-cards', [StudyController::class, 'getDueCards'])->name('due-cards');

    // Study tracking API (without CSRF for external requests)
    Route::prefix('api')->group(function () {
        Route::post('cards/{path}', [StudyController::class, 'study'])
            ->name('cards.study')
            ->withoutMiddleware(VerifyCsrfToken::class);

        Route::post('file/{path}/{fileName}', [StudyController::class, 'read'])
            ->name('cards.read')
            ->withoutMiddleware(VerifyCsrfToken::class);

        Route::post('track-card', [StudyController::class, 'track'])
            ->withoutMiddleware(VerifyCsrfToken::class);
    });
});

Route::prefix('words')->group(function () {
    // ترجمه و ذخیره کلمات
    Route::get('/translate', [WordTranslationController::class, 'translate']);

    // جستجو
    Route::get('/search', [WordTranslationController::class, 'search']);

    // لیست کلمات
    Route::get('/', [WordTranslationController::class, 'index']);

    // دریافت یک کلمه
    Route::get('/{word}', [WordTranslationController::class, 'show']);

    // حذف کلمه
    Route::delete('/{word}', [WordTranslationController::class, 'destroy']);
});


