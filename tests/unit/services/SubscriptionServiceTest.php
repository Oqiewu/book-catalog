<?php

namespace tests\unit\services;

use app\models\Author;
use app\models\Subscription;
use app\services\SubscriptionService;
use Codeception\Test\Unit;
use UnitTester;

/**
 * Unit test for SubscriptionService
 */
class SubscriptionServiceTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testSubscribeWithEmail()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Subscribe',
            'last_name' => 'Test',
        ]);
        $author->save();

        $service = new SubscriptionService();
        $result = $service->subscribe($author->id, 'test@example.com', null);

        $this->assertTrue($result, 'Subscription should be created successfully');

        // Check subscription exists
        $subscription = Subscription::findOne([
            'author_id' => $author->id,
            'email' => 'test@example.com',
        ]);

        $this->assertNotNull($subscription, 'Subscription should exist in database');

        // Cleanup
        $subscription->delete();
        $author->delete();
    }

    public function testSubscribeWithPhone()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Phone',
            'last_name' => 'Test',
        ]);
        $author->save();

        $service = new SubscriptionService();
        $result = $service->subscribe($author->id, null, '+79991234567');

        $this->assertTrue($result, 'Subscription with phone should be created successfully');

        // Check subscription exists
        $subscription = Subscription::findOne([
            'author_id' => $author->id,
            'phone' => '+79991234567',
        ]);

        $this->assertNotNull($subscription, 'Phone subscription should exist in database');

        // Cleanup
        $subscription->delete();
        $author->delete();
    }

    public function testPreventDuplicateSubscription()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Duplicate',
            'last_name' => 'Test',
        ]);
        $author->save();

        $service = new SubscriptionService();

        // First subscription
        $result1 = $service->subscribe($author->id, 'duplicate@example.com', null);
        $this->assertTrue($result1, 'First subscription should succeed');

        // Try to subscribe again with same email
        $result2 = $service->subscribe($author->id, 'duplicate@example.com', null);
        $this->assertFalse($result2, 'Duplicate subscription should fail');

        // Count subscriptions
        $count = Subscription::find()
            ->where(['author_id' => $author->id, 'email' => 'duplicate@example.com'])
            ->count();

        $this->assertEquals(1, $count, 'Should have only one subscription');

        // Cleanup
        Subscription::deleteAll(['author_id' => $author->id]);
        $author->delete();
    }

    public function testGetSubscriptionsByAuthor()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Multi',
            'last_name' => 'Subscriber',
        ]);
        $author->save();

        $service = new SubscriptionService();

        // Create multiple subscriptions
        $service->subscribe($author->id, 'user1@example.com', null);
        $service->subscribe($author->id, 'user2@example.com', null);
        $service->subscribe($author->id, null, '+79991111111');

        $subscriptions = $service->getSubscriptionsByAuthor($author->id);

        $this->assertEquals(3, count($subscriptions), 'Should have 3 subscriptions');

        // Cleanup
        Subscription::deleteAll(['author_id' => $author->id]);
        $author->delete();
    }

    public function testSubscribeWithoutEmailAndPhone()
    {
        // Create test author
        $author = new Author([
            'first_name' => 'Invalid',
            'last_name' => 'Subscription',
        ]);
        $author->save();

        $service = new SubscriptionService();
        $result = $service->subscribe($author->id, null, null);

        $this->assertFalse($result, 'Subscription without email and phone should fail');

        // Cleanup
        $author->delete();
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
