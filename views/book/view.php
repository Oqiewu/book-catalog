<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Book $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Каталог книг', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить эту книгу?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </p>

    <div class="row">
        <div class="col-md-3">
            <?php if ($model->cover_image): ?>
                <?= Html::img($model->getCoverUrl(), ['class' => 'img-fluid', 'alt' => $model->title]) ?>
            <?php else: ?>
                <div class="bg-light p-5 text-center text-muted">
                    Нет обложки
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-9">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'title',
                    'year',
                    [
                        'attribute' => 'authors',
                        'format' => 'html',
                        'value' => function ($model) {
                            $authors = array_map(function ($author) {
                                return Html::a(Html::encode($author->getFullName()), ['author/view', 'id' => $author->id]);
                            }, $model->authors);
                            return implode(', ', $authors);
                        },
                        'label' => 'Авторы',
                    ],
                    'description:ntext',
                    'isbn',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>

</div>
