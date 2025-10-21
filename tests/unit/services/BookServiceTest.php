<?php

namespace tests\unit\services;

use app\services\BookService;
use app\services\StorageService;
use app\models\Book;
use app\models\Author;
use Codeception\Test\Unit;

/**
 * Unit test for BookService
 */
class BookServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testSaveBookWithoutImage()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Test',
            'last_name' => 'Author',
        ]);
        $author->save();

        // Create test book
        $book = new Book([
            'title' => 'Test Book',
            'year' => 2024,
            'description' => 'Test description',
        ]);

        $service = new BookService();
        $result = $service->save($book, [$author->id]);

        $this->assertTrue($result, 'Book should be saved successfully');
        $this->assertNotNull($book->id, 'Book should have an ID after save');

        // Cleanup
        $book->delete();
        $author->delete();
    }

    public function testDeleteBook()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Test',
            'last_name' => 'Author',
        ]);
        $author->save();

        // Create test book
        $book = new Book([
            'title' => 'Test Book to Delete',
            'year' => 2024,
        ]);
        $book->save();
        $book->linkAuthors([$author->id]);

        $bookId = $book->id;

        $service = new BookService();
        $result = $service->delete($book);

        $this->assertTrue($result, 'Book should be deleted successfully');
        $this->assertNull(Book::findOne($bookId), 'Book should not exist after deletion');

        // Cleanup
        $author->delete();
    }

    public function testSaveWithTransaction()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Transaction',
            'last_name' => 'Test',
        ]);
        $author->save();

        // Create book with invalid year (to test rollback)
        $book = new Book([
            'title' => 'Invalid Book',
            'year' => 999, // Invalid year - should fail validation
        ]);

        $service = new BookService();
        $result = $service->save($book, [$author->id]);

        $this->assertFalse($result, 'Save should fail for invalid book');
        $this->assertNull($book->id, 'Book should not be saved');

        // Cleanup
        $author->delete();
    }
}
