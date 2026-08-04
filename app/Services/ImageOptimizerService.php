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
        $width = isset($dimensions[0]) ? (int) $dimensions[0] : null;
        $height = isset($dimensions[1]) ? (int) $dimensions[1] : null;

        if (! $this->canConvertToWebp($file)) {
            return $this->storeOriginal($file, $directory, $width, $height);
        }

        $image = $this->createImageResource($file);

        /*
         * Some images pass MIME / dimension validation but cannot be decoded by
         * the particular GD + libpng build installed on the server. Optimisation
         * is optional; a decoder mismatch must not turn a valid upload request
         * into a 500 response. Preserve the original and continue without a
         * generated thumbnail.
         */
        if (! $image || ! $width || ! $height) {
            return $this->storeOriginal($file, $directory, $width, $height);
        }

        try {
            $base = Str::uuid()->toString();
            $mainPath = "{$directory}/{$base}.webp";
            $thumbPath = "{$directory}/{$base}-thumb.webp";

            [$main, $mainWidth, $mainHeight] = $this->resize(
                $image,
                $width,
                $height,
                $maxWidth,
            );

            [$thumb] = $this->resize(
                $image,
                $width,
                $height,
                720,
            );

            try {
                $this->writeWebp($main, $mainPath, 86);
                $this->writeWebp($thumb, $thumbPath, 80);
            } catch (\Throwable $exception) {
                $disk->delete([$mainPath, $thumbPath]);

                throw $exception;
            } finally {
                imagedestroy($main);
                imagedestroy($thumb);
            }

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

    private function storeOriginal(
        UploadedFile $file,
        string $directory,
        ?int $width,
        ?int $height,
    ): array {
        $path = $file->store($directory, 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The uploaded image could not be stored.');
        }

        return [
            'path' => $path,
            'thumbnail_path' => null,
            'width' => $width,
            'height' => $height,
        ];
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
            'webp' => function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($file->getRealPath())
                : false,
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

        if (! $canvas) {
            throw new RuntimeException('Could not allocate an image canvas.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);

        if (! imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        )) {
            imagedestroy($canvas);

            throw new RuntimeException('Could not resize the uploaded image.');
        }

        return [$canvas, $targetWidth, $targetHeight];
    }

    private function writeWebp(mixed $image, string $path, int $quality): void
    {
        $temp = tempnam(sys_get_temp_dir(), 'portfolio-webp-');

        if ($temp === false) {
            throw new RuntimeException('Could not create a temporary image file.');
        }

        try {
            if (! imagewebp($image, $temp, $quality)) {
                throw new RuntimeException('Could not encode the uploaded image.');
            }

            $contents = file_get_contents($temp);

            if ($contents === false || ! Storage::disk('public')->put($path, $contents)) {
                throw new RuntimeException('Could not store the optimized image.');
            }
        } finally {
            @unlink($temp);
        }
    }
}
