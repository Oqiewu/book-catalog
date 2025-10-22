<?php

namespace tests\unit\models;

use app\models\User;
use Codeception\Test\Unit;
use Yii;

class UserTest extends Unit
{
    private ?User $adminUser = null;

    public function testFindUserById()
    {
        verify($user = User::findIdentity(100))->notEmpty();
        verify($user->username)->equals('admin');

        verify(User::findIdentity(999))->empty();
    }

    public function testFindUserByAccessToken()
    {
        verify($user = User::findIdentityByAccessToken('test100key'))->notEmpty();
        verify($user->username)->equals('admin');

        verify(User::findIdentityByAccessToken('non-existing'))->empty();
    }

    public function testFindUserByUsername()
    {
        verify($user = User::findByUsername('admin'))->notEmpty();
        verify(User::findByUsername('not-admin'))->empty();
    }

    /**
     * @depends testFindUserByUsername
     */
    public function testValidateUser()
    {
        $user = User::findByUsername('admin');

        verify($user->validateAuthKey('test100key'))->true();
        verify($user->validateAuthKey('test102key'))->false();

        verify($user->validatePassword('admin'))->true();
        verify($user->validatePassword('123456'))->false();
    }

    protected function _before()
    {
        $this->adminUser = User::findOne(100);
        if (!$this->adminUser) {
            $this->adminUser = new User([
                'id' => 100,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'auth_key' => 'test100key',
            ]);
            $this->adminUser->setPassword('admin');
            $this->adminUser->save(false);
        }
    }

    protected function _after()
    {
        Yii::$app->user->logout();
    }
}
