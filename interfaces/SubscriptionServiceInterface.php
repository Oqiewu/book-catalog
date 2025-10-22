<?php

declare(strict_types=1);

namespace app\interfaces;

use app\models\Subscription;

/**
 * Interface for subscription management services
 */
interface SubscriptionServiceInterface
{
    /**
     * Subscribe user to author's new books
     *
     * @param int $authorId
     * @param string|null $email
     * @param string|null $phone
     * @return Subscription|null
     */
    public function subscribe(int $authorId, ?string $email = null, ?string $phone = null): ?Subscription;
}
