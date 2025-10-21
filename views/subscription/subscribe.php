<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Subscription $model */
/** @var app\models\Author $author */

$this->title = 'Подписка на новые книги автора: ' . $author->getFullName();
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['author/index']];
$this->params['breadcrumbs'][] = ['label' => $author->getFullName(), 'url' => ['author/view', 'id' => $author->id]];
$this->params['breadcrumbs'][] = 'Подписка';
?>
<div class="subscription-subscribe">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <p><strong>Вы подписываетесь на уведомления о новых книгах автора <?= Html::encode($author->getFullName()) ?>.</strong></p>
        <p>Укажите ваш email или номер телефона. При поступлении новой книги этого автора вам придет SMS-уведомление (если указан телефон).</p>
    </div>

    <div class="subscription-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => '+79001234567']) ?>

        <div class="form-group">
            <?= Html::submitButton('Подписаться', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Отмена', ['author/view', 'id' => $author->id], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
