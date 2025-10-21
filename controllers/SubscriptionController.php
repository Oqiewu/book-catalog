<?php

namespace app\controllers;

use app\models\Subscription;
use app\models\Author;
use app\services\SubscriptionService;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

/**
 * SubscriptionController handles author subscriptions.
 */
class SubscriptionController extends Controller
{
    private SubscriptionService $subscriptionService;

    public function __construct($id, $module, SubscriptionService $subscriptionService = null, $config = [])
    {
        $this->subscriptionService = $subscriptionService ?? new SubscriptionService();
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Subscribe to author's new books
     *
     * @return Response
     */
    public function actionCreate()
    {
        $authorId = Yii::$app->request->post('author_id');
        $email = Yii::$app->request->post('email');
        $phone = Yii::$app->request->post('phone');

        if (!$authorId) {
            Yii::$app->session->setFlash('error', 'Не указан автор для подписки.');
            return $this->redirect(Yii::$app->request->referrer ?: ['book/index']);
        }

        $author = Author::findOne($authorId);
        if (!$author) {
            Yii::$app->session->setFlash('error', 'Автор не найден.');
            return $this->redirect(Yii::$app->request->referrer ?: ['book/index']);
        }

        if (empty($email) && empty($phone)) {
            Yii::$app->session->setFlash('error', 'Необходимо указать email или телефон.');
            return $this->redirect(Yii::$app->request->referrer ?: ['author/view', 'id' => $authorId]);
        }

        $subscription = $this->subscriptionService->subscribe($authorId, $email, $phone);

        if ($subscription) {
            Yii::$app->session->setFlash('success',
                sprintf('Вы успешно подписались на новые книги автора %s', $author->getFullName())
            );
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при создании подписки. Возможно, вы уже подписаны.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['author/view', 'id' => $authorId]);
    }

    /**
     * Display subscription form
     *
     * @param int $authorId
     * @return string
     */
    public function actionSubscribe($authorId)
    {
        $author = Author::findOne($authorId);

        if (!$author) {
            Yii::$app->session->setFlash('error', 'Автор не найден.');
            return $this->redirect(['book/index']);
        }

        $model = new Subscription();
        $model->author_id = $authorId;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $subscription = $this->subscriptionService->subscribe(
                $model->author_id,
                $model->email,
                $model->phone
            );

            if ($subscription) {
                Yii::$app->session->setFlash('success',
                    sprintf('Вы успешно подписались на новые книги автора %s', $author->getFullName())
                );
                return $this->redirect(['author/view', 'id' => $authorId]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при создании подписки.');
            }
        }

        return $this->render('subscribe', [
            'model' => $model,
            'author' => $author,
        ]);
    }
}
