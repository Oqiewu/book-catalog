<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * RBAC initialization and management controller
 */
class RbacController extends Controller
{
    /**
     * Initializes RBAC roles and permissions
     * @return int Exit code
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Remove old data
        $auth->removeAll();

        $this->stdout("Initializing RBAC...\n");

        // Create permissions
        $createBook = $auth->createPermission('createBook');
        $createBook->description = 'Create a book';
        $auth->add($createBook);

        $updateBook = $auth->createPermission('updateBook');
        $updateBook->description = 'Update a book';
        $auth->add($updateBook);

        $deleteBook = $auth->createPermission('deleteBook');
        $deleteBook->description = 'Delete a book';
        $auth->add($deleteBook);

        $viewBook = $auth->createPermission('viewBook');
        $viewBook->description = 'View a book';
        $auth->add($viewBook);

        $createAuthor = $auth->createPermission('createAuthor');
        $createAuthor->description = 'Create an author';
        $auth->add($createAuthor);

        $updateAuthor = $auth->createPermission('updateAuthor');
        $updateAuthor->description = 'Update an author';
        $auth->add($updateAuthor);

        $deleteAuthor = $auth->createPermission('deleteAuthor');
        $deleteAuthor->description = 'Delete an author';
        $auth->add($deleteAuthor);

        $viewAuthor = $auth->createPermission('viewAuthor');
        $viewAuthor->description = 'View an author';
        $auth->add($viewAuthor);

        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users';
        $auth->add($manageUsers);

        $this->stdout("Permissions created.\n");

        // Create roles
        $guest = $auth->createRole('guest');
        $guest->description = 'Guest user';
        $auth->add($guest);
        $auth->addChild($guest, $viewBook);
        $auth->addChild($guest, $viewAuthor);

        $user = $auth->createRole('user');
        $user->description = 'Authenticated user';
        $auth->add($user);
        $auth->addChild($user, $guest);

        $editor = $auth->createRole('editor');
        $editor->description = 'Editor - can create and edit content';
        $auth->add($editor);
        $auth->addChild($editor, $user);
        $auth->addChild($editor, $createBook);
        $auth->addChild($editor, $updateBook);
        $auth->addChild($editor, $createAuthor);
        $auth->addChild($editor, $updateAuthor);

        $admin = $auth->createRole('admin');
        $admin->description = 'Administrator - full access';
        $auth->add($admin);
        $auth->addChild($admin, $editor);
        $auth->addChild($admin, $deleteBook);
        $auth->addChild($admin, $deleteAuthor);
        $auth->addChild($admin, $manageUsers);

        $this->stdout("Roles created.\n");
        $this->stdout("RBAC initialization completed successfully!\n\n");
        $this->stdout("Created roles:\n");
        $this->stdout("  - guest: Can view books and authors\n");
        $this->stdout("  - user: Authenticated user (inherits guest permissions)\n");
        $this->stdout("  - editor: Can create and edit books and authors\n");
        $this->stdout("  - admin: Full access to all resources\n");

        return ExitCode::OK;
    }

    /**
     * Assigns a role to a user
     * @param string $role Role name
     * @param int $userId User ID
     * @return int Exit code
     */
    public function actionAssign($role, $userId)
    {
        $auth = Yii::$app->authManager;

        $roleObject = $auth->getRole($role);
        if (!$roleObject) {
            $this->stderr("Error: Role '{$role}' not found.\n");
            return ExitCode::DATAERR;
        }

        try {
            $auth->assign($roleObject, $userId);
            $this->stdout("Role '{$role}' assigned to user {$userId} successfully.\n");
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Error: {$e->getMessage()}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Revokes a role from a user
     * @param string $role Role name
     * @param int $userId User ID
     * @return int Exit code
     */
    public function actionRevoke($role, $userId)
    {
        $auth = Yii::$app->authManager;

        $roleObject = $auth->getRole($role);
        if (!$roleObject) {
            $this->stderr("Error: Role '{$role}' not found.\n");
            return ExitCode::DATAERR;
        }

        try {
            $auth->revoke($roleObject, $userId);
            $this->stdout("Role '{$role}' revoked from user {$userId} successfully.\n");
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("Error: {$e->getMessage()}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Lists all available roles
     * @return int Exit code
     */
    public function actionRoles()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        $this->stdout("Available roles:\n");
        foreach ($roles as $role) {
            $this->stdout("  - {$role->name}: {$role->description}\n");
        }

        return ExitCode::OK;
    }

    /**
     * Lists all available permissions
     * @return int Exit code
     */
    public function actionPermissions()
    {
        $auth = Yii::$app->authManager;
        $permissions = $auth->getPermissions();

        $this->stdout("Available permissions:\n");
        foreach ($permissions as $permission) {
            $this->stdout("  - {$permission->name}: {$permission->description}\n");
        }

        return ExitCode::OK;
    }
}
