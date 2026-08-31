<?php

namespace App\Services;

use InvalidArgumentException;

final class BracketService
{
    /** @var list<array{1:int,2:int}> */
    public const OPENING_MATCHUPS = [
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

    /** @param list<array<string, mixed>> $teams */
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
