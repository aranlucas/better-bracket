<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class HardenDatabaseIntegrity extends Migration
{
    public function up(): void
    {
        $this->db->query('CREATE UNIQUE INDEX IF NOT EXISTS users_email_lower_unique ON users (LOWER(email))');
        $this->db->query('CREATE INDEX IF NOT EXISTS user_groups_user_id_idx ON user_groups (user_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS picks_group_user_idx ON picks (group_id, user_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS picks_team_id_idx ON picks (team_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS games_team_id_1_idx ON games (team_id_1)');
        $this->db->query('CREATE INDEX IF NOT EXISTS games_team_id_2_idx ON games (team_id_2)');

        $this->db->query(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'picks_membership_fk'
                ) THEN
                    ALTER TABLE picks
                        ADD CONSTRAINT picks_membership_fk
                        FOREIGN KEY (group_id, user_id)
                        REFERENCES user_groups (group_id, user_id)
                        ON DELETE CASCADE;
                END IF;
            END
            $$
            SQL);

        $this->db->query(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'picks_slot_shape_check'
                ) THEN
                    ALTER TABLE picks
                        ADD CONSTRAINT picks_slot_shape_check CHECK (
                            (region = 0 AND round = 6 AND game = 1 AND team = 1)
                            OR
                            (region BETWEEN 1 AND 4 AND round BETWEEN 1 AND 5)
                        );
                END IF;
            END
            $$
            SQL);
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE picks DROP CONSTRAINT IF EXISTS picks_slot_shape_check');
        $this->db->query('ALTER TABLE picks DROP CONSTRAINT IF EXISTS picks_membership_fk');
        $this->db->query('DROP INDEX IF EXISTS games_team_id_2_idx');
        $this->db->query('DROP INDEX IF EXISTS games_team_id_1_idx');
        $this->db->query('DROP INDEX IF EXISTS picks_team_id_idx');
        $this->db->query('DROP INDEX IF EXISTS picks_group_user_idx');
        $this->db->query('DROP INDEX IF EXISTS user_groups_user_id_idx');
        $this->db->query('DROP INDEX IF EXISTS users_email_lower_unique');
    }
}
