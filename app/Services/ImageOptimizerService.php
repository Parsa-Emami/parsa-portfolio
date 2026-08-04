<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageOptimizerService
{
    public function store(UploadedFile $file, string $directory, int $maxWidth = 2400): array
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        $dimensions = @getimagesize($file->getRealPath());
        $width = $dimensions[0] ?? null;
        $height = $dimensions[1] ?? null;

        if (! $this->canConvertToWebp($file)) {
            $path = $file->store($directory, 'public');

            return [
                'path' => $path,
                'thumbnail_path' => null,
                'width' => $width,
                'height' => $height,
            ];
        }

        $image = $this->createImageResource($file);

        if (! $image) {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        try {
            $base = Str::uuid()->toString();
            $mainPath = "{$directory}/{$base}.webp";
            $thumbPath = "{$directory}/{$base}-thumb.webp";

            [$main, $mainWidth, $mainHeight] = $this->resize($image, (int) $width, (int) $height, $maxWidth);
            [$thumb, $thumbWidth, $thumbHeight] = $this->resize($image, (int) $width, (int) $height, 720);

            $this->writeWebp($main, $mainPath, 86);
            $this->writeWebp($thumb, $thumbPath, 80);

            imagedestroy($main);
            imagedestroy($thumb);

            return [
                'path' => $mainPath,
                'thumbnail_path' => $thumbPath,
                'width' => $mainWidth,
                'height' => $mainHeight,
            ];
        } finally {
            imagedestroy($image);
        }
    }

    public function delete(?string ...$paths): void
    {
        Storage::disk('public')->delete(array_values(array_filter($paths)));
    }

    private function canConvertToWebp(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! function_exists('imagewebp') || ! function_exists('imagecreatetruecolor')) {
            return false;
        }

        return match ($extension) {
            'jpg', 'jpeg' => function_exists('imagecreatefromjpeg'),
            'png' => function_exists('imagecreatefrompng'),
            'webp' => function_exists('imagecreatefromwebp'),
            default => false,
        };
    }

    private function createImageResource(UploadedFile $file): mixed
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'png' => @imagecreatefrompng($file->getRealPath()),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };
    }

    private function resize(mixed $source, int $width, int $height, int $maxWidth): array
    {
        if ($width <= 0 || $height <= 0) {
            throw new RuntimeException('Invalid image dimensions.');
        }

        $targetWidth = min($width, $maxWidth);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return [$canvas, $targetWidth, $targetHeight];
    }

    private function writeWebp(mixed $image, string $path, int $quality): void
    {
        $temp = tempnam(sys_get_temp_dir(), 'portfolio-webp-');

        if ($temp === false || ! imagewebp($image, $temp, $quality)) {
            throw new RuntimeException('Could not encode the uploaded image.');
        }

        Storage::disk('public')->put($path, file_get_contents($temp));
        @unlink($temp);
    }
}
