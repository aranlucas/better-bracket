<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class UserModelTest extends CIUnitTestCase
{
    public function testLegacyLoginUpgradesPasswordAndRejectsIncorrectCredentials(): void
    {
        $db = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => false,
        ], false);
        try {
            $db->query('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, password TEXT)');
            $legacy = hash('sha256', 'member@example.com' . 'secret-password');
            $db->table('users')->insert(['id' => 1, 'email' => 'member@example.com', 'password' => $legacy]);
            $model = new UserModel($db);

            self::assertNull($model->authenticate('member@example.com', 'incorrect'));
            self::assertSame($legacy, $model->find(1)['password']);
            self::assertSame(['id' => 1, 'email' => 'member@example.com'], $model->authenticate(' MEMBER@example.com ', 'secret-password'));
            $upgraded = $model->find(1)['password'];
            self::assertTrue(password_verify('secret-password', $upgraded));
            self::assertFalse(password_needs_rehash($upgraded, PASSWORD_DEFAULT));

            self::assertNull($model->authenticate('member@example.com', 'incorrect'));
            self::assertNotNull($model->authenticate('member@example.com', 'secret-password'));
            self::assertSame($upgraded, $model->find(1)['password']);
        } finally {
            $db->close();
        }
    }
}
