<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\SubscriptionServiceInterface;
use app\models\Author;
use app\models\Subscription;

/**
 * Service for managing subscriptions
 * Single Responsibility: handles only subscription management
 */
class SubscriptionService implements SubscriptionServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function subscribe(int $authorId, ?string $email = null, ?string $phone = null): ?Subscription
    {
        if (!$this->validateInput($email, $phone)) {
            return null;
        }

        if (!$this->authorExists($authorId)) {
            return null;
        }

        $existingSubscription = $this->findExistingSubscription($authorId, $email, $phone);
        if ($existingSubscription) {
            return $existingSubscription;
        }

        return $this->createSubscription($authorId, $email, $phone);
    }

    /**
     * Validate input parameters
     *
     * @param string|null $email
     * @param string|null $phone
     * @return bool
     */
    private function validateInput(?string $email, ?string $phone): bool
    {
        return !empty($email) || !empty($phone);
    }

    /**
     * Check if author exists
     *
     * @param int $authorId
     * @return bool
     */
    private function authorExists(int $authorId): bool
    {
        return Author::findOne($authorId) !== null;
    }

    /**
     * Find existing subscription
     *
     * @param int $authorId
     * @param string|null $email
     * @param string|null $phone
     * @return Subscription|null
     */
    private function findExistingSubscription(int $authorId, ?string $email, ?string $phone): ?Subscription
    {
        $query = Subscription::find()->where(['author_id' => $authorId]);

        if ($email) {
            $query->andWhere(['email' => $email]);
        } elseif ($phone) {
            $query->andWhere(['phone' => $phone]);
        }

        return $query->one();
    }

    /**
     * Create new subscription
     *
     * @param int $authorId
     * @param string|null $email
     * @param string|null $phone
     * @return Subscription|null
     */
    private function createSubscription(int $authorId, ?string $email, ?string $phone): ?Subscription
    {
        $subscription = new Subscription();
        $subscription->author_id = $authorId;
        $subscription->email = $email;
        $subscription->phone = $phone;

        if ($subscription->save()) {
            return $subscription;
        }

        return null;
    }
}
