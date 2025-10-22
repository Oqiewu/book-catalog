<?php

declare(strict_types=1);

namespace app\interfaces;

/**
 * Interface for notification services (SMS, Email, Push, etc.)
 */
interface NotificationServiceInterface
{
    /**
     * Send notification
     *
     * @param string $recipient Recipient identifier (phone, email, etc.)
     * @param string $message Message text
     * @return bool
     */
    public function send(string $recipient, string $message): bool;
}
