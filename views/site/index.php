<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Каталог книг';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Добро пожаловать в каталог книг!</h1>

        <p class="lead">Просматривайте книги, узнавайте об авторах и подписывайтесь на уведомления о новинках.</p>

        <p>
            <?= Html::a('Перейти к книгам', ['book/index'], ['class' => 'btn btn-lg btn-success']) ?>
        </p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4 mb-3">
                <h2>Каталог книг</h2>

                <p>Просматривайте полный каталог книг с информацией о названии, годе выпуска, авторах и описании.
                <?php if (!Yii::$app->user->isGuest): ?>
                Авторизованные пользователи могут добавлять, редактировать и удалять книги.
                <?php endif; ?>
                </p>

                <p><?= Html::a('Смотреть книги &raquo;', ['book/index'], ['class' => 'btn btn-outline-secondary']) ?></p>
            </div>
            <div class="col-lg-4 mb-3">
                <h2>Авторы</h2>

                <p>Узнайте больше об авторах книг. Просматривайте их работы и подписывайтесь на уведомления
                о новых книгах избранных авторов. При появлении новой книги вы получите SMS-уведомление.</p>

                <p><?= Html::a('Список авторов &raquo;', ['author/index'], ['class' => 'btn btn-outline-secondary']) ?></p>
            </div>
            <div class="col-lg-4">
                <h2>ТОП-10 авторов</h2>

                <p>Смотрите рейтинг самых продуктивных авторов за выбранный год.
                Узнайте, кто из авторов выпустил больше всего книг в интересующий вас период.</p>

                <p><?= Html::a('Посмотреть рейтинг &raquo;', ['report/top-authors'], ['class' => 'btn btn-outline-secondary']) ?></p>
            </div>
        </div>

    </div>
</div>
