<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

/**
 * @phpstan-type Slot array{region:int, round:int, game:int, team:int, feeders:list<string>, team_id:?int}
 */
final class BracketService
{
    public const array REGIONS = ['south', 'west', 'east', 'midwest'];
    public const array GAMES_BY_ROUND = [1 => 8, 2 => 4, 3 => 2, 4 => 1];

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
     * The legal slots and their inputs, shared by validation and browser transitions.
     * Champion is the existing optional confirmation of the championship winner.
     *
     * @param list<array<string, mixed>> $teams
     * @return array<string, Slot>
     */
    public static function definition(array $teams): array
    {
        $bySeed = [];
        foreach ($teams as $team) {
            $bySeed[strtolower((string) $team['region'])][(int) $team['seed']] = (int) $team['id'];
        }

        $slots = [];
        foreach (self::REGIONS as $index => $regionName) {
            $region = $index + 1;
            foreach (self::GAMES_BY_ROUND as $round => $gameCount) {
                for ($game = 1; $game <= $gameCount; $game++) {
                    for ($team = 1; $team <= 2; $team++) {
                        $sourceGame = ($game - 1) * 2 + $team;
                        $slots["{$region}-{$round}-{$game}-{$team}"] = [
                            'region' => $region, 'round' => $round, 'game' => $game, 'team' => $team,
                            'feeders' => $round === 1 ? [] : self::gameSlots($region, $round - 1, $sourceGame),
                            'team_id' => $round === 1 ? ($bySeed[$regionName][self::OPENING_MATCHUPS[$game - 1][$team]] ?? null) : null,
                        ];
                    }
                }
            }
        }

        for ($region = 1; $region <= 3; $region++) {
            for ($team = 1; $team <= 2; $team++) {
                $sourceRegion = $region < 3 ? ($region - 1) * 2 + $team : $team;
                $slots["{$region}-5-1-{$team}"] = [
                    'region' => $region, 'round' => 5, 'game' => 1, 'team' => $team,
                    'feeders' => self::gameSlots($sourceRegion, $region < 3 ? 4 : 5, 1),
                    'team_id' => null,
                ];
            }
        }
        $slots['champion'] = [
            'region' => 0, 'round' => 6, 'game' => 1, 'team' => 1,
            'feeders' => self::gameSlots(3, 5, 1), 'team_id' => null,
        ];

        return $slots;
    }

    /**
     * Validate the complete selection before returning rows suitable for persistence.
     * Partial brackets are allowed, but every pick must have a legal path from its seed.
     *
     * @param array<string, mixed> $picks
     * @param list<array<string, mixed>> $teams
     * @return list<array{team_id:int, region:int, round:int, game:int, team:int}>
     */
    public static function validatePicks(array $picks, array $teams): array
    {
        // 63 game winners plus the optional, separately stored champion confirmation.
        if (count($picks) > 64) {
            throw new InvalidArgumentException('A bracket cannot contain more than 64 selections.');
        }

        $definition = self::definition($teams);
        $selected = [];
        $games = [];
        $normalized = [];
        foreach ($picks as $slot => $teamId) {
            if (! is_string($slot) || ! isset($definition[$slot])) {
                throw new InvalidArgumentException('The bracket contains an invalid game slot.');
            }
            if ((! is_int($teamId) && (! is_string($teamId) || ! ctype_digit($teamId) || (string) (int) $teamId !== $teamId)) || (int) $teamId < 1) {
                throw new InvalidArgumentException('The bracket contains an invalid team selection.');
            }

            $entry = $definition[$slot];
            $gameKey = $entry['region'] . '-' . $entry['round'] . '-' . $entry['game'];
            if (isset($games[$gameKey])) {
                throw new InvalidArgumentException('Choose only one winner for each game.');
            }
            $games[$gameKey] = true;
            $selected[$slot] = (int) $teamId;
            $normalized[] = [
                'team_id' => (int) $teamId, 'region' => $entry['region'],
                'round' => $entry['round'], 'game' => $entry['game'], 'team' => $entry['team'],
            ];
        }

        foreach ($selected as $slot => $teamId) {
            $entry = $definition[$slot];
            if ($entry['feeders'] === []) {
                if ($teamId !== $entry['team_id']) {
                    throw new InvalidArgumentException('An opening-round pick must match its region and seed.');
                }
                continue;
            }
            $eligible = array_intersect_key($selected, array_flip($entry['feeders']));
            if (! in_array($teamId, $eligible, true)) {
                throw new InvalidArgumentException('A later-round pick must have won its previous game.');
            }
        }

        return $normalized;
    }

    /** @return list<string> */
    private static function gameSlots(int $region, int $round, int $game): array
    {
        return ["{$region}-{$round}-{$game}-1", "{$region}-{$round}-{$game}-2"];
    }

    /**
     * @param list<array<string, mixed>> $teams
     * @return array<string, list<array<string, mixed>>>
     */
    public static function teamsByRegion(array $teams): array
    {
        $result = array_fill_keys(self::REGIONS, []);
        foreach ($teams as $team) {
            $region = strtolower((string) ($team['region'] ?? ''));
            if (isset($result[$region])) {
                $result[$region][] = $team;
            }
        }

        return $result;
    }
}
