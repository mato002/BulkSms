<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Note: This service requires intervention/image package
// Install with: composer require intervention/image
// If not installed, methods will throw exceptions

class ImageOptimizationService
{
    /**
     * Optimize and store an uploaded image
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $disk = 'public',
        string $path = 'avatars',
        int $maxWidth = 800,
        int $maxHeight = 800,
        int $quality = 85
    ): string {
        // Check if intervention/image is available
        if (!class_exists(\Intervention\Image\Facades\Image::class)) {
            throw new \Exception('intervention/image package is required. Install with: composer require intervention/image');
        }

        // Create image instance
        $image = \Intervention\Image\Facades\Image::make($file);

        // Resize if needed
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Encode and store
        $encoded = $image->encode($file->getClientOriginalExtension(), $quality);
        
        $fullPath = $path . '/' . $filename;
        Storage::disk($disk)->put($fullPath, $encoded);

        return $fullPath;
    }

    /**
     * Optimize image from existing file
     */
    public function optimizeExisting(
        string $filePath,
        string $disk = 'public',
        int $maxWidth = 800,
        int $maxHeight = 800,
        int $quality = 85
    ): string {
        if (!Storage::disk($disk)->exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        if (!class_exists(\Intervention\Image\Facades\Image::class)) {
            throw new \Exception('intervention/image package is required. Install with: composer require intervention/image');
        }

        $image = \Intervention\Image\Facades\Image::make(Storage::disk($disk)->get($filePath));

        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $encoded = $image->encode($extension, $quality);
        
        Storage::disk($disk)->put($filePath, $encoded);

        return $filePath;
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail(
        string $filePath,
        string $disk = 'public',
        int $width = 200,
        int $height = 200
    ): string {
        if (!Storage::disk($disk)->exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        if (!class_exists(\Intervention\Image\Facades\Image::class)) {
            throw new \Exception('intervention/image package is required. Install with: composer require intervention/image');
        }

        $image = \Intervention\Image\Facades\Image::make(Storage::disk($disk)->get($filePath));
        $image->fit($width, $height);

        $pathInfo = pathinfo($filePath);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

        $extension = $pathInfo['extension'];
        $encoded = $image->encode($extension, 85);
        
        Storage::disk($disk)->put($thumbnailPath, $encoded);

        return $thumbnailPath;
    }
}

