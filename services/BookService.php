<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\BookServiceInterface;
use app\interfaces\NotificationDispatcherInterface;
use app\interfaces\StorageServiceInterface;
use app\models\Book;
use Exception;
use Yii;
use yii\web\UploadedFile;

/**
 * Service for managing books
 * Single Responsibility: handles only book CRUD operations
 */
final readonly class BookService implements BookServiceInterface
{
    private StorageServiceInterface $storageService;
    private NotificationDispatcherInterface $notificationDispatcher;

    public function __construct(
        StorageServiceInterface $storageService,
        NotificationDispatcherInterface $notificationDispatcher
    ) {
        $this->storageService = $storageService;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Book $book, array $authorIds = []): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $isNewBook = $book->isNewRecord;

            $this->handleImageUpload($book);

            if (!$book->save()) {
                $transaction->rollBack();
                return false;
            }

            if (!empty($authorIds)) {
                $book->linkAuthors($authorIds);
            }

            $transaction->commit();

            if ($isNewBook) {
                $this->notificationDispatcher->notifySubscribers($book);
            }

            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Handle image upload for the book
     *
     * @param Book $book
     * @return void
     */
    private function handleImageUpload(Book $book): void
    {
        $book->imageFile = UploadedFile::getInstance($book, 'imageFile');

        if ($book->imageFile) {
            $oldCover = $book->cover_image;
            $fileName = $this->storageService->generateFileName($book->imageFile);

            if ($this->storageService->uploadFile($book->imageFile, $fileName)) {
                $book->cover_image = $fileName;

                if ($oldCover && !$book->isNewRecord) {
                    $this->storageService->deleteFile($oldCover);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Book $book): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($book->cover_image) {
                $this->storageService->deleteFile($book->cover_image);
            }

            if (!$book->delete()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }
}
