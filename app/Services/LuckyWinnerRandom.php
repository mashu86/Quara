<?php

namespace App\Services;

use InvalidArgumentException;

class LuckyWinnerRandom
{
    public function select(array $entries): array
    {
        $total = array_sum(array_column(array_column($entries, 'eligibility'), 'weight'));
        if ($total < 1 || $total > PHP_INT_MAX) {
            throw new InvalidArgumentException('The draw needs a non-empty, positive weighted pool.');
        }

        return $this->entryAt($entries, random_int(1, (int) $total));
    }

    public function entryAt(array $entries, int $ticket): array
    {
        if ($ticket < 1) {
            throw new InvalidArgumentException('Invalid draw ticket.');
        }
        foreach ($entries as $entry) {
            $weight = (int) $entry['eligibility']['weight'];
            if ($weight < 1) {
                throw new InvalidArgumentException('Every entry needs a positive weight.');
            }
            $ticket -= $weight;
            if ($ticket <= 0) {
                return $entry;
            }
        }

        throw new InvalidArgumentException('Draw ticket is outside the pool.');
    }
}
