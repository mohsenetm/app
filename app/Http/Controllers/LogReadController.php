<?php

namespace App\Http\Controllers;

use App\Models\LogRead;
use Illuminate\Http\Request;

class LogReadController extends Controller
{
    public function index()
    {
        $logReads = LogRead::query()->where('is_main', true)
            ->orderBy('id', 'desc')
            ->get();

        return view('log-reads.index', compact('logReads'));
    }
}
