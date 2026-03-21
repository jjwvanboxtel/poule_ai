<?php declare(strict_types=1);

namespace App\Infrastructure\Storage;

use RuntimeException;

final class LogoStorage
{
    private readonly string $storagePath;
    private readonly string $publicPath;

    public function __construct(string $basePath)
    {
        $this->storagePath = $basePath . '/storage/logos';
        $this->publicPath = '/storage/logos';
    }

    /**
     * Store an uploaded logo file and return the public path.
     *
     * @param array{tmp_name: string, name: string, error: int, size: int} $file
     * @throws RuntimeException if the upload fails validation or storage.
     */
    public function store(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Logo upload failed with error code: ' . $file['error']);
        }

        $maxSize = 2 * 1024 * 1024; // 2 MB
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('Logo bestand mag maximaal 2 MB zijn.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            throw new RuntimeException('Ongeldig bestandstype. Toegestane typen: ' . implode(', ', $allowed));
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if ($mimeType === false) {
            throw new RuntimeException('Kan het MIME-type van het bestand niet bepalen.');
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes, true)) {
            throw new RuntimeException('Ongeldig MIME-type: ' . $mimeType);
        }

        if (!is_dir($this->storagePath)) {
            if (!mkdir($this->storagePath, 0755, true) && !is_dir($this->storagePath)) {
                throw new RuntimeException('Kon de logo map niet aanmaken.');
            }
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->storagePath . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Kon het logo bestand niet opslaan.');
        }

        return $this->publicPath . '/' . $filename;
    }

    /**
     * Delete an existing logo file by its stored path.
     */
    public function delete(string $publicPath): void
    {
        if (!str_starts_with($publicPath, $this->publicPath)) {
            return;
        }

        $relativePath = substr($publicPath, strlen($this->publicPath));
        $fullPath = $this->storagePath . $relativePath;

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
