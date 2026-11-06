<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

class BracketModel extends Model
{
    /** @return list<array<string, mixed>> */
    public function bracketTeams(): array
    {
        return $this->db->table('teams')
            ->select('id, team_name, seed, region')
            ->orderBy('region', 'ASC')
            ->orderBy('seed', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** @param list<array{team_id:int, region:int, round:int, game:int, team:int}> $picks */
    public function savePicks(int $groupId, int $userId, array $picks): bool
    {
        try {
            $this->db->transBegin();
            // Lock the stable membership row even when the bracket has no picks.
            // Query Builder's no-op update serializes replacement across sessions.
            $this->db->table('user_groups')->where([
                'group_id' => $groupId,
                'user_id' => $userId,
            ])->update(['group_id' => $groupId]);
            $this->db->table('picks')->where([
                'group_id' => $groupId,
                'user_id'  => $userId,
            ])->delete();

            foreach ($picks as $pick) {
                $this->db->table('picks')->insert([
                    'user_id' => $userId,
                    'group_id' => $groupId,
                    'team_id' => $pick['team_id'],
                    'region'  => $pick['region'],
                    'round'   => $pick['round'],
                    'game'    => $pick['game'],
                    'team'    => $pick['team'],
                ]);
            }

            if (! $this->db->transStatus()) {
                throw new \RuntimeException('The picks transaction failed.');
            }

            $this->db->transCommit();

            return true;
        } catch (Throwable $exception) {
            $this->db->transRollback();
            log_message('error', 'Pick save failed: ' . $exception->getMessage());

            return false;
        }
    }

}
