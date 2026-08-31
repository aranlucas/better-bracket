<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\BracketService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BracketServiceTest extends TestCase
{
    public function testNormalizesOpeningRoundAndChampionPicks(): void
    {
        $picks = BracketService::normalizePicks([
            '1-1-1-1' => 12,
            '2-4-1-2' => 43,
            'champion' => 43,
        ]);

        self::assertSame([
            ['team_id' => 12, 'region' => 1, 'round' => 1, 'game' => 1, 'team' => 1],
            ['team_id' => 43, 'region' => 2, 'round' => 4, 'game' => 1, 'team' => 2],
            ['team_id' => 43, 'region' => 0, 'round' => 6, 'game' => 1, 'team' => 1],
        ], $picks);
    }

    public function testRejectsInvalidSlotKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BracketService::normalizePicks(['1-9-1-1' => 12]);
    }

    public function testRejectsUnknownTeamValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BracketService::normalizePicks(['1-1-1-1' => 'not-a-team']);
    }

    public function testGroupsTeamsByKnownRegion(): void
    {
        $regions = BracketService::teamsByRegion([
            ['region' => 'south', 'id' => 1],
            ['region' => 'WEST', 'id' => 2],
            ['region' => 'unknown', 'id' => 3],
        ]);

        self::assertSame([['region' => 'south', 'id' => 1]], $regions['south']);
        self::assertSame([['region' => 'WEST', 'id' => 2]], $regions['west']);
        self::assertSame([], $regions['east']);
    }

    public function testRejectsTwoWinnersForOneGame(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Choose only one winner');

        BracketService::validateConsistency(BracketService::normalizePicks([
            '1-1-1-1' => 1,
            '1-1-1-2' => 16,
        ]));
    }

    public function testRejectsTeamThatDidNotAdvance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must have won');

        BracketService::validateConsistency(BracketService::normalizePicks([
            '1-1-1-1' => 1,
            '1-2-1-1' => 16,
        ]));
    }

    public function testAcceptsConsistentPartialBracket(): void
    {
        $picks = BracketService::normalizePicks([
            '1-1-1-1' => 1,
            '1-2-1-1' => 1,
        ]);

        BracketService::validateConsistency($picks);

        self::assertCount(2, $picks);
    }
}
