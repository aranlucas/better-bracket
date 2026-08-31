<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class CreateApplicationSchema extends Migration
{
    public function up(): void
    {
        $statements = [
            'CREATE TABLE IF NOT EXISTS users (id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY, email varchar(125) NOT NULL UNIQUE, password varchar(256) NOT NULL, date_joined timestamp without time zone NOT NULL DEFAULT statement_timestamp())',
            'CREATE TABLE IF NOT EXISTS users_profile (user_id integer PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE, first varchar(125) NOT NULL, last varchar(125) NOT NULL, description text, caption varchar(125))',
            'CREATE TABLE IF NOT EXISTS groups (id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY, name varchar(125) NOT NULL UNIQUE, date_created timestamp without time zone NOT NULL DEFAULT statement_timestamp())',
            'CREATE TABLE IF NOT EXISTS groups_profile (group_id integer PRIMARY KEY REFERENCES groups(id) ON DELETE CASCADE, picturelocation varchar(256), description text, caption varchar(125))',
            'CREATE TABLE IF NOT EXISTS user_groups (group_id integer NOT NULL REFERENCES groups(id) ON DELETE CASCADE, user_id integer NOT NULL REFERENCES users(id) ON DELETE CASCADE, PRIMARY KEY (group_id, user_id))',
            "CREATE TABLE IF NOT EXISTS teams (id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY, team_name varchar(126) NOT NULL, seed integer NOT NULL CHECK (seed BETWEEN 1 AND 16), region varchar(8) NOT NULL CHECK (region IN ('south', 'west', 'east', 'midwest')), UNIQUE (region, seed))",
            'CREATE TABLE IF NOT EXISTS games (id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY, team_id_1 integer NOT NULL REFERENCES teams(id), team_id_2 integer NOT NULL REFERENCES teams(id), date_played timestamp without time zone NOT NULL, CHECK (team_id_1 <> team_id_2))',
            'CREATE TABLE IF NOT EXISTS scores (game_id integer PRIMARY KEY REFERENCES games(id) ON DELETE CASCADE, score smallint NOT NULL CHECK (score >= 0), score_2 smallint NOT NULL CHECK (score_2 >= 0))',
            'CREATE TABLE IF NOT EXISTS picks (id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY, user_id integer NOT NULL REFERENCES users(id) ON DELETE CASCADE, group_id integer NOT NULL REFERENCES groups(id) ON DELETE CASCADE, team_id integer NOT NULL REFERENCES teams(id), region smallint NOT NULL CHECK (region BETWEEN 0 AND 4), round smallint NOT NULL CHECK (round BETWEEN 1 AND 6), game smallint NOT NULL CHECK (game BETWEEN 1 AND 8), team smallint NOT NULL CHECK (team BETWEEN 1 AND 2), UNIQUE (user_id, group_id, region, round, game, team))',
        ];

        foreach ($statements as $statement) {
            $this->db->query($statement);
        }
    }

    public function down(): void
    {
        foreach (['picks', 'scores', 'games', 'teams', 'user_groups', 'groups_profile', 'groups', 'users_profile', 'users'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
        }
    }
}
