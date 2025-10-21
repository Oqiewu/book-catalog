<?php

declare(strict_types=1);

namespace app\services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Yii;
use yii\web\UploadedFile;

/**
 * Service for managing file storage in MinIO (S3-compatible)
 * Falls back to local file storage if MinIO is unavailable
 */
class StorageService
{
    private ?S3Client $s3Client = null;
    private string $bucket;
    private bool $useFallbackStorage = false;
    private string $localUploadPath;

    public function __construct()
    {
        $this->bucket = $_ENV['MINIO_BUCKET'] ?? 'book-covers';
        $this->localUploadPath = Yii::getAlias('@webroot/uploads/covers/');

        try {
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'region' => 'us-east-1',
                'endpoint' => $_ENV['MINIO_ENDPOINT'] ?? 'http://minio:9000',
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $_ENV['MINIO_ROOT_USER'] ?? 'minioadmin',
                    'secret' => $_ENV['MINIO_ROOT_PASSWORD'] ?? 'minioadmin',
                ],
            ]);

            $this->ensureBucketExists();

        } catch (\Exception $e) {
            Yii::error("MinIO initialization failed: " . $e->getMessage(), __METHOD__);
            Yii::warning("Falling back to local file storage", __METHOD__);
            $this->useFallbackStorage = true;
            $this->ensureLocalDirectoryExists();
        }
    }

    /**
     * Ensure local upload directory exists
     */
    private function ensureLocalDirectoryExists(): void
    {
        if (!is_dir($this->localUploadPath)) {
            mkdir($this->localUploadPath, 0755, true);
            Yii::info("Created local upload directory: {$this->localUploadPath}", __METHOD__);
        }
    }

    /**
     * Ensure bucket exists, create if not
     */
    private function ensureBucketExists(): void
    {
        try {
            if (!$this->s3Client->doesBucketExist($this->bucket)) {
                $this->s3Client->createBucket([
                    'Bucket' => $this->bucket,
                ]);

                // Set public read policy for the bucket
                $policy = json_encode([
                    'Version' => '2012-10-17',
                    'Statement' => [
                        [
                            'Effect' => 'Allow',
                            'Principal' => '*',
                            'Action' => 's3:GetObject',
                            'Resource' => "arn:aws:s3:::{$this->bucket}/*"
                        ]
                    ]
                ]);

                $this->s3Client->putBucketPolicy([
                    'Bucket' => $this->bucket,
                    'Policy' => $policy,
                ]);

                Yii::info("Bucket {$this->bucket} created successfully", __METHOD__);
            }
        } catch (AwsException $e) {
            Yii::error("Error ensuring bucket exists: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Upload file to MinIO or local storage with unique name
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @return string|false  Returns the generated file name on success, false on failure
     */
    public function uploadFile(UploadedFile $file, string $prefix = 'cover'): string|false
    {
        do {
            $fileName = $this->generateFileName($file, $prefix);
        } while ($this->fileExists($fileName));

        if ($this->useFallbackStorage) {
            return $this->uploadToLocalStorage($file, $fileName) ? $fileName : false;
        }

        try {
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $fileName,
                'Body' => fopen($file->tempName, 'r'),
                'ContentType' => $file->type,
                'ACL' => 'public-read',
            ]);

            Yii::info("File uploaded successfully to MinIO: {$fileName}", __METHOD__);
            return $fileName;

        } catch (AwsException $e) {
            Yii::error("MinIO upload failed: " . $e->getMessage(), __METHOD__);
            Yii::warning("Attempting fallback to local storage", __METHOD__);
            $this->useFallbackStorage = true;
            return $this->uploadToLocalStorage($file, $fileName) ? $fileName : false;
        }
    }

    /**
     * Upload file to local storage (fallback)
     *
     * @param UploadedFile $file
     * @param string $fileName
     * @return bool
     */
    private function uploadToLocalStorage(UploadedFile $file, string $fileName): bool
    {
        try {
            $filePath = $this->localUploadPath . $fileName;
            if ($file->saveAs($filePath)) {
                Yii::info("File uploaded successfully to local storage: {$fileName}", __METHOD__);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Yii::error("Local storage upload failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Delete file from MinIO or local storage
     *
     * @param string $fileName
     * @return bool
     */
    public function deleteFile(string $fileName): bool
    {
        if ($this->useFallbackStorage) {
            return $this->deleteFromLocalStorage($fileName);
        }

        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $fileName,
            ]);

            Yii::info("File deleted successfully from MinIO: {$fileName}", __METHOD__);
            return true;

        } catch (AwsException $e) {
            Yii::error("MinIO delete failed: " . $e->getMessage(), __METHOD__);
            return $this->deleteFromLocalStorage($fileName);
        }
    }

    /**
     * Delete file from local storage (fallback)
     *
     * @param string $fileName
     * @return bool
     */
    private function deleteFromLocalStorage(string $fileName): bool
    {
        try {
            $filePath = $this->localUploadPath . $fileName;
            if (file_exists($filePath) && unlink($filePath)) {
                Yii::info("File deleted successfully from local storage: {$fileName}", __METHOD__);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Yii::error("Local storage delete failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Get public URL for file
     *
     * @param string $fileName
     * @return string
     */
    public function getFileUrl(string $fileName): string
    {
        if ($this->useFallbackStorage) {
            return Yii::getAlias('@web') . '/uploads/covers/' . $fileName;
        }

        $endpoint = $_ENV['MINIO_ENDPOINT'] ?? 'http://localhost:9000';

        $publicEndpoint = str_replace('minio:9000', 'localhost:9000', $endpoint);

        return "{$publicEndpoint}/{$this->bucket}/{$fileName}";
    }

    /**
     * Check if file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function fileExists(string $fileName): bool
    {
        if ($this->useFallbackStorage) {
            return file_exists($this->localUploadPath . $fileName);
        }

        try {
            return $this->s3Client->doesObjectExist($this->bucket, $fileName);
        } catch (AwsException $e) {
            Yii::error("Error checking file existence in MinIO: " . $e->getMessage(), __METHOD__);
            return file_exists($this->localUploadPath . $fileName);
        }
    }

    /**
     * Generate unique file name
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @return string
     */
    public function generateFileName(UploadedFile $file, string $prefix = 'cover'): string
    {
        return $prefix . '_' . uniqid('', true) . '.' . $file->extension;
    }
}
