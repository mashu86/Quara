<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderOperation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class LuckyWinnerEligibility
{
    public function period(array $input): array
    {
        $timezone = config('luckywinner.timezone');
        if ($input['draw_type'] === 'month') {
            $start = CarbonImmutable::createFromFormat('!Y-m', $input['month'], $timezone);
            $end = $start->endOfMonth();
            $label = $start->format('F Y');
        } else {
            $start = CarbonImmutable::parse($input['start_date'], $timezone)->startOfDay();
            $end = CarbonImmutable::parse($input['end_date'], $timezone)->endOfDay();
            $label = $start->isSameDay($end) ? $start->format('d M Y') : $start->format('d M Y').' – '.$end->format('d M Y');
        }

        return [
            'draw_type' => $input['draw_type'],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'selected_month' => $input['draw_type'] === 'month' ? $start->format('Y-m') : null,
            'period_label' => $label,
            'timezone' => $timezone,
        ];
    }

    public function rules(): array
    {
        $normal = max(1, min(1000000, (int) config('luckywinner.normal_weight')));

        return [
            'normal_weight' => $normal,
            'return_weight' => max(1, min($normal, (int) config('luckywinner.return_weight'))),
            'order_statuses' => config('luckywinner.successful_order_statuses'),
            'payment_statuses' => config('luckywinner.successful_payment_statuses'),
            'return_operation_types' => config('luckywinner.return_operation_types'),
            'return_scope' => 'Order or matching customer phone/email/account; active return activity within the selected period.',
            'date_basis' => 'sale_date, falling back to created_at; inclusive local dates',
            'algorithm' => 'random_int cumulative integer weights, without replacement by order ID',
        ];
    }

    public function entries(array $period, array $rules): array
    {
        $start = $period['start_date'].' 00:00:00';
        $until = CarbonImmutable::parse($period['end_date'], $period['timezone'])->addDay()->startOfDay()->toDateTimeString();

        $returnsByOrder = [];
        $returnsByIdentity = [];
        $returns = OrderOperation::with('order:id,user_id,customer_phone,customer_email')
            ->where('status', 'active')
            ->whereIn('operation_type', $rules['return_operation_types'])
            ->whereDate(DB::raw('COALESCE(return_date, created_at)'), '>=', $period['start_date'])
            ->whereDate(DB::raw('COALESCE(return_date, created_at)'), '<=', $period['end_date'])
            ->get();

        foreach ($returns as $return) {
            $info = ['operation_id' => $return->id, 'order_id' => $return->order_id, 'type' => $return->operation_type,
                'date' => ($return->return_date ?? $return->created_at)->toDateString()];
            $returnsByOrder[$return->order_id][$return->id] = $info;
            if ($return->order) {
                foreach ($this->identities($return->order) as $identity) {
                    $returnsByIdentity[$identity][$return->id] = $info;
                }
            }
        }

        $orders = Order::query()
            ->whereIn('payment_status', $rules['payment_statuses'])
            ->whereIn('order_status', $rules['order_statuses'])
            ->where(DB::raw('COALESCE(sale_date, created_at)'), '>=', $start)
            ->where(DB::raw('COALESCE(sale_date, created_at)'), '<', $until)
            ->whereDoesntHave('operations', fn ($query) => $query->where('status', 'inactive')->orWhere('operation_type', 'dummy_test'))
            ->orderBy('id')->get();

        $entries = [];
        foreach ($orders as $order) {
            if (in_array($this->phone($order->customer_phone), config('luckywinner.excluded_test_phones'), true)) {
                continue;
            }
            $activity = $returnsByOrder[$order->id] ?? [];
            foreach ($this->identities($order) as $identity) {
                $activity += $returnsByIdentity[$identity] ?? [];
            }
            ksort($activity);
            $hasReturn = count($activity) > 0;
            $entries[] = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'order_date' => ($order->sale_date ?? $order->created_at)->toDateTimeString(),
                'customer_address' => implode(', ', array_filter([$order->house_building, $order->street, $order->area, $order->city, $order->district, $order->state, $order->pin_code])),
                'order_type' => $order->order_source === 'manual' || $order->payment_method === 'offline_sale' ? 'manual' : 'website',
                'eligibility' => [
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'has_return' => $hasReturn,
                    'return_activity' => array_values($activity),
                    'weight' => $hasReturn ? $rules['return_weight'] : $rules['normal_weight'],
                ],
            ];
        }

        return $entries;
    }

    private function identities(Order $order): array
    {
        $keys = [];
        if ($phone = $this->phone($order->customer_phone)) {
            $keys[] = 'phone:'.$phone;
        }
        if ($email = strtolower(trim((string) $order->customer_email))) {
            $keys[] = 'email:'.$email;
        }
        if ($order->user_id) {
            $keys[] = 'user:'.$order->user_id;
        }

        return $keys;
    }

    private function phone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');
        // Normalize Indian local numbers and +91 / 0091 / leading-zero forms.
        if ((strlen($digits) === 12 && str_starts_with($digits, '91')) ||
            (strlen($digits) === 14 && str_starts_with($digits, '0091')) ||
            (strlen($digits) === 11 && str_starts_with($digits, '0'))) {
            return substr($digits, -10);
        }

        return $digits;
    }
}
