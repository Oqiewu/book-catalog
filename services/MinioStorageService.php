<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\StorageServiceInterface;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use RuntimeException;
use Yii;
use yii\web\UploadedFile;

/**
 * MinIO (S3-compatible) storage service
 */
class MinioStorageService implements StorageServiceInterface
{
    private S3Client $s3Client;
    private string $bucket;
    private string $endpoint;

    public function __construct(?S3Client $s3Client = null, ?string $bucket = null, ?string $endpoint = null)
    {
        $this->bucket = $bucket ?? ($_ENV['MINIO_BUCKET'] ?? 'book-covers');
        $this->endpoint = $endpoint ?? ($_ENV['MINIO_ENDPOINT'] ?? 'http://minio:9000');

        $this->s3Client = $s3Client ?? new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => $this->endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $_ENV['MINIO_ROOT_USER'] ?? 'minioadmin',
                'secret' => $_ENV['MINIO_ROOT_PASSWORD'] ?? 'minioadmin',
            ],
        ]);

        $this->ensureBucketExists();
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

                $this->setBucketPublicPolicy();
                Yii::info("Bucket {$this->bucket} created successfully", __METHOD__);
            }
        } catch (AwsException $e) {
            Yii::error("Error ensuring bucket exists: " . $e->getMessage(), __METHOD__);
            throw new RuntimeException("Failed to initialize MinIO bucket: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Set public read policy for the bucket
     */
    private function setBucketPublicPolicy(): void
    {
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
        try {
            return $this->s3Client->doesObjectExist($this->bucket, $fileName);
        } catch (AwsException $e) {
            Yii::error("Error checking file existence in MinIO: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(string $fileName): bool
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $fileName,
            ]);

            Yii::info("File deleted successfully from MinIO: {$fileName}", __METHOD__);
            return true;
        } catch (AwsException $e) {
            Yii::error("MinIO delete failed: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFileUrl(string $fileName): string
    {
        $publicEndpoint = str_replace('minio:9000', 'localhost:9000', $this->endpoint);
        return "{$publicEndpoint}/{$this->bucket}/{$fileName}";
    }
}
