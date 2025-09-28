<?php

use App\Http\Controllers\StudyController;
use Illuminate\Support\Facades\Route;

Route::any('cards/{path}', [StudyController::class, 'study'])->name('cards.study');

Route::any('file/{path}/{fileName}', [StudyController::class, 'read'])->name('cards.read');
