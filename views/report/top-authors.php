<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $topAuthors */
/** @var int $year */
/** @var array $availableYears */

$this->title = 'ТОП-10 авторов за ' . $year . ' год';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-top-authors">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="mb-3">
        <label for="year-select" class="form-label">Выберите год:</label>
        <select id="year-select" class="form-select" style="width: 200px;" onchange="window.location.href = this.value">
            <?php foreach ($availableYears as $availableYear): ?>
                <option value="<?= Url::to(['top-authors', 'year' => $availableYear]) ?>"
                    <?= $availableYear == $year ? 'selected' : '' ?>>
                    <?= $availableYear ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (empty($topAuthors)): ?>
        <div class="alert alert-info">
            За <?= $year ?> год нет данных о выпущенных книгах.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Автор</th>
                        <th width="150">Количество книг</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topAuthors as $index => $author): ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td>
                                <?= Html::a(
                                    Html::encode($author['full_name']),
                                    ['author/view', 'id' => $author['id']]
                                ) ?>
                            </td>
                            <td class="text-center">
                                <strong><?= $author['books_count'] ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>
