# Razorpay stock and cancelled orders

The previous checkout wrote `reserved_until` but available stock ignored those reservations. Two customers could start payment for the last unit. Deduction also clamped insufficient stock to zero, and the webhook confirmed orders even when stock deduction failed. Payment callbacks, webhooks and reconciliation did not share an order lock.

Checkout now locks size rows and rechecks available stock before creating the order and its five-minute hold. Available stock excludes active checkout holds and internal reservations. A hold belongs to its order: a late payment cannot consume another customer's hold. Payment stock deductions and order/payment updates share a database transaction and an order lock. Repeated confirmations do not deduct twice. Insufficient stock rolls back all deductions for the order.

Cancelled orders are excluded from auto-sync, manual Razorpay checks and reconciliation. Webhooks and callbacks also preserve cancellation. Cancelling an unpaid pending checkout only releases its hold; it does not add stock that was never deducted.

Only matching captured INR payments with the expected order and amount are confirmed. The callback signature is checked against the stored Razorpay order ID. Failed gateway order creation never opens an unbound payment window. Webhooks require a valid signature; set `RAZORPAY_WEBHOOK_SECRET` to the configured Razorpay webhook secret (the existing API secret fallback remains for installations using it).

Expired payment pages disable reopening and close the browser checkout. A bank payment already in progress can still arrive late. If stock is then unavailable, the payment is recorded as paid, the order stays pending, and an admin notification and order warning request stock/refund review. The customer sees that the order is under review, not that it is being prepared. This does not automatically refund money or change Razorpay dashboard capture settings.

For a paid order under stock review, verify physical availability. Use the existing Razorpay Sync/reconciliation action after correcting actual inventory; it rechecks and deducts stock before confirming. Otherwise arrange the appropriate refund. Do not increase inventory just to bypass the availability check.

The automatic page check still examines at most five recent pending online orders per page load. No scheduler was added. Existing payments and historical stock records were not rewritten.

Validation uses isolated SQLite test data and mocked Razorpay calls; it does not charge customers or verify the live dashboard configuration. MySQL locking uses current reads and transaction retries; production contention should also be exercised in staging.

The focused payment, stock, admin payment-edit and refund tests pass: 27 tests, 142 assertions. The full suite run had 55 passes and two unrelated failures: missing address fields in the manual-sale shipping test and a missing settings table in the visual-search test setup.

Razorpay describes delayed bank responses and late authorisation in its [payment documentation](https://github.com/razorpay/markdown-docs/blob/master/payments/payments/late-authorisation.md).
