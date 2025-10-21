<?php

namespace app\controllers;

use app\models\Book;
use app\models\Author;
use app\services\BookService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * BookController implements the CRUD actions for Book model.
 */
class BookController extends Controller
{
    private BookService $bookService;

    public function __construct($id, $module, BookService $bookService = null, $config = [])
    {
        $this->bookService = $bookService ?? new BookService();
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'], // Only authenticated users
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
     * Lists all Book models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Book::find()->with('authors')->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Book model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Book model.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Book();
        $authors = Author::find()->orderBy(['last_name' => SORT_ASC])->all();

        if ($model->load(Yii::$app->request->post())) {
            $authorIds = Yii::$app->request->post('author_ids', []);

            // Validate that at least one author is selected
            if (empty($authorIds)) {
                $model->addError('authors', 'Необходимо выбрать хотя бы одного автора.');
                return $this->render('create', [
                    'model' => $model,
                    'authors' => $authors,
                ]);
            }

            if ($this->bookService->save($model, $authorIds)) {
                Yii::$app->session->setFlash('success', 'Книга успешно добавлена.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при сохранении книги.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'authors' => $authors,
        ]);
    }

    /**
     * Updates an existing Book model.
     *
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $authors = Author::find()->orderBy(['last_name' => SORT_ASC])->all();

        if ($model->load(Yii::$app->request->post())) {
            $authorIds = Yii::$app->request->post('author_ids', []);

            // Validate that at least one author is selected
            if (empty($authorIds)) {
                $model->addError('authors', 'Необходимо выбрать хотя бы одного автора.');
                return $this->render('update', [
                    'model' => $model,
                    'authors' => $authors,
                ]);
            }

            if ($this->bookService->save($model, $authorIds)) {
                Yii::$app->session->setFlash('success', 'Книга успешно обновлена.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при обновлении книги.');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'authors' => $authors,
        ]);
    }

    /**
     * Deletes an existing Book model.
     *
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($this->bookService->delete($model)) {
            Yii::$app->session->setFlash('success', 'Книга успешно удалена.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении книги.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Book model based on its primary key value.
     *
     * @param int $id ID
     * @return Book the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Book::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
}
