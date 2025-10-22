<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Book;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\db\Query;

/**
 * ReportController handles statistical reports.
 */
class ReportController extends Controller
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
                        'actions' => ['top-authors'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * TOP 10 authors by books count in a specific year
     *
     * @param int|null $year
     * @return string
     */
    public function actionTopAuthors(?int $year = null): string
    {
        if ($year === null) {
            $year = date('Y');
        }

        $year = (int) $year;
        if ($year < 1000 || $year > 9999) {
            $year = date('Y');
        }

        $topAuthors = $this->getTopAuthorsByYear($year);

        $availableYears = Book::find()
            ->select('year')
            ->distinct()
            ->orderBy(['year' => SORT_DESC])
            ->column();

        return $this->render('top-authors', [
            'topAuthors' => $topAuthors,
            'year' => $year,
            'availableYears' => $availableYears,
        ]);
    }

    /**
     * Get TOP 10 authors by books count in specific year
     *
     * @param int $year
     * @return array
     */
    protected function getTopAuthorsByYear(int $year): array
    {
        $query = new Query();
        $query->select([
            'a.id',
            'a.first_name',
            'a.last_name',
            'a.middle_name',
            'COUNT(b.id) as books_count'
        ])
        ->from('{{%authors}} a')
        ->innerJoin('{{%book_author}} ba', 'ba.author_id = a.id')
        ->innerJoin('{{%books}} b', 'b.id = ba.book_id AND b.year = :year', ['year' => $year])
        ->groupBy(['a.id', 'a.first_name', 'a.last_name', 'a.middle_name'])
        ->orderBy(['books_count' => SORT_DESC])
        ->limit(10);

        $results = $query->all();

        return array_map(function($row) {
            $parts = array_filter([
                $row['last_name'],
                $row['first_name'],
                $row['middle_name'],
            ]);

            return [
                'id' => $row['id'],
                'full_name' => implode(' ', $parts),
                'books_count' => $row['books_count'],
            ];
        }, $results);
    }
}
