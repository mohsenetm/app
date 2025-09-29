<?php

namespace App\Http\Controllers;

use App\Models\LogRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogReadController extends Controller
{
    public function index()
    {
        $logReads = LogRead::query()->where('is_main', true)
            ->orderBy('id', 'desc')
            ->get();

        return view('log-reads.index', compact('logReads'));
    }

    public function read(string $path, string $fileName)
    {
        $files = File::allFiles($path);

        foreach ($files as $key => $file) {
            if ($file->getExtension() !== 'md') {
                unset($files[$key]);
            }
        }

//        dd($files);

        return view('read', compact('path', 'fileName', 'files'));
    }
}
