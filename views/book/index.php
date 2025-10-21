<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Каталог книг';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Добавить книгу', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
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
            'isbn',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
