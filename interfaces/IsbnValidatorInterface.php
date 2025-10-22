<?php

declare(strict_types=1);

namespace app\interfaces;

/**
 * Interface for ISBN validation
 */
interface IsbnValidatorInterface
{
    /**
     * Validate ISBN format and checksum
     *
     * @param string $isbn
     * @return bool
     */
    public function validate(string $isbn): bool;

    /**
     * Get validation error message
     *
     * @return string|null
     */
    public function getError(): ?string;
}
