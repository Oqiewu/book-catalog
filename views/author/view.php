<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\Author $model */
/** @var yii\data\ActiveDataProvider $booksDataProvider */

$this->title = $model->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="author-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить этого автора?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('Подписаться на новые книги', ['subscription/subscribe', 'authorId' => $model->id], ['class' => 'btn btn-info']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'last_name',
            'first_name',
            'middle_name',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <h2>Книги автора</h2>

    <?= GridView::widget([
        'dataProvider' => $booksDataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'cover_image',
                'format' => 'html',
                'value' => function ($model) {
                    if ($model->cover_image) {
                        return Html::img($model->getCoverUrl(), ['width' => '50']);
                    }
                    return '<span class="text-muted">Нет обложки</span>';
                },
                'label' => 'Обложка',
            ],
            [
                'attribute' => 'title',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->title), ['book/view', 'id' => $model->id]);
                },
            ],
            'year',
            'isbn',

            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'book',
            ],
        ],
    ]); ?>

</div>
