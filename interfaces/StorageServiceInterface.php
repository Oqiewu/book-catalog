<?php

declare(strict_types=1);

namespace app\interfaces;

use yii\web\UploadedFile;

/**
 * Interface for file storage services
 */
interface StorageServiceInterface
{
    /**
     * Upload file to storage
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @return string|false Returns the file name on success, false on failure
     */
    public function uploadFile(UploadedFile $file, string $prefix = 'cover'): string|false;

    /**
     * Delete file from storage
     *
     * @param string $fileName
     * @return bool
     */
    public function deleteFile(string $fileName): bool;

    /**
     * Get public URL for file
     *
     * @param string $fileName
     * @return string
     */
    public function getFileUrl(string $fileName): string;

    /**
     * Check if file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function fileExists(string $fileName): bool;

    /**
     * Generate unique file name
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @return string
     */
    public function generateFileName(UploadedFile $file, string $prefix = 'cover'): string;
}
