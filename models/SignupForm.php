<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Exception;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_repeat;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'email', 'password', 'password_repeat'], 'required'],
            [['username', 'email'], 'trim'],

            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Имя пользователя может содержать только латинские буквы, цифры, дефис и подчеркивание.'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'Это имя пользователя уже занято.'],

            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже зарегистрирован.'],

            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'username' => 'Имя пользователя',
            'email' => 'Email',
            'password' => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     * @throws Exception
     */
    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        if ($user->save()) {
            return $user;
        }

        return null;
    }
}
