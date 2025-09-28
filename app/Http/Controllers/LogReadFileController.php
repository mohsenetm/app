<?php

namespace App\Http\Controllers;

use App\Models\LogRead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogReadFileController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file_path' => 'required|string',
            'file_name' => 'required|string',
        ]);

        $logReadFile = LogRead::create([
            'user_id' => $request->user()->id,
            'file_path' => $validated['file_path'],
            'file_name' => $validated['file_name'],
            'read_at' => now(),
        ]);

        return response()->json($logReadFile, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $logs = LogRead::where('user_id', $request->user()->id)
            ->orderBy('read_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
