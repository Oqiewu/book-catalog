<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'language' => 'en-US',
    'components' => [
        'db' => $db,
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
            'messageClass' => 'yii\symfonymailer\Message'
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
        ],
        'storageService' => [
            'class' => 'app\services\StorageService',
        ],
        'notificationService' => [
            'class' => 'app\services\SmsService',
        ],
        'notificationDispatcher' => function() {
            return new \app\services\BookNotificationDispatcher(
                \Yii::$app->get('notificationService')
            );
        },
        'bookService' => function() {
            return new \app\services\BookService(
                \Yii::$app->get('storageService'),
                \Yii::$app->get('notificationDispatcher')
            );
        },
        'subscriptionService' => [
            'class' => 'app\services\SubscriptionService',
        ],
    ],
    'container' => [
        'singletons' => [
            'app\interfaces\StorageServiceInterface' => 'app\services\StorageService',
            'app\interfaces\NotificationServiceInterface' => 'app\services\SmsService',
            'app\interfaces\BookServiceInterface' => function() {
                return \Yii::$app->get('bookService');
            },
            'app\interfaces\SubscriptionServiceInterface' => 'app\services\SubscriptionService',
            'app\interfaces\NotificationDispatcherInterface' => function() {
                return \Yii::$app->get('notificationDispatcher');
            },
        ],
    ],
    'params' => $params,
];
