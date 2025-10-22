<?php

namespace tests\unit\models;

use app\models\LoginForm;
use app\models\User;
use Codeception\Test\Unit;
use Yii;

class LoginFormTest extends Unit
{
    private $model;

    public function testLoginNoUser()
    {
        $this->model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        verify($this->model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword()
    {
        $this->model = new LoginForm([
            'username' => 'demo',
            'password' => 'wrong_password',
        ]);

        verify($this->model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
        verify($this->model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect()
    {
        $this->model = $this->getMockBuilder(LoginForm::class)
            ->onlyMethods(['getUser'])
            ->setConstructorArgs([['username' => 'demo', 'password' => 'demo']])
            ->getMock();

        $userMock = $this->createMock(User::class);
        $userMock->method('validatePassword')->willReturn(true);

        $this->model->method('getUser')->willReturn($userMock);

        verify($this->model->login())->true();
    }

    protected function _after()
    {
        Yii::$app->user->logout();
    }

}
