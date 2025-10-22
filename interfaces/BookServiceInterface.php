<?php

declare(strict_types=1);

namespace app\interfaces;

use app\models\Book;

/**
 * Interface for book management services
 */
interface BookServiceInterface
{
    /**
     * Save book with image upload and authors linking
     *
     * @param Book $book
     * @param array $authorIds
     * @return bool
     */
    public function save(Book $book, array $authorIds = []): bool;

    /**
     * Delete book with its cover image
     *
     * @param Book $book
     * @return bool
     */
    public function delete(Book $book): bool;
}
