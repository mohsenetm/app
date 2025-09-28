<?php

use App\Http\Controllers\LogReadController;
use App\Http\Controllers\MarkdownDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/scan/{path}', [MarkdownDirectoryController::class, 'scanMarkdownDirectory']);

Route::get('/cards/{path}', function (string $path) {
    return view('app', compact('path'));
});

Route::get('/read/{path}/{fileName}', function (string $path, string $fileName) {
    return view('read', compact('path', 'fileName'));
});

Route::get('/log-reads', [LogReadController::class, 'index']);

require __DIR__ . '/settings.php';
