<?php

declare(strict_types=1);

namespace app\services;

use app\models\Subscription;
use app\models\Author;
use Yii;
use yii\db\Exception;

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
     * @throws Exception
     */
    public function subscribe(int $authorId, ?string $email = null, ?string $phone = null): ?Subscription
    {
        if (empty($email) && empty($phone)) {
            return null;
        }

        $author = Author::findOne($authorId);
        if (!$author) {
            return null;
        }

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
