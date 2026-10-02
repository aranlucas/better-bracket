<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\GroupModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class GroupModelTest extends CIUnitTestCase
{
    public function testFailedReplacementPreservesPreviousPicks(): void
    {
        // Isolated SQLite database: no production connection, schema or seed data.
        $db = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => false,
        ], false);
        try {
            $db->query('CREATE TABLE picks (user_id INTEGER, group_id INTEGER, team_id INTEGER CHECK (team_id > 0), region INTEGER, round INTEGER, game INTEGER, team INTEGER)');
            $original = ['user_id' => 1, 'group_id' => 1, 'team_id' => 8, 'region' => 1, 'round' => 1, 'game' => 2, 'team' => 1];
            $db->table('picks')->insert($original);
            $model = new GroupModel($db);
            $valid = ['team_id' => 1, 'region' => 1, 'round' => 1, 'game' => 1, 'team' => 1];
            $invalid = ['team_id' => -1, 'region' => 1, 'round' => 1, 'game' => 3, 'team' => 1];

            self::assertFalse($model->savePicks(1, 1, [$valid, $invalid]));
            self::assertSame([$original], $db->table('picks')->get()->getResultArray());
        } finally {
            $db->close();
        }
    }
}
