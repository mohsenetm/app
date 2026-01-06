<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertImageRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ImageConversionController extends Controller
{
    public function convert(ConvertImageRequest $request): JsonResponse
    {
        $image = $request->file('image');
        $format = $request->input('format');
        $quality = $request->input('quality', 80);

        $result = $this->convertImageWithFfmpeg($image, $format, $quality);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تبدیل تصویر'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'تصویر با موفقیت تبدیل شد',
            'path' => $result
        ]);
    }

    public function download(string $filename)
    {
        $path = storage_path("app/temp_converted_images/{$filename}");
        
        if (!file_exists($path)) {
            abort(404, 'فایل یافت نشد');
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function convertImageWithFfmpeg($image, string $format, int $quality): ?string
    {
        $imageDirectory = 'temp_images';
        $convertedImageDirectory = 'temp_converted_images';

        if (!Storage::exists($imageDirectory) && !Storage::makeDirectory($imageDirectory)) {
            return null;
        }

        if (!Storage::exists($convertedImageDirectory) && !Storage::makeDirectory($convertedImageDirectory)) {
            return null;
        }

        $tempPath = $image->store($imageDirectory);
        $filename = pathinfo($tempPath, PATHINFO_FILENAME);
        $inputFullPath = storage_path('app/' . $tempPath);
        $outputFullPath = storage_path("app/{$convertedImageDirectory}/{$filename}.{$format}");

        $qualityOption = '-q:v ' . $quality;
        if ($format === 'avif') {
            $qualityOption = '-c:v libaom-av1 -crf ' . $quality . ' -b:v 0 -pix_fmt yuv444p';
        }

        $command = "C:/ffmpeg/ffmpeg.exe -i {$inputFullPath} {$qualityOption} {$outputFullPath}";
        exec($command, $output, $resultCode);

        unlink($inputFullPath);

        if ($resultCode !== 0) {
            return null;
        }

        return $outputFullPath;
    }
}