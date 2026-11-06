<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

class GroupModel extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;

    /** @return list<array<string, mixed>> */
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

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->table('groups g')
            ->select('g.id, g.name, g.date_created, gp.description, gp.caption')
            ->join('groups_profile gp', 'gp.group_id = g.id', 'left')
            ->orderBy('g.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** @return array<string, mixed>|null */
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

    /** @return list<array<string, mixed>> */
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

    /** @return array{id?: int, error?: string} */
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

}
