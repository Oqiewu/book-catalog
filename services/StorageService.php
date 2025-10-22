<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\StorageServiceInterface;
use Exception;
use Yii;
use yii\web\UploadedFile;

/**
 * Storage service with automatic fallback from MinIO to local storage
 * Adapter pattern implementation
 */
class StorageService implements StorageServiceInterface
{
    private StorageServiceInterface $primaryStorage;
    private StorageServiceInterface $fallbackStorage;
    private bool $useFallback = false;

    public function __construct(
        ?StorageServiceInterface $primaryStorage = null,
        ?StorageServiceInterface $fallbackStorage = null
    ) {
        $this->fallbackStorage = $fallbackStorage ?? new LocalStorageService();

        try {
            $this->primaryStorage = $primaryStorage ?? new MinioStorageService();
        } catch (Exception $e) {
            Yii::error("Primary storage initialization failed: " . $e->getMessage(), __METHOD__);
            Yii::warning("Using fallback storage from the start", __METHOD__);
            $this->useFallback = true;
            $this->primaryStorage = $this->fallbackStorage;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uploadFile(UploadedFile $file, string $prefix = 'cover'): string|false
    {
        if ($this->useFallback) {
            return $this->fallbackStorage->uploadFile($file, $prefix);
        }

        $result = $this->primaryStorage->uploadFile($file, $prefix);

        if ($result === false) {
            Yii::warning("Primary storage upload failed, trying fallback", __METHOD__);
            $this->useFallback = true;
            return $this->fallbackStorage->uploadFile($file, $prefix);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(string $fileName): bool
    {
        if ($this->useFallback) {
            return $this->fallbackStorage->deleteFile($fileName);
        }

        $result = $this->primaryStorage->deleteFile($fileName);

        if (!$result) {
            Yii::warning("Primary storage delete failed, trying fallback", __METHOD__);
            return $this->fallbackStorage->deleteFile($fileName);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileUrl(string $fileName): string
    {
        if ($this->useFallback) {
            return $this->fallbackStorage->getFileUrl($fileName);
        }

        return $this->primaryStorage->getFileUrl($fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists(string $fileName): bool
    {
        if ($this->useFallback) {
            return $this->fallbackStorage->fileExists($fileName);
        }

        return $this->primaryStorage->fileExists($fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFileName(UploadedFile $file, string $prefix = 'cover'): string
    {
        return $this->primaryStorage->generateFileName($file, $prefix);
    }
}
