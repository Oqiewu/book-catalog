<?php

declare(strict_types=1);

namespace app\services;

use app\models\Book;
use app\models\Subscription;
use Yii;
use yii\web\UploadedFile;
use app\models\Author;
use yii\base\InvalidConfigException;

/**
 * Service for managing books
 */
final readonly class BookService
{
    private StorageService $storageService;

    public function __construct(StorageService $storageService = null)
    {
        $this->storageService = $storageService ?? new StorageService();
    }
    /**
     * Save book with image upload and authors linking
     *
     * @param Book $book
     * @param array $authorIds
     * @return bool
     */
    public function save(Book $book, array $authorIds = []): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $isNewBook = $book->isNewRecord;

            $book->imageFile = UploadedFile::getInstance($book, 'imageFile');

            if ($book->imageFile) {
                $oldCover = $book->cover_image;
                $fileName = $this->storageService->generateFileName($book->imageFile);

                if ($this->storageService->uploadFile($book->imageFile, $fileName)) {
                    $book->cover_image = $fileName;

                    if ($oldCover && !$isNewBook) {
                        $this->storageService->deleteFile($oldCover);
                    }
                }
            }

            if (!$book->save()) {
                $transaction->rollBack();
                return false;
            }

            if (!empty($authorIds)) {
                $book->linkAuthors($authorIds);
            }

            $transaction->commit();

            if ($isNewBook) {
                $this->notifySubscribers($book);
            }

            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Delete book with its cover image
     *
     * @param Book $book
     * @return bool
     * @throws \Throwable
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

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Notify subscribers about new book
     *
     * @param Book $book
     * @return void
     * @throws InvalidConfigException
     */
    protected function notifySubscribers(Book $book): void
    {
        $authorIds = $book->getAuthorIds();

        if (empty($authorIds)) {
            return;
        }

        $subscriptions = Subscription::find()
            ->where(['author_id' => $authorIds])
            ->all();

        $smsService = new SmsService();

        foreach ($subscriptions as $subscription) {
            if ($subscription->phone) {
                $message = $this->buildNotificationMessage($book, $subscription->author);
                $smsService->send($subscription->phone, $message);
            }
        }
    }

    /**
     * Build notification message
     *
     * @param Book $book
     * @param Author $author
     * @return string
     */
    protected function buildNotificationMessage(Book $book, Author $author): string
    {
        return sprintf(
            'Новая книга "%s" автора %s уже в каталоге!',
            $book->title,
            $author->getFullName()
        );
    }
}
