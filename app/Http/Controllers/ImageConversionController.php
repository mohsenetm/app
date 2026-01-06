<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertImageRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class ImageConversionController extends Controller
{
    public function image()
    {
        return view('image');
    }

    public function convert(ConvertImageRequest $request): JsonResponse
    {
        $startTime = microtime(true);
        $image = $request->file('image');
        $format = $request->input('format') ?? $image->getClientOriginalExtension();
        $qualityInput = $request->input('quality', 'ultra');

        // Map quality levels to numeric values (different for jpg and webp)
        $qualityMapJpg = [
            'ultra' => 32,
            'high' => 16,
            'medium' => 8,
            'low' => 4
        ];

        $qualityMapWebp = [
            'ultra' => 30,
            'high' => 50,
            'medium' => 70,
            'low' => 90
        ];

        $qualityMap = ($format === 'jpg' || $format === 'jpeg') ? $qualityMapJpg : $qualityMapWebp;
        $quality = $qualityMap[$qualityInput] ?? 32;

        $originalSize = $image->getSize();
        $result = $this->convertImageWithFfmpeg($image, $format, $quality);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تبدیل تصویر'
            ], 500);
        }

        $convertedSize = filesize($result);
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $originalSizeKb = round($originalSize / 1024, 2);
        $convertedSizeKb = round($convertedSize / 1024, 2);
        $compressionPercent = round((($originalSize - $convertedSize) / $originalSize) * 100, 2);

        return response()->json([
            'success' => true,
            'message' => 'تصویر با موفقیت تبدیل شد',
            'path' => $result,
            'original_size_kb' => (int)$originalSizeKb,
            'converted_size_kb' => (int)$convertedSizeKb,
            'compression_percent' => (int)$compressionPercent,
            'duration_seconds' => $duration
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

    private function convertImageWithFfmpeg(UploadedFile $image, string $format, int $quality): ?string
    {
        $imageDirectory = 'temp_images';
        $convertedImageDirectory = 'temp_converted_images';

        $storagePath = storage_path('app/public');

        if (!is_dir($storagePath . '/' . $imageDirectory)) {
            mkdir($storagePath . '/' . $imageDirectory, 0755, true);
        }

        if (!is_dir($storagePath . '/' . $convertedImageDirectory)) {
            mkdir($storagePath . '/' . $convertedImageDirectory, 0755, true);
        }

        $filename = uniqid() . '_' . time();
        $extension = $image->getClientOriginalExtension();
        $tempFileName = $filename . '.' . $extension;

        // ذخیره فایل موقت
        $image->move($storagePath . '/' . $imageDirectory, $tempFileName);

        $inputFullPath = $storagePath . '/' . $imageDirectory . '/' . $tempFileName;
        $outputFullPath = $storagePath . "/{$convertedImageDirectory}/{$filename}.{$format}";

        $qualityOption = '-q:v ' . $quality;
        if ($format === 'avif') {
            $qualityOption = '-c:v libaom-av1 -crf ' . $quality . ' -b:v 0 -pix_fmt yuv444p';
        }

        $command = "C:/ffmpeg/ffmpeg.exe -i \"{$inputFullPath}\" {$qualityOption} \"{$outputFullPath}\" 2>&1";
        exec($command, $output, $resultCode);


        if (file_exists($inputFullPath)) {
            unlink($inputFullPath);
        }

        if ($resultCode !== 0 || !file_exists($outputFullPath)) {
            return null;
        }

        return $outputFullPath;
    }
}
