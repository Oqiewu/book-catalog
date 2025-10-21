<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Author;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\db\Exception;

/**
 * AuthorController implements the CRUD actions for Author model.
 */
class AuthorController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'], // Guests and authenticated users
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['createAuthor'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => ['updateAuthor'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['deleteAuthor'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Author models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Author::find()->orderBy(['last_name' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Author model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        $booksDataProvider = new ActiveDataProvider([
            'query' => $model->getBooks()->orderBy(['year' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'booksDataProvider' => $booksDataProvider,
        ]);
    }

    /**
     * Creates a new Author model.
     *
     * @return string|Response
     * @throws Exception
     */
    public function actionCreate(): string|Response
    {
        $model = new Author();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор успешно добавлен.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Author model.
     *
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Exception
     */
    public function actionUpdate(int $id): string|Response
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор успешно обновлен.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Author model.
     *
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Автор успешно удален.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the Author model based on its primary key value.
     *
     * @param int $id ID
     * @return Author the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Author
    {
        if (($model = Author::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
}
