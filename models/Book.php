<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
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
    public static function tableName()
    {
        return '{{%books}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
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
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 1024 * 1024 * 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
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
     * Gets query for [[Authors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id']);
    }

    /**
     * Get authors IDs for form.
     *
     * @return array
     */
    public function getAuthorIds()
    {
        return $this->getAuthors()->select('id')->column();
    }

    /**
     * Link authors to the book.
     *
     * @param array $authorIds
     * @return void
     */
    public function linkAuthors(array $authorIds)
    {
        // Remove old links
        BookAuthor::deleteAll(['book_id' => $this->id]);

        // Add new links
        foreach ($authorIds as $authorId) {
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $this->id;
            $bookAuthor->author_id = $authorId;
            $bookAuthor->save(false);
        }
    }

    /**
     * Get cover image URL from MinIO.
     *
     * @return string|null
     */
    public function getCoverUrl()
    {
        if ($this->cover_image) {
            $storageService = new \app\services\StorageService();
            return $storageService->getFileUrl($this->cover_image);
        }
        return null;
    }

    /**
     * Validates ISBN with checksum verification
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateIsbn($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return; // ISBN необязателен
        }

        // Remove dashes and spaces
        $isbn = str_replace(['-', ' '], '', $this->$attribute);

        // Check if contains only allowed characters
        if (!preg_match('/^[\dX]+$/', $isbn)) {
            $this->addError($attribute, 'ISBN может содержать только цифры, дефисы и символ X');
            return;
        }

        if (strlen($isbn) == 10) {
            // ISBN-10 validation
            if (!$this->validateIsbn10($isbn)) {
                $this->addError($attribute, 'Неверная контрольная сумма ISBN-10');
            }
        } elseif (strlen($isbn) == 13) {
            // ISBN-13 validation
            if (!$this->validateIsbn13($isbn)) {
                $this->addError($attribute, 'Неверная контрольная сумма ISBN-13');
            }
        } else {
            $this->addError($attribute, 'ISBN должен содержать 10 или 13 символов (без учета дефисов)');
        }
    }

    /**
     * Validates ISBN-10 checksum
     *
     * @param string $isbn
     * @return bool
     */
    protected function validateIsbn10($isbn)
    {
        $check = 0;
        for ($i = 0; $i < 10; $i++) {
            $digit = ($isbn[$i] === 'X') ? 10 : (int)$isbn[$i];
            $check += $digit * (10 - $i);
        }
        return ($check % 11 == 0);
    }

    /**
     * Validates ISBN-13 checksum
     *
     * @param string $isbn
     * @return bool
     */
    protected function validateIsbn13($isbn)
    {
        $check = 0;
        for ($i = 0; $i < 13; $i++) {
            $check += (int)$isbn[$i] * (($i % 2 == 0) ? 1 : 3);
        }
        return ($check % 10 == 0);
    }
}
