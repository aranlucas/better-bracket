<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

final class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->db->table('teams')->countAllResults() > 0) {
            return;
        }

        $legacySql = file_get_contents(dirname(__DIR__, 3) . '/db.sql');
        if ($legacySql === false) {
            throw new RuntimeException('Tournament seed data could not be read.');
        }

        $seedSql = preg_replace('#/\*.*?\*/#s', '', $legacySql);
        if ($seedSql === null) {
            throw new RuntimeException('Tournament seed comments could not be removed.');
        }

        preg_match_all('/INSERT INTO (?:teams|games|scores)\b.*?;/is', $seedSql, $matches);
        if ($matches[0] === []) {
            throw new RuntimeException('Tournament seed statements were not found.');
        }

        $this->db->transException(true)->transStart();
        foreach ($matches[0] as $statement) {
            $this->db->query($statement);
        }
        $this->db->transComplete();
    }
}
