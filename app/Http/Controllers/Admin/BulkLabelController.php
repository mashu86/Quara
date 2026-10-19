<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BulkLabelPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkLabelController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $orders = collect();
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $orders = $this->eligibleOrders($filters['start_date'], $filters['end_date'])
                ->with('items.product.images')
                ->orderByDesc(DB::raw('COALESCE(sale_date, created_at)'))
                ->get();
        }

        return view('admin.bulk_labels.index', [
            'orders' => $orders,
            'startDate' => $filters['start_date'] ?? '',
            'endDate' => $filters['end_date'] ?? '',
            'fromAddress' => config('business.courier_sender_address', []),
        ]);
    }

    public function print(Request $request)
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:500'],
            'order_ids.*' => ['required', 'integer', 'distinct'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'layout' => ['required', 'in:vertical,split'],
        ]);

        $orders = $this->eligibleOrders($data['start_date'], $data['end_date'])
            ->whereIn('id', $data['order_ids'])
            ->get();

        abort_if($orders->isEmpty(), 422, 'No eligible paid orders were selected in this date range.');

        return response()->view('admin.bulk_labels.print', [
            'orders' => $orders,
            'fromAddress' => config('business.courier_sender_address', []),
            'customerId' => config('business.courier_customer_id', ''),
            'layout' => $data['layout'],
            'filename' => $data['start_date'].'_'.$data['end_date'].'_bulk_label.pdf',
            'startDate' => $data['start_date'],
            'endDate' => $data['end_date'],
        ])->header('Content-Disposition', 'inline');
    }

    public function downloadPdf(Request $request, BulkLabelPdf $pdf)
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:500'],
            'order_ids.*' => ['required', 'integer', 'distinct'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'layout' => ['required', 'in:vertical,split'],
        ]);

        $orders = $this->eligibleOrders($data['start_date'], $data['end_date'])
            ->whereIn('id', $data['order_ids'])
            ->get();
        abort_if($orders->isEmpty(), 422, 'No eligible paid orders were selected in this date range.');

        $filename = $data['start_date'].'_'.$data['end_date'].'_bulk_label.pdf';
        $content = $pdf->make(
            $orders,
            $data['layout'],
            config('business.courier_sender_address', []),
            config('business.courier_customer_id', '')
        );

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function eligibleOrders(string $startDate, string $endDate)
    {
        return Order::query()
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween(DB::raw('COALESCE(sale_date, created_at)'), [$startDate.' 00:00:00', $endDate.' 23:59:59']);
    }
}
