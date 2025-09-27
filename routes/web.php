<?php

use App\Http\Controllers\MarkdownDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/scan/{path}', [MarkdownDirectoryController::class, 'scanMarkdownDirectory']);

Route::get('/cards/{path}', function (string $path) {
    return view('app', compact('path'));
});

require __DIR__ . '/settings.php';
