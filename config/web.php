<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'sfRnpNfRxkQO9zDJvCCHpAlQ3Nc55xnr',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        // Dependency Injection: Storage Service
        'storageService' => [
            'class' => 'app\services\StorageService',
        ],
        // Dependency Injection: Notification Service
        'notificationService' => [
            'class' => 'app\services\SmsService',
        ],
        // Dependency Injection: Notification Dispatcher
        'notificationDispatcher' => function() {
            return new \app\services\BookNotificationDispatcher(
                \Yii::$app->get('notificationService')
            );
        },
        // Dependency Injection: Book Service
        'bookService' => function() {
            return new \app\services\BookService(
                \Yii::$app->get('storageService'),
                \Yii::$app->get('notificationDispatcher')
            );
        },
        // Dependency Injection: Subscription Service
        'subscriptionService' => [
            'class' => 'app\services\SubscriptionService',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'container' => [
        'singletons' => [
            // Interface bindings for dependency injection
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

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
