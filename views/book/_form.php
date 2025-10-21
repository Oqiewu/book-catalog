<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Author[] $authors */

$selectedAuthors = $model->isNewRecord ? [] : $model->getAuthorIds();
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->textInput(['type' => 'number', 'min' => 1000, 'max' => 9999]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <?php if ($model->cover_image): ?>
        <div class="form-group">
            <label>Текущая обложка</label><br>
            <?= Html::img($model->getCoverUrl(), ['width' => '200']) ?>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label>Авторы</label>
        <?= Html::checkboxList(
            'author_ids',
            $selectedAuthors,
            ArrayHelper::map($authors, 'id', function($author) {
                return $author->getFullName();
            }),
            ['class' => 'form-check']
        ) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
