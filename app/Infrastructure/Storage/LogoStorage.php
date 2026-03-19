<?php declare(strict_types=1);

namespace App\Infrastructure\Storage;

final class LogoStorage
{
    private readonly string $storageDir;

    public function __construct(string $basePath)
    {
        $this->storageDir = $basePath . '/storage/logos';
    }

    /** @param array<string, mixed> $fileInfo */
    public function store(array $fileInfo, string $slug): string
    {
        $tmpPath = is_string($fileInfo['tmp_name'] ?? null) ? $fileInfo['tmp_name'] : '';
        $size = is_numeric($fileInfo['size'] ?? null) ? (int) $fileInfo['size'] : 0;
        $error = is_numeric($fileInfo['error'] ?? null) ? (int) $fileInfo['error'] : UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new \RuntimeException('Invalid file upload.');
        }

        if ($size > 2 * 1024 * 1024) {
            throw new \RuntimeException('File exceeds 2 MB limit.');
        }

        $mime = mime_content_type($tmpPath);
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if (!is_string($mime) || !isset($extMap[$mime])) {
            throw new \RuntimeException('Invalid image type. Allowed: JPEG, PNG, GIF, WebP.');
        }

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        $ext = $extMap[$mime];
        $filename = preg_replace('/[^a-z0-9\-_]/', '-', strtolower($slug)) . '.' . $ext;
        $destination = $this->storageDir . '/' . $filename;

        if ($filename === null) {
            throw new \RuntimeException('Failed to generate valid filename.');
        }

        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return 'logos/' . $filename;
    }

    public function delete(string $path): void
    {
        $fullPath = dirname($this->storageDir) . '/' . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
