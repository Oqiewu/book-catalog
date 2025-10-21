<?php

namespace app\services;

use app\models\Subscription;
use app\models\Author;
use Yii;

/**
 * Service for managing subscriptions
 */
class SubscriptionService
{
    /**
     * Subscribe user to author's new books
     *
     * @param int $authorId
     * @param string|null $email
     * @param string|null $phone
     * @return Subscription|null
     */
    public function subscribe($authorId, $email = null, $phone = null)
    {
        if (empty($email) && empty($phone)) {
            return null;
        }

        // Check if author exists
        $author = Author::findOne($authorId);
        if (!$author) {
            return null;
        }

        // Check if subscription already exists
        $query = Subscription::find()->where(['author_id' => $authorId]);

        if ($email) {
            $query->andWhere(['email' => $email]);
        } elseif ($phone) {
            $query->andWhere(['phone' => $phone]);
        }

        $existingSubscription = $query->one();

        if ($existingSubscription) {
            return $existingSubscription;
        }

        // Create new subscription
        $subscription = new Subscription();
        $subscription->author_id = $authorId;
        $subscription->email = $email;
        $subscription->phone = $phone;

        if ($subscription->save()) {
            return $subscription;
        }

        return null;
    }

    /**
     * Get subscriptions by author
     *
     * @param int $authorId
     * @return Subscription[]
     */
    public function getByAuthor($authorId)
    {
        return Subscription::find()
            ->where(['author_id' => $authorId])
            ->all();
    }

    /**
     * Get subscriptions by phone
     *
     * @param string $phone
     * @return Subscription[]
     */
    public function getByPhone($phone)
    {
        return Subscription::find()
            ->where(['phone' => $phone])
            ->with('author')
            ->all();
    }

    /**
     * Get subscriptions by email
     *
     * @param string $email
     * @return Subscription[]
     */
    public function getByEmail($email)
    {
        return Subscription::find()
            ->where(['email' => $email])
            ->with('author')
            ->all();
    }
}
