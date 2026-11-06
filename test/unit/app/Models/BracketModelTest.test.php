<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\BracketModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class BracketModelTest extends CIUnitTestCase
{
    public function testFailedReplacementPreservesPreviousPicks(): void
    {
        $db = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => false,
        ], false);
        try {
            $db->query('CREATE TABLE user_groups (group_id INTEGER, user_id INTEGER)');
            $db->table('user_groups')->insert(['group_id' => 1, 'user_id' => 1]);
            $db->query('CREATE TABLE picks (user_id INTEGER, group_id INTEGER, team_id INTEGER CHECK (team_id > 0), region INTEGER, round INTEGER, game INTEGER, team INTEGER)');
            $original = ['user_id' => 1, 'group_id' => 1, 'team_id' => 8, 'region' => 1, 'round' => 1, 'game' => 2, 'team' => 1];
            $db->table('picks')->insert($original);
            $model = new BracketModel($db);
            $valid = ['team_id' => 1, 'region' => 1, 'round' => 1, 'game' => 1, 'team' => 1];
            $invalid = ['team_id' => -1, 'region' => 1, 'round' => 1, 'game' => 3, 'team' => 1];

            self::assertFalse($model->savePicks(1, 1, [$valid, $invalid]));
            self::assertSame([$original], $db->table('picks')->get()->getResultArray());
        } finally {
            $db->close();
        }
    }
}
