<?php

namespace app\models;

use app\validators\IsbnValidator;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\web\UploadedFile;

/**
 * Book model
 *
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string|null $description
 * @property string|null $isbn
 * @property string|null $cover_image
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Author[] $authors
 * @property UploadedFile|null $imageFile
 */
class Book extends ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%books}}';
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
            [['title', 'year'], 'required'],
            [['year'], 'integer', 'min' => 1000, 'max' => 9999],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['isbn'], 'string', 'max' => 20],
            [['isbn'], 'unique'],
            [['isbn'], 'validateIsbn'],
            [['cover_image'], 'string', 'max' => 255],
            [['title', 'description', 'isbn'], 'trim'],
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg, gif',
                'maxSize' => 1024 * 1024 * 2
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'cover_image' => 'Обложка',
            'imageFile' => 'Изображение обложки',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Get authors IDs for form.
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getAuthorIds(): array
    {
        return $this->getAuthors()->select('id')->column();
    }

    /**
     * Gets query for [[Authors]].
     *
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getAuthors(): ActiveQuery
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id']);
    }

    /**
     * Link authors to the book.
     *
     * @param array $authorIds
     * @return void
     * @throws Exception
     */
    public function linkAuthors(array $authorIds): void
    {
        BookAuthor::deleteAll(['book_id' => $this->id]);

        foreach ($authorIds as $authorId) {
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $this->id;
            $bookAuthor->author_id = $authorId;
            $bookAuthor->save(false);
        }
    }

    /**
     * Get cover image URL from storage service.
     *
     * @return string|null
     */
    public function getCoverUrl(): ?string
    {
        if ($this->cover_image) {
            $storageService = Yii::$app->get('storageService');
            return $storageService->getFileUrl($this->cover_image);
        }
        return null;
    }

    /**
     * Validates ISBN with checksum verification using dedicated validator
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateIsbn($attribute, $params)
    {
        $validator = new IsbnValidator();

        if (!$validator->validate($this->$attribute)) {
            $this->addError($attribute, $validator->getError());
        }
    }
}
