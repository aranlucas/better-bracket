<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class BracketService
{
    /** @var list<array{1:int,2:int}> */
    public const array OPENING_MATCHUPS = [
        ['1' => 1, '2' => 16],
        ['1' => 8, '2' => 9],
        ['1' => 5, '2' => 12],
        ['1' => 4, '2' => 13],
        ['1' => 6, '2' => 11],
        ['1' => 3, '2' => 14],
        ['1' => 7, '2' => 10],
        ['1' => 2, '2' => 15],
    ];

    /**
     * Converts the browser's slot map into database rows and rejects malformed
     * keys before they reach the database.
     *
     * @param array<string, mixed> $picks
     * @return list<array{team_id:int, region:int, round:int, game:int, team:int}>
     */
    public static function normalizePicks(array $picks): array
    {
        if (count($picks) > 63) {
            throw new InvalidArgumentException('A bracket cannot contain more than 63 picks.');
        }

        $normalized = [];
        foreach ($picks as $slot => $teamId) {
            if (! is_string($slot) || ! is_numeric($teamId) || (int) $teamId < 1) {
                throw new InvalidArgumentException('The bracket contains an invalid team selection.');
            }

            if ($slot === 'champion') {
                $normalized[] = [
                    'team_id' => (int) $teamId,
                    'region'  => 0,
                    'round'   => 6,
                    'game'    => 1,
                    'team'    => 1,
                ];
                continue;
            }

            if (preg_match('/\A([1-4])-([1-5])-([1-8])-(1|2)\z/', $slot, $matches) !== 1) {
                throw new InvalidArgumentException('The bracket contains an invalid game slot.');
            }

            $normalized[] = [
                'team_id' => (int) $teamId,
                'region'  => (int) $matches[1],
                'round'   => (int) $matches[2],
                'game'    => (int) $matches[3],
                'team'    => (int) $matches[4],
            ];
        }

        return $normalized;
    }

    /**
     * @param list<array{team_id:int, region:int, round:int, game:int, team:int}> $picks
     */
    public static function validateConsistency(array $picks): void
    {
        $slots = [];
        $games = [];

        foreach ($picks as $pick) {
            $slot = self::slotKey($pick);
            $game = $pick['region'] . '-' . $pick['round'] . '-' . $pick['game'];
            if (isset($games[$game])) {
                throw new InvalidArgumentException('Choose only one winner for each game.');
            }

            $games[$game] = true;
            $slots[$slot] = $pick['team_id'];
        }

        foreach ($picks as $pick) {
            $feeders = self::feederSlots($pick);
            if ($feeders === []) {
                continue;
            }

            $eligibleTeamIds = array_values(array_intersect_key($slots, array_flip($feeders)));
            if (! in_array($pick['team_id'], $eligibleTeamIds, true)) {
                throw new InvalidArgumentException('A later-round pick must have won its previous game.');
            }
        }
    }

    /** @param array{team_id:int, region:int, round:int, game:int, team:int} $pick */
    private static function slotKey(array $pick): string
    {
        return $pick['region'] === 0
            ? 'champion'
            : implode('-', [$pick['region'], $pick['round'], $pick['game'], $pick['team']]);
    }

    /**
     * @param array{team_id:int, region:int, round:int, game:int, team:int} $pick
     * @return list<string>
     */
    private static function feederSlots(array $pick): array
    {
        if ($pick['region'] === 0) {
            return ['3-5-1-1', '3-5-1-2'];
        }

        if ($pick['round'] === 1) {
            return [];
        }

        if ($pick['round'] <= 4) {
            $sourceGame = (($pick['game'] - 1) * 2) + $pick['team'];

            return [
                $pick['region'] . '-' . ($pick['round'] - 1) . '-' . $sourceGame . '-1',
                $pick['region'] . '-' . ($pick['round'] - 1) . '-' . $sourceGame . '-2',
            ];
        }

        if ($pick['region'] <= 2) {
            $sourceRegion = (($pick['region'] - 1) * 2) + $pick['team'];

            return [$sourceRegion . '-4-1-1', $sourceRegion . '-4-1-2'];
        }

        $sourceRegion = $pick['team'];

        return [$sourceRegion . '-5-1-1', $sourceRegion . '-5-1-2'];
    }

    /**
     * @param list<array<string, mixed>> $teams
     * @return array<string, list<array<string, mixed>>>
     */
    public static function teamsByRegion(array $teams): array
    {
        $regions = ['south', 'west', 'east', 'midwest'];
        $result = array_fill_keys($regions, []);

        foreach ($teams as $team) {
            $region = strtolower((string) ($team['region'] ?? ''));
            if (isset($result[$region])) {
                $result[$region][] = $team;
            }
        }

        return $result;
    }
}
