<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\BracketService;
use CodeIgniter\Model;
use Throwable;

class GroupModel extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    public function forUser(int $userId): array
    {
        return $this->db->table('groups g')
            ->select('g.id, g.name, g.date_created, gp.description, gp.caption')
            ->join('user_groups ug', 'ug.group_id = g.id')
            ->join('groups_profile gp', 'gp.group_id = g.id', 'left')
            ->where('ug.user_id', $userId)
            ->orderBy('g.date_created', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function all(): array
    {
        return $this->db->table('groups g')
            ->select('g.id, g.name, g.date_created, gp.description, gp.caption')
            ->join('groups_profile gp', 'gp.group_id = g.id', 'left')
            ->orderBy('g.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findWithProfile(int $id): ?array
    {
        $group = $this->db->table('groups g')
            ->select('g.id, g.name, g.date_created, gp.description, gp.caption, gp.picturelocation')
            ->join('groups_profile gp', 'gp.group_id = g.id', 'left')
            ->where('g.id', $id)
            ->get()
            ->getRowArray();

        return is_array($group) ? $group : null;
    }

    public function members(int $id): array
    {
        return $this->db->table('user_groups ug')
            ->select('u.id, u.email, up.first, up.last')
            ->join('users u', 'u.id = ug.user_id')
            ->join('users_profile up', 'up.user_id = u.id', 'left')
            ->where('ug.group_id', $id)
            ->orderBy('up.first', 'ASC')
            ->orderBy('up.last', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return $this->db->table('user_groups')
            ->where(['group_id' => $groupId, 'user_id' => $userId])
            ->countAllResults() > 0;
    }

    public function createGroup(string $name, string $description, string $caption, int $ownerId): array
    {
        try {
            $this->db->transBegin();
            $this->insert(['name' => $name]);
            $id = (int) $this->getInsertID();

            if ($id < 1) {
                throw new \RuntimeException('The group id was not returned after insert.');
            }

            $this->db->table('groups_profile')->insert([
                'group_id'    => $id,
                'description' => $description,
                'caption'     => $caption,
            ]);
            $this->db->table('user_groups')->insert([
                'group_id' => $id,
                'user_id'  => $ownerId,
            ]);

            if (! $this->db->transStatus()) {
                throw new \RuntimeException('The group transaction failed.');
            }

            $this->db->transCommit();

            return ['id' => $id];
        } catch (Throwable $exception) {
            $this->db->transRollback();
            log_message('error', 'Group creation failed: ' . $exception->getMessage());

            return ['error' => 'We could not create the group right now. Please try again.'];
        }
    }

    public function addMember(int $groupId, int $userId): bool
    {
        if ($this->findWithProfile($groupId) === null || $this->isMember($groupId, $userId)) {
            return false;
        }

        return $this->db->table('user_groups')->insert([
            'group_id' => $groupId,
            'user_id'  => $userId,
        ]) !== false;
    }

    public function bracketTeams(): array
    {
        return $this->db->table('teams')
            ->select('id, team_name, seed, region')
            ->orderBy('region', 'ASC')
            ->orderBy('seed', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function savePicks(int $groupId, int $userId, array $picks): bool
    {
        try {
            $this->db->transBegin();
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
