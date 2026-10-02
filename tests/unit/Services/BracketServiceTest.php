<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\BracketService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BracketServiceTest extends TestCase
{
    /** @return list<array<string, mixed>> */
    private static function teams(): array
    {
        $teams = [];
        foreach (BracketService::REGIONS as $index => $region) {
            for ($seed = 1; $seed <= 16; $seed++) {
                $teams[] = ['id' => $index * 16 + $seed, 'region' => $region, 'seed' => $seed];
            }
        }

        return $teams;
    }

    public function testAcceptsConsistentPartialBracketAndNumericStringIds(): void
    {
        self::assertSame([
            ['team_id' => 1, 'region' => 1, 'round' => 1, 'game' => 1, 'team' => 1],
            ['team_id' => 1, 'region' => 1, 'round' => 2, 'game' => 1, 'team' => 1],
        ], BracketService::validatePicks(['1-1-1-1' => '1', '1-2-1-1' => 1], self::teams()));
        self::assertSame([], BracketService::validatePicks([], self::teams()));
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function invalidPicks(): iterable
    {
        yield 'wrong region' => [['1-1-1-1' => 17]];
        yield 'wrong seed' => [['1-1-1-1' => 8]];
        yield 'opponent in wrong slot' => [['1-1-1-1' => 16]];
        yield 'unknown team' => [['1-1-1-1' => 1000]];
        yield 'nonexistent round' => [['1-9-1-1' => 1]];
        yield 'nonexistent second round game' => [['1-2-5-1' => 1]];
        yield 'nonexistent elite eight game' => [['1-4-8-1' => 1]];
        yield 'nonexistent final four region' => [['4-5-1-1' => 1]];
        yield 'nonexistent championship game' => [['3-5-2-1' => 1]];
        yield 'fractional id' => [['1-1-1-1' => 1.9]];
        yield 'float id' => [['1-1-1-1' => 1.0]];
        yield 'fractional string id' => [['1-1-1-1' => '1.9']];
        yield 'scientific notation' => [['1-1-1-1' => '1e0']];
        yield 'overflow id' => [['1-1-1-1' => '9999999999999999999999999']];
        yield 'zero id' => [['1-1-1-1' => 0]];
        yield 'boolean id' => [['1-1-1-1' => true]];
        yield 'array id' => [['1-1-1-1' => []]];
        yield 'null id' => [['1-1-1-1' => null]];
        yield 'duplicate winner' => [['1-1-1-1' => 1, '1-1-1-2' => 16]];
        yield 'missing predecessor' => [['1-2-1-1' => 1]];
        yield 'losing predecessor' => [['1-1-1-1' => 1, '1-2-1-1' => 16]];
        yield 'wrong feeder' => [['1-1-2-1' => 8, '1-2-1-1' => 8]];
        yield 'champion without final' => [['champion' => 1]];
    }

    #[DataProvider('invalidPicks')]
    public function testRejectsIllegalBracket(array $picks): void
    {
        $this->expectException(InvalidArgumentException::class);
        BracketService::validatePicks($picks, self::teams());
    }

    public function testRejectsOpeningPickWhenSeedIsNotInTournament(): void
    {
        $this->expectException(InvalidArgumentException::class);
        BracketService::validatePicks(['1-1-1-1' => 1], []);
    }

    public function testAcceptsAll63WinnersAndOptionalChampionConfirmation(): void
    {
        $picks = [];
        foreach (BracketService::definition(self::teams()) as $slot => $entry) {
            if ($entry['team'] !== 1) {
                continue;
            }
            $picks[$slot] = $entry['team_id'] ?? $picks[$entry['feeders'][0]];
        }
        // Selection order does not change eligibility.
        self::assertCount(64, BracketService::validatePicks(array_reverse($picks, true), self::teams()));
        unset($picks['champion']);
        self::assertCount(63, BracketService::validatePicks($picks, self::teams()));
    }

    public function testRejectsChampionThatLostTheChampionship(): void
    {
        $picks = ['1-1-1-1' => 1, '1-2-1-1' => 1, '1-3-1-1' => 1, '1-4-1-1' => 1,
            '1-5-1-1' => 1, '3-5-1-1' => 1, 'champion' => 17];
        $this->expectException(InvalidArgumentException::class);
        BracketService::validatePicks($picks, self::teams());
    }

    public function testGroupsTeamsByKnownRegion(): void
    {
        $regions = BracketService::teamsByRegion([
            ['region' => 'south', 'id' => 1], ['region' => 'WEST', 'id' => 2], ['region' => 'unknown', 'id' => 3],
        ]);
        self::assertSame([['region' => 'south', 'id' => 1]], $regions['south']);
        self::assertSame([['region' => 'WEST', 'id' => 2]], $regions['west']);
        self::assertSame([], $regions['east']);
    }
}
