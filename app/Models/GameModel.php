<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class GameModel extends Model
{
    /** @return list<array<string, mixed>> */
    public function pastGames(): array
    {
        return $this->db->table('games g')
            ->select('g.date_played, t1.team_name AS team_1, t2.team_name AS team_2, s.score AS score_1, s.score_2 AS score_2')
            ->join('teams t1', 't1.id = g.team_id_1')
            ->join('teams t2', 't2.id = g.team_id_2')
            ->join('scores s', 's.game_id = g.id')
            ->orderBy('g.date_played', 'ASC')
            ->get()
            ->getResultArray();
    }
}
