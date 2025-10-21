<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\base\InvalidConfigException;

/**
 * Author model
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Book[] $books
 * @property Subscription[] $subscriptions
 */
class Author extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%authors}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['first_name', 'last_name'], 'required'],
            [['first_name', 'last_name', 'middle_name'], 'string', 'max' => 100],
            [['first_name', 'last_name', 'middle_name'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Gets query for [[Books]].
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getBooks(): ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return ActiveQuery
     */
    public function getSubscriptions(): ActiveQuery
    {
        return $this->hasMany(Subscription::class, ['author_id' => 'id']);
    }

    /**
     * Returns full name of the author.
     *
     * @return string
     */
    public function getFullName(): string
    {
        $parts = array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get number of books published by author in a specific year.
     *
     * @param int $year
     * @return int
     * @throws InvalidConfigException
     */
    public function getBooksCountByYear(int $year): int
    {
        return $this->getBooks()
            ->where(['year' => $year])
            ->count();
    }
}
