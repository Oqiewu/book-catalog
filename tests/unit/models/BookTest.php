<?php

namespace tests\unit\models;

use app\models\Book;
use Codeception\Test\Unit;

/**
 * Unit test for Book model
 */
class BookTest extends Unit
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

    public function testBookValidation()
    {
        $book = new Book();

        // Test empty book
        $this->assertFalse($book->validate(), 'Empty book should not validate');
        $this->assertArrayHasKey('title', $book->errors, 'Should have title error');
        $this->assertArrayHasKey('year', $book->errors, 'Should have year error');

        // Test valid book
        $book->title = 'Valid Book';
        $book->year = 2024;
        $this->assertTrue($book->validate(), 'Valid book should validate');
    }

    public function testYearValidation()
    {
        $book = new Book([
            'title' => 'Test Book',
            'year' => 999,
        ]);

        $this->assertFalse($book->validate(), 'Year 999 should not validate');
        $this->assertArrayHasKey('year', $book->errors);

        $book->year = 10000;
        $this->assertFalse($book->validate(), 'Year 10000 should not validate');

        $book->year = 2024;
        $this->assertTrue($book->validate(), 'Year 2024 should validate');
    }

    public function testIsbn10Validation()
    {
        $book = new Book([
            'title' => 'ISBN Test',
            'year' => 2024,
        ]);

        // Valid ISBN-10: 0-306-40615-2
        $book->isbn = '0-306-40615-2';
        $this->assertTrue($book->validate(), 'Valid ISBN-10 should validate');

        // Invalid ISBN-10 checksum
        $book->isbn = '0-306-40615-3';
        $this->assertFalse($book->validate(), 'Invalid ISBN-10 checksum should fail');
        $this->assertArrayHasKey('isbn', $book->errors);

        // Valid ISBN-10 with X: 043942089X
        $book->clearErrors();
        $book->isbn = '043942089X';
        $this->assertTrue($book->validate(), 'ISBN-10 with X should validate');
    }

    public function testIsbn13Validation()
    {
        $book = new Book([
            'title' => 'ISBN Test',
            'year' => 2024,
        ]);

        // Valid ISBN-13: 978-0-306-40615-7
        $book->isbn = '978-0-306-40615-7';
        $this->assertTrue($book->validate(), 'Valid ISBN-13 should validate');

        // Invalid ISBN-13 checksum
        $book->isbn = '978-0-306-40615-8';
        $this->assertFalse($book->validate(), 'Invalid ISBN-13 checksum should fail');
        $this->assertArrayHasKey('isbn', $book->errors);
    }

    public function testIsbnOptional()
    {
        $book = new Book([
            'title' => 'No ISBN Book',
            'year' => 2024,
        ]);

        // ISBN is optional
        $this->assertTrue($book->validate(), 'Book without ISBN should validate');
    }

    public function testIsbnInvalidFormat()
    {
        $book = new Book([
            'title' => 'ISBN Test',
            'year' => 2024,
        ]);

        // Invalid length
        $book->isbn = '123456';
        $this->assertFalse($book->validate(), 'Short ISBN should fail');
        $this->assertArrayHasKey('isbn', $book->errors);

        // Invalid characters
        $book->clearErrors();
        $book->isbn = '978-0-306-ABC-7';
        $this->assertFalse($book->validate(), 'ISBN with letters should fail');
    }

    public function testUniqueIsbn()
    {
        // Create first book
        $book1 = new Book([
            'title' => 'First Book',
            'year' => 2024,
            'isbn' => '978-0-306-40615-7',
        ]);
        $book1->save();

        // Try to create second book with same ISBN
        $book2 = new Book([
            'title' => 'Second Book',
            'year' => 2024,
            'isbn' => '978-0-306-40615-7',
        ]);

        $this->assertFalse($book2->validate(), 'Duplicate ISBN should fail');
        $this->assertArrayHasKey('isbn', $book2->errors);

        // Cleanup
        $book1->delete();
    }
}
