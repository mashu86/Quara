<?php

namespace App\Services;

use App\Models\LuckyDraw;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LuckyWinnerDrafts
{
    public function __construct(private LuckyWinnerEligibility $eligibility, private LuckyWinnerRandom $random) {}

    private function cache()
    {
        return Cache::store(config('luckywinner.cache_store'));
    }

    private function key(string $token): string
    {
        return 'luckywinner:draft:'.$token;
    }

    public function create(array $input, User $admin): array
    {
        $period = $this->eligibility->period($input);
        $rules = $this->eligibility->rules();
        $entries = $this->eligibility->entries($period, $rules);
        if (! count($entries)) {
            throw ValidationException::withMessages(['period' => 'No successful paid orders in this period. Try another month or date range.']);
        }

        $draft = [
            'token' => (string) Str::uuid(), 'admin_id' => $admin->id,
            'period' => $period, 'rules' => $rules, 'entries' => $entries,
            'eligibility_checked_at' => now()->toDateTimeString(),
            'expires_at' => now()->addHours(config('luckywinner.draft_lifetime_hours'))->timestamp,
            'gift_count' => null, 'winners' => [], 'stored' => null,
        ];
        $this->put($draft);

        return $draft;
    }

    private function put(array $draft): void
    {
        $this->cache()->put($this->key($draft['token']), $draft, max(1, $draft['expires_at'] - now()->timestamp));
    }

    public function get(string $token, User $admin): array
    {
        $draft = $this->cache()->get($this->key($token));
        abort_unless($draft && $draft['expires_at'] > now()->timestamp, 410, 'This temporary draw has expired. Start a new draw.');
        abort_unless($draft['admin_id'] === $admin->id, 403);

        return $draft;
    }

    public function select(string $token, User $admin, int $gifts, int $position): array
    {
        return $this->cache()->lock($this->key($token).':lock', 60)->block(5, function () use ($token, $admin, $gifts, $position) {
            $draft = $this->get($token, $admin);
            abort_if($draft['stored'], 409, 'This draw is already stored. Start a new draw to select again.');
            if ($gifts < 1 || $gifts > count($draft['entries'])) {
                throw ValidationException::withMessages(['gift_count' => 'Choose between 1 and '.count($draft['entries']).' gifts.']);
            }
            if ($draft['gift_count'] !== null && $draft['gift_count'] !== $gifts) {
                abort(409, 'The gift count is fixed once the draw starts.');
            }
            // Retrying a lost response returns the same result, never another winner.
            if ($position >= 1 && $position <= count($draft['winners'])) {
                return $draft;
            }
            abort_unless($position === count($draft['winners']) + 1 && $position <= $gifts, 409, 'Select winners in sequence.');

            if (! count($draft['winners'])) {
                $current = $this->eligibility->entries($draft['period'], $draft['rules']);
                abort_unless($current === $draft['entries'], 409, 'Orders or returns changed since loading. Start a new draw to reload eligible entries.');
                $draft['eligibility_checked_at'] = now()->toDateTimeString();
            }

            $winningIds = array_column($draft['winners'], 'order_id');
            $remaining = array_values(array_filter($draft['entries'], fn ($entry) => ! in_array($entry['order_id'], $winningIds, true)));
            $winner = $this->random->select($remaining);
            $winner['position'] = $position;
            $winner['selected_at'] = now()->toDateTimeString();
            $draft['winners'][] = $winner;
            $draft['gift_count'] = $gifts;
            $this->put($draft);

            return $draft;
        });
    }

    public function store(string $token, User $admin): LuckyDraw
    {
        return $this->cache()->lock($this->key($token).':lock', 60)->block(5, function () use ($token, $admin) {
            // Database token uniqueness also protects retries after the cache expires.
            if ($existing = LuckyDraw::where('draft_token', $token)->first()) {
                abort_unless($existing->admin_id === $admin->id, 403);

                return $existing;
            }
            $draft = $this->get($token, $admin);
            abort_unless($draft['gift_count'] && count($draft['winners']) === $draft['gift_count'], 409, 'Reveal all winners before storing this draw.');

            $draw = DB::transaction(function () use ($draft, $admin) {
                $draw = LuckyDraw::create(array_merge($draft['period'], [
                    'draft_token' => $draft['token'], 'admin_id' => $admin->id, 'admin_name' => $admin->name,
                    'total_successful_orders' => count($draft['entries']), 'total_entries' => count($draft['entries']),
                    'gift_count' => $draft['gift_count'], 'winner_count' => count($draft['winners']),
                    'selection_rules' => $draft['rules'], 'eligibility_checked_at' => $draft['eligibility_checked_at'],
                    'drawn_at' => $draft['winners'][0]['selected_at'],
                ]));
                $draw->update(['draw_number' => 'LW-'.now()->format('Y-m').'-'.str_pad((string) $draw->id, 4, '0', STR_PAD_LEFT)]);
                $draw->winners()->createMany($draft['winners']);

                return $draw;
            });
            $draft['stored'] = $this->storedResponse($draw);
            $this->put($draft);

            return $draw;
        });
    }

    public function storedResponse(LuckyDraw $draw): array
    {
        return ['draw_number' => $draw->draw_number, 'url' => route('admin.luckywinner.show', $draw)];
    }

    public function publicState(array $draft): array
    {
        $display = fn ($entry) => [
            'order_id' => $entry['order_id'], 'order_number' => $entry['order_number'],
            'customer_name' => $entry['customer_name'], 'order_type' => $entry['order_type'],
        ];

        return [
            'token' => $draft['token'], 'period' => $draft['period'],
            'total_entries' => count($draft['entries']), 'gift_count' => $draft['gift_count'],
            'entries' => array_map($display, $draft['entries']),
            'winners' => array_map(fn ($entry) => array_merge($display($entry), ['position' => $entry['position']]), $draft['winners']),
            'stored' => $draft['stored'], 'expires_at' => $draft['expires_at'],
        ];
    }
}
