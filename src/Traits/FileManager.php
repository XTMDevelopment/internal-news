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

/**
 * Trait for managing file uploads, storage, and manipulation.
 *
 * Provides methods for validating, uploading, compressing, and managing files including images, videos, and PDFs.
 */
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

    /**
     * Get the list of allowed file extensions.
     *
     * @return array<int, string>
     */
    protected function getAllowedExtensions(): array
    {
        return [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'mp4', 'mpeg', 'mov', 'avi', 'webm', 'ogg',
            'pdf',
        ];
    }

    /**
     * Get the list of allowed MIME types.
     *
     * @return array<int, string>
     */
    protected function getAllowedMimes(): array
    {
        return array_merge(
            $this->allowedImageMimes,
            $this->allowedVideoMimes,
            $this->allowedPdfMimes,
        );
    }

    /**
     * Validate that the uploaded file is of an allowed type.
     *
     * @param UploadedFile $file The file to validate
     * @return bool Returns true if the file is valid
     * @throws ValidationException If the file type is not allowed
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
     * Upload a file from the request to storage.
     *
     * @param Request $request The HTTP request containing the file
     * @param string $key The key name of the file in the request
     * @param string|null $folderName The folder name to store the file in
     * @param string|null $fileName The desired file name (without extension)
     * @return string|false The full path to the stored file, or false on failure
     * @throws ValidationException If the file type is not allowed
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

    /**
     * Get the contents of a file from storage.
     *
     * @param string $path The path to the file
     * @return string|false The file contents, or false if the file doesn't exist
     */
    protected function getFile(string $path): string|false
    {
        if (!Storage::exists($path)) {
            return false;
        }

        return Storage::get($path);
    }

    /**
     * Get the public URL for a file.
     *
     * @param string $path The path to the file
     * @return string|null The public URL, or null if the file doesn't exist
     */
    protected function getFileUrl(string $path): ?string
    {
        if (!Storage::exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    /**
     * Get a temporary URL for a file that expires after a specified time.
     *
     * @param string $path The path to the file
     * @param DateTimeInterface|DateInterval|int $expiration The expiration time
     * @return string|null The temporary URL, or null if the file doesn't exist or URL generation fails
     */
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

    /**
     * Delete a file from storage.
     *
     * @param string $path The path to the file to delete
     * @return bool True if the file was deleted, false if it doesn't exist
     */
    protected function deleteFile(string $path): bool
    {
        if (!Storage::exists($path)) {
            return false;
        }

        return Storage::delete($path);
    }

    /**
     * Delete multiple files from storage.
     *
     * @param array<int, string> $paths The paths to the files to delete
     * @return bool True if all files were deleted, false if any deletion failed
     */
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

    /**
     * Get the size of a file in bytes.
     *
     * @param string $path The path to the file
     * @return int|false The file size in bytes, or false if the file doesn't exist
     */
    protected function getFileSize(string $path): int|false
    {
        if (!Storage::exists($path)) {
            return false;
        }

        return Storage::size($path);
    }

    /**
     * Get the size of a file in human-readable format (e.g., "1.5 MB").
     *
     * @param string $path The path to the file
     * @return string|false The human-readable file size, or false if the file doesn't exist
     */
    protected function getFileSizeHumanReadable(string $path): string|false
    {
        $size = $this->getFileSize($path);
        if ($size === false) {
            return false;
        }

        return $this->formatBytes($size);
    }

    /**
     * Get the total size of all files in a folder.
     *
     * @param string $folderPath The path to the folder
     * @param bool $recursive Whether to include subdirectories
     * @return int The total size in bytes
     */
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

    /**
     * Get the total size of all files in a folder in human-readable format.
     *
     * @param string $folderPath The path to the folder
     * @param bool $recursive Whether to include subdirectories
     * @return string The human-readable folder size
     */
    protected function getFolderSizeHumanReadable(string $folderPath = '', bool $recursive = true): string
    {
        $size = $this->getFolderSize($folderPath, $recursive);
        return $this->formatBytes($size);
    }

    /**
     * Check if a file exists in storage.
     *
     * @param string $path The path to the file
     * @return bool True if the file exists, false otherwise
     */
    protected function fileExists(string $path): bool
    {
        return Storage::exists($path);
    }

    /**
     * List all files in a folder.
     *
     * @param string $folderPath The path to the folder
     * @param bool $recursive Whether to include subdirectories
     * @return array<int, string> Array of file paths
     */
    protected function listFiles(string $folderPath = '', bool $recursive = false): array
    {
        return Storage::files($folderPath, $recursive);
    }

    /**
     * List all directories in a folder.
     *
     * @param string $folderPath The path to the folder
     * @param bool $recursive Whether to include subdirectories
     * @return array<int, string> Array of directory paths
     */
    protected function listDirectories(string $folderPath = '', bool $recursive = false): array
    {
        return Storage::directories($folderPath, $recursive);
    }

    /**
     * Format bytes into a human-readable string (e.g., "1.5 MB").
     *
     * @param int $bytes The number of bytes
     * @param int $precision The number of decimal places
     * @return string The formatted string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Sanitize a file name by removing invalid characters.
     *
     * @param string $fileName The file name to sanitize
     * @return string The sanitized file name
     */
    protected function sanitizeFileName(string $fileName): string
    {
        $fileName = preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $fileName);
        $fileName = preg_replace('/-+/', '-', $fileName);
        return trim($fileName, '-.');
    }

    /**
     * Delete all files in a folder.
     *
     * @param string $folderPath The path to the folder
     * @param bool $recursive Whether to include subdirectories
     * @return bool True if all files were deleted, false otherwise
     */
    protected function deleteFolderFiles(string $folderPath = '', bool $recursive = false): bool
    {
        $files = Storage::files($folderPath, $recursive);

        if (empty($files)) {
            return true;
        }

        return Storage::delete($files);
    }

    /**
     * Determine the file type based on MIME type.
     *
     * @param UploadedFile $file The uploaded file
     * @return string The file type: 'images', 'videos', 'pdf', or 'others'
     */
    protected function getFileType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        if (in_array($mimeType, $this->allowedImageMimes)) return 'images';
        if (in_array($mimeType, $this->allowedVideoMimes)) return 'videos';
        if (in_array($mimeType, $this->allowedPdfMimes)) return 'pdf';
        return 'others';
    }

    /**
     * Compress and resize an image file.
     *
     * @param UploadedFile $file The image file to compress
     * @return UploadedFile The compressed image file, or the original file if compression fails
     */
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