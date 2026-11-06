<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddUserGroupLookupIndex extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE INDEX user_groups_user_id_group_id_idx ON user_groups (user_id, group_id)');
    }

    public function down(): void
    {
        $this->db->query('DROP INDEX user_groups_user_id_group_id_idx');
    }
}
