# Lucky Winner

Open `/luckywinner` or **Admin → Lucky Winner**. Guests use the existing admin login and return to the studio. No separate credentials are needed.

## Running a giveaway

1. Choose one of the last four calendar months, or any custom date range, including a single day.
2. Load eligible orders and choose the number of gifts. Each order is a separate entry, including multiple orders from the same customer.
3. Start the draw. The server selects a winner; the reel shuffles, slows down, and reveals that result. Use Next Winner to continue. Full screen hides the surrounding controls for recording; Escape or the full screen button exits.
4. Select See All Winners to show the final result. Exit full screen, then click **Store Winners** to create the permanent draw and linked winner records.
5. Find stored draws in **Winner archive**. To draw the same period again, explicitly start a new draw. Existing records are never overwritten.

## Eligibility and return weighting

`config/luckywinner.php` contains the reusable rules. Defaults:

- Payment status must be `paid` or the legacy `completed` value; order status must be `confirmed`, `processing`, `packed`, `shipped`, or `delivered`. Both manual and website orders qualify. Unpaid COD/manual orders do not qualify.
- The date is `sale_date`, falling back to `created_at`, matching the application's effective order date. Inclusive dates use Asia/Kolkata; order filtering ends at the beginning of the next day, exclusively.
- Test phone numbers configured in `excluded_test_phones`, orders with inactive operations, and dummy/test operations are excluded, consistent with the existing separation of real and test sales.
- An active configured return operation within the selected dates reduces weight from 100 to 50. Its `return_date` is used, falling back to its creation date. It affects that order and other entries matching its customer's normalized phone, case-insensitive email, or user account. Names alone are never used to identify customers. A return on an older purchase can affect an eligible purchase by the same customer.
- Multiple returns apply the reduction once. Return weight is clamped to 1–normal weight, so it cannot exclude an otherwise eligible entry or increase its probability. Adjust the return operation types and weight in the config if needed.
- Immediately before the first selection, the complete pool is checked again. Any change requires reloading. Once selection starts, that snapshot is frozen so all winners in the event use the same rules and pool.

## Randomness, retries, and storage

The backend uses PHP `random_int(1, totalWeight)` with cumulative integer weights, excluding previously winning order IDs. The browser's decorative shuffle never selects winners. Another order belonging to the same customer may still win.

Temporary drafts are admin-owned, expire after 24 hours, and can resume on page refresh in the same session. They are held in a lock-capable cache, not in the permanent draw tables. Clearing the cache can discard unsaved draws. By default this is the file cache. For multiple application servers, set `LUCKYWINNER_CACHE_STORE` to a shared lock-capable store such as Redis.

Selection requests include the winner position. Retrying the same position returns the same winner. Gift count is fixed after selection begins. Store requests use a unique draft token; a repeated request returns the existing draw, even after cache expiry. Database uniqueness also prevents duplicate event tokens, winner positions, and order IDs within an event.

The event includes an immutable rules snapshot, eligibility check time, draw time, host identity, period, and counts. Each winner stores the order/customer/address and eligibility snapshot, selection time, and store timestamp. Snapshot order IDs intentionally have no cascading order foreign key so order deletion cannot erase winner history. Addresses are shown only in private admin history details, not in game responses or the recording area.

No order, product, stock, payment, refund, expense, or income records are changed by Lucky Winner.

## Installation and verification

No frontend build or new packages are required. Deploy the PHP, Blade, CSS and JS files, then apply the additive migration:

```sh
php artisan migrate --path=database/migrations/2026_09_05_000008_create_lucky_draw_tables.php
php artisan view:clear
php artisan test --filter=LuckyWinner
```

The tests cover access control, dates and sale-date fallback, duplicate customer entries, failed/unpaid/cancelled exclusion, customer return weights, weighted ticket boundaries, sequence/retry protection, refresh recovery, expiry, immutable stored snapshots, repeat events, and lack of writes to existing business records.
