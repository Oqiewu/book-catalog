<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * Subscription model
 *
 * @property int $id
 * @property int $author_id
 * @property string|null $email
 * @property string|null $phone
 * @property int $created_at
 *
 * @property Author $author
 */
class Subscription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%subscriptions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['author_id'], 'required'],
            [['author_id'], 'integer'],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'match', 'pattern' => '/^[\d\+\-\(\)\s]+$/', 'message' => 'Неверный формат телефона'],
            [['email', 'phone'], 'trim'],
            // At least one of email or phone must be provided
            ['email', 'required', 'when' => function($model) {
                return empty($model->phone);
            }, 'message' => 'Необходимо указать email или телефон'],
            ['phone', 'required', 'when' => function($model) {
                return empty($model->email);
            }, 'message' => 'Необходимо указать email или телефон'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['author_id' => 'id']],
            // Unique combination of author_id and email
            [['email'], 'unique', 'targetAttribute' => ['author_id', 'email'], 'message' => 'Вы уже подписаны на этого автора'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'author_id' => 'Автор',
            'email' => 'Email',
            'phone' => 'Телефон',
            'created_at' => 'Дата подписки',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return ActiveQuery
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
