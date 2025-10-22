<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\NotificationDispatcherInterface;
use app\interfaces\NotificationServiceInterface;
use app\models\Author;
use app\models\Book;
use app\models\Subscription;

/**
 * Dispatcher for book-related notifications
 * Single Responsibility: handles only notification dispatching
 */
class BookNotificationDispatcher implements NotificationDispatcherInterface
{
    private NotificationServiceInterface $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * {@inheritdoc}
     */
    public function notifySubscribers(Book $book): void
    {
        $authorIds = $book->getAuthorIds();

        if (empty($authorIds)) {
            return;
        }

        $subscriptions = Subscription::find()
            ->where(['author_id' => $authorIds])
            ->with('author')
            ->all();

        foreach ($subscriptions as $subscription) {
            if ($subscription->phone) {
                $message = $this->buildNotificationMessage($book, $subscription->author);
                $this->notificationService->send($subscription->phone, $message);
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
    private function buildNotificationMessage(Book $book, Author $author): string
    {
        return sprintf(
            'Новая книга "%s" автора %s уже в каталоге!',
            $book->title,
            $author->getFullName()
        );
    }
}
