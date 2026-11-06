<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

final class BracketConcurrencyTest extends CIUnitTestCase
{
    public function testReplacementWaitsForMembershipLockAndReplacesConcurrentPicks(): void
    {
        if (! getenv('PGHOST')) {
            self::markTestSkipped('Requires the disposable PostgreSQL CI service.');
        }

        $db = pg_connect('host=' . getenv('PGHOST') . ' dbname=' . getenv('PGDATABASE') . ' user=' . getenv('PGUSER') . ' password=' . getenv('PGPASSWORD'), PGSQL_CONNECT_FORCE_NEW);
        self::assertNotFalse($db);
        $schema = 'audit_' . bin2hex(random_bytes(8));
        $process = null;
        $pipes = [];
        try {
            pg_query($db, 'CREATE SCHEMA ' . $schema);
            pg_query($db, 'SET search_path TO ' . $schema);
            pg_query($db, 'CREATE TABLE user_groups (group_id INTEGER, user_id INTEGER, PRIMARY KEY(group_id,user_id))');
            pg_query($db, 'CREATE TABLE picks (user_id INTEGER, group_id INTEGER, team_id INTEGER, region INTEGER, round INTEGER, game INTEGER, team INTEGER)');
            pg_query($db, 'INSERT INTO user_groups VALUES (1,1)');
            pg_query($db, 'BEGIN');
            pg_query($db, 'SELECT * FROM user_groups WHERE group_id=1 AND user_id=1 FOR UPDATE');
            $config = [
                'DBDriver' => 'Postgre', 'hostname' => getenv('PGHOST'),
                'database' => getenv('PGDATABASE'), 'username' => getenv('PGUSER'),
                'password' => getenv('PGPASSWORD'), 'schema' => $schema, 'DBPrefix' => '',
                'charset' => 'utf8', 'DBDebug' => true,
            ];
            $source = 'require "test/bootstrap.php"; $db = \\Config\\Database::connect(' . var_export($config, true) . ', false);'
                . '$db->query("SET application_name TO ' . $schema . '");'
                . '$model = new \\App\\Models\\BracketModel($db);'
                . 'exit($model->savePicks(1,1,[["team_id"=>16,"region"=>1,"round"=>1,"game"=>1,"team"=>2]]) ? 0 : 2);';
            $process = proc_open([PHP_BINARY, '-r', $source], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, ROOTPATH);
            self::assertIsResource($process);
            fclose($pipes[0]);
            $blocked = false;
            $deadline = microtime(true) + 10;
            do {
                pg_query($db, 'SELECT pg_stat_clear_snapshot()');
                $result = pg_query_params($db, 'SELECT 1 FROM pg_stat_activity WHERE application_name=$1 AND wait_event_type=\'Lock\'', [$schema]);
                $blocked = pg_num_rows($result) > 0;
                if ($blocked) {
                    break;
                }
                if (! proc_get_status($process)['running']) {
                    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
                    self::fail('Save process exited before waiting for membership: ' . $output);
                }
                usleep(20_000);
            } while (microtime(true) < $deadline);
            self::assertTrue($blocked, 'A second session must wait on membership before replacing an initially empty bracket.');
            pg_query($db, 'INSERT INTO picks VALUES (1,1,1,1,1,1,1)');
            pg_query($db, 'COMMIT');
            $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            self::assertSame(0, proc_close($process), $output);
            $process = null;
            self::assertSame([['team_id' => '16']], pg_fetch_all(pg_query($db, 'SELECT team_id FROM picks')));
        } finally {
            pg_query($db, 'ROLLBACK');
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            pg_query($db, 'DROP SCHEMA ' . $schema . ' CASCADE');
            pg_close($db);
        }
    }
}
