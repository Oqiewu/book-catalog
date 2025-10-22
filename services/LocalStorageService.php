<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\StorageServiceInterface;
use Exception;
use RuntimeException;
use Yii;
use yii\web\UploadedFile;

/**
 * Local file system storage service
 */
class LocalStorageService implements StorageServiceInterface
{
    private string $uploadPath;
    private string $webPath;

    public function __construct(?string $uploadPath = null, ?string $webPath = null)
    {
        $this->uploadPath = $uploadPath ?? Yii::getAlias('@webroot/uploads/covers/');
        $this->webPath = $webPath ?? Yii::getAlias('@web') . '/uploads/covers/';

        $this->ensureDirectoryExists();
    }

    /**
     * Ensure upload directory exists
     */
    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0755, true) && !is_dir($this->uploadPath)) {
                throw new RuntimeException("Failed to create upload directory: {$this->uploadPath}");
            }
            Yii::info("Created local upload directory: {$this->uploadPath}", __METHOD__);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uploadFile(UploadedFile $file, string $prefix = 'cover'): string|false
    {
        do {
            $fileName = $this->generateFileName($file, $prefix);
        } while ($this->fileExists($fileName));

        try {
            $filePath = $this->uploadPath . $fileName;
            if ($file->saveAs($filePath)) {
                Yii::info("File uploaded successfully to local storage: {$fileName}", __METHOD__);
                return $fileName;
            }
            return false;
        } catch (Exception $e) {
            Yii::error("Local storage upload failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateFileName(UploadedFile $file, string $prefix = 'cover'): string
    {
        return $prefix . '_' . uniqid('', true) . '.' . $file->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists(string $fileName): bool
    {
        return file_exists($this->uploadPath . $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(string $fileName): bool
    {
        try {
            $filePath = $this->uploadPath . $fileName;
            if (file_exists($filePath) && unlink($filePath)) {
                Yii::info("File deleted successfully from local storage: {$fileName}", __METHOD__);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Yii::error("Local storage delete failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFileUrl(string $fileName): string
    {
        return $this->webPath . $fileName;
    }
}
