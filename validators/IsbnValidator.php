<?php

declare(strict_types=1);

namespace app\validators;

use app\interfaces\IsbnValidatorInterface;

/**
 * ISBN validator with checksum verification
 * Single Responsibility: validates ISBN format and checksum
 */
class IsbnValidator implements IsbnValidatorInterface
{
    private ?string $error = null;

    /**
     * {@inheritdoc}
     */
    public function validate(string $isbn): bool
    {
        $this->error = null;

        if (empty($isbn)) {
            return true; // ISBN is optional
        }

        // Remove dashes and spaces
        $cleanIsbn = str_replace(['-', ' '], '', $isbn);

        // Check if contains only allowed characters
        if (!preg_match('/^[\dX]+$/', $cleanIsbn)) {
            $this->error = 'ISBN может содержать только цифры, дефисы и символ X';
            return false;
        }

        $length = strlen($cleanIsbn);

        if ($length === 10) {
            return $this->validateIsbn10($cleanIsbn);
        }

        if ($length === 13) {
            return $this->validateIsbn13($cleanIsbn);
        }

        $this->error = 'ISBN должен содержать 10 или 13 символов (без учета дефисов)';
        return false;
    }

    /**
     * Validate ISBN-10 checksum
     *
     * @param string $isbn
     * @return bool
     */
    private function validateIsbn10(string $isbn): bool
    {
        $check = 0;
        for ($i = 0; $i < 10; $i++) {
            $digit = ($isbn[$i] === 'X') ? 10 : (int)$isbn[$i];
            $check += $digit * (10 - $i);
        }

        $isValid = ($check % 11 === 0);

        if (!$isValid) {
            $this->error = 'Неверная контрольная сумма ISBN-10';
        }

        return $isValid;
    }

    /**
     * Validate ISBN-13 checksum
     *
     * @param string $isbn
     * @return bool
     */
    private function validateIsbn13(string $isbn): bool
    {
        $check = 0;
        for ($i = 0; $i < 13; $i++) {
            $check += (int)$isbn[$i] * (($i % 2 === 0) ? 1 : 3);
        }

        $isValid = ($check % 10 === 0);

        if (!$isValid) {
            $this->error = 'Неверная контрольная сумма ISBN-13';
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
