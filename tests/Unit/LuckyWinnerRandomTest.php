<?php

namespace Tests\Unit;

use App\Services\LuckyWinnerRandom;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LuckyWinnerRandomTest extends TestCase
{
    public function test_every_weighted_ticket_maps_to_exactly_one_entry(): void
    {
        $entries = [
            ['order_id' => 11, 'eligibility' => ['weight' => 100]],
            ['order_id' => 12, 'eligibility' => ['weight' => 50]],
            ['order_id' => 13, 'eligibility' => ['weight' => 100]],
        ];
        $random = new LuckyWinnerRandom;
        $counts = [11 => 0, 12 => 0, 13 => 0];
        for ($ticket = 1; $ticket <= 250; $ticket++) {
            $counts[$random->entryAt($entries, $ticket)['order_id']]++;
        }
        $this->assertSame([11 => 100, 12 => 50, 13 => 100], $counts);
        $this->assertSame(12, $random->entryAt($entries, 101)['order_id']);
        $this->assertSame(13, $random->entryAt($entries, 151)['order_id']);
        $this->assertContains($random->select($entries)['order_id'], [11, 12, 13]);
    }

    public function test_empty_pool_cannot_select_a_winner(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new LuckyWinnerRandom)->select([]);
    }
}
