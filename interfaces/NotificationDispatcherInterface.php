<?php

declare(strict_types=1);

namespace app\interfaces;

use app\models\Book;

/**
 * Interface for dispatching notifications about new books
 */
interface NotificationDispatcherInterface
{
    /**
     * Notify subscribers about new book
     *
     * @param Book $book
     * @return void
     */
    public function notifySubscribers(Book $book): void;
}
