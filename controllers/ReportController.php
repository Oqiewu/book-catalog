<?php

namespace app\controllers;

use app\models\Author;
use app\models\Book;
use Yii;
use yii\web\Controller;
use yii\db\Query;

/**
 * ReportController handles statistical reports.
 */
class ReportController extends Controller
{
    /**
     * TOP 10 authors by books count in a specific year
     *
     * @param int|null $year
     * @return string
     */
    public function actionTopAuthors($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        // Validate year
        $year = (int) $year;
        if ($year < 1000 || $year > 9999) {
            $year = date('Y');
        }

        // Get TOP 10 authors by books count in specific year
        $topAuthors = $this->getTopAuthorsByYear($year);

        // Get available years for filter
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
    protected function getTopAuthorsByYear($year)
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

        // Transform results to include full name
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
