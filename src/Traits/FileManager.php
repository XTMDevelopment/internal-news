<?php

namespace XTraMile\News\Traits;

use DateInterval;
use DateTimeInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

trait FileManager
{
    protected array $allowedImageMimes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    protected array $allowedVideoMimes = [
        'video/mp4',
        'video/mpeg',
        'video/quicktime',
        'video/x-msvideo',
        'video/webm',
        'video/ogg',
    ];

    protected array $allowedPdfMimes = [
        'application/pdf',
    ];

    protected function getAllowedExtensions(): array
    {
        return [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'mp4', 'mpeg', 'mov', 'avi', 'webm', 'ogg',
            'pdf',
        ];
    }

    protected function getAllowedMimes(): array
    {
        return array_merge(
            $this->allowedImageMimes,
            $this->allowedVideoMimes,
            $this->allowedPdfMimes,
        );
    }

    /**
     * @throws ValidationException
     */
    protected function validateFileType(UploadedFile $file): bool
    {
        $validator = Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:' . implode(',', $this->getAllowedExtensions()),
                    'mimetypes:' . implode(',', $this->getAllowedMimes()),
                ]
            ]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'file' => 'File must be an image, video, or PDF. Allowed types: ' . implode(', ', $this->getAllowedExtensions())
            ]);
        }

        return true;
    }

    /**
     * @throws ValidationException
     */
    protected function uploadFile(
        Request $request,
        string $key,
        ?string $folderName = null,
        ?string $fileName = null,
    ): string|false
    {
        if (!$request->hasFile($key)) {
            return false;
        }

        $file = $request->file($key);
        $this->validateFileType($file);

        $fileType = $this->getFileType($file);

        if ($folderName === null) {
            $folderName = $fileName;
        }

        $extension = $file->getClientOriginalExtension();

        if ($fileName === null) {
            $fileName = time() . '.' . $extension;

        } else {
            $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = $fileNameWithoutExt . '.' . $extension;
        }

        $fileName = $this->sanitizeFileName($fileName);
        if ($fileType === 'images' && strtolower($file->getClientOriginalExtension() !== 'svg')) {
            $file = $this->compressImage($file);
        }

        $fullPath = rtrim($folderName, '/') . '/' . $fileName;
        $stored = Storage::putFileAs(
            $folderName,
            $file,
            basename($fullPath)
        );

        return $stored ? $fullPath : false;
    }

    protected function getFile(string $path): string|false
    {
        if (!Storage::exists($path)) {
            return false;
        }

        return Storage::get($path);
    }

    protected function getFileUrl(string $path): ?string
    {
        if (!Storage::exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    protected function getTemporaryUrl(
        string $path,
        DateTimeInterface|DateInterval|int $expiration
    ): ?string
    {
        if (!Storage::exists($path)) {
            return null;
        }

        try {
            return Storage::temporaryUrl($path, $expiration);

        } catch (Exception) {
            return null;
        }
    }

    protected function deleteFile(string $path): bool
    {
        if (!Storage::exists($path)) {
            return false;
        }

        return Storage::delete($path);
    }

    protected function deleteFiles(array $paths): bool
    {
        $deleted = true;

        foreach ($paths as $path) {
            if (!$this->deleteFile($path)) {
                $deleted = false;
            }
        }

        return $deleted;
    }

    protected function getFileSize(string $path): int|false
    {
        if (!Storage::exists($path)) {
            return false;
        }

        return Storage::size($path);
    }

    protected function getFileSizeHumanReadable(string $path): string|false
    {
        $size = $this->getFileSize($path);
        if ($size === false) {
            return false;
        }

        return $this->formatBytes($size);
    }

    protected function getFolderSize(string $folderPath = '', bool $recursive = true): int
    {
        $totalSize = 0;
        $files = Storage::files($folderPath, $recursive);

        foreach ($files as $file) {
            $size = Storage::size($file);
            $totalSize += $size;
        }

        return $totalSize;
    }

    protected function getFolderSizeHumanReadable(string $folderPath = '', bool $recursive = true): string
    {
        $size = $this->getFolderSize($folderPath, $recursive);
        return $this->formatBytes($size);
    }

    protected function fileExists(string $path): bool
    {
        return Storage::exists($path);
    }

    protected function listFiles(string $folderPath = '', bool $recursive = false): array
    {
        return Storage::files($folderPath, $recursive);
    }

    protected function listDirectories(string $folderPath = '', bool $recursive = false): array
    {
        return Storage::directories($folderPath, $recursive);
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function sanitizeFileName(string $fileName): string
    {
        $fileName = preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $fileName);
        $fileName = preg_replace('/-+/', '-', $fileName);
        return trim($fileName, '-.');
    }

    protected function deleteFolderFiles(string $folderPath = '', bool $recursive = false): bool
    {
        $files = Storage::files($folderPath, $recursive);

        if (empty($files)) {
            return true;
        }

        return Storage::delete($files);
    }

    protected function getFileType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        if (in_array($mimeType, $this->allowedImageMimes)) return 'images';
        if (in_array($mimeType, $this->allowedVideoMimes)) return 'videos';
        if (in_array($mimeType, $this->allowedPdfMimes)) return 'pdf';
        return 'others';
    }

    protected function compressImage(UploadedFile $file): UploadedFile
    {
        try {
            $image = Image::make($file->getRealPath());

            $image->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $tempPath = tempnam(sys_get_temp_dir(), 'compressed_');
            $extension = $file->getClientOriginalExtension();

            $quality = 80;

            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    $image->encode('jpg', $quality)->save($tempPath);
                    break;
                case 'png':
                    $image->encode('png')->save($tempPath);
                    break;
                case 'gif':
                    $image->encode('gif')->save($tempPath);
                    break;
                case 'webp':
                    $image->encode('webp', $quality)->save($tempPath);
                    break;
                default:
                    $image->encode('jpg', $quality)->save($tempPath);
            }

            return new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                $file->getMimeType(),
                null,
            );

        } catch (Exception) {
            return $file;
        }
    }
}