<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierAddress;
use App\Models\GeminiApiKey;
use App\Services\BulkLabelPdf;
use Illuminate\Http\Request;

class CourierAddressController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        return view('admin.courier_addresses.index', [
            'addresses' => CourierAddress::query()
                ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->orderByDesc('id')
                ->get(),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function create()
    {
        return $this->form(new CourierAddress(['state' => 'Kerala']));
    }

    public function store(Request $request)
    {
        CourierAddress::create($this->validated($request));

        return $request->boolean('add_another')
            ? redirect()->route('admin.courier-addresses.create')->with('success', 'Address saved. Add the next one.')
            : redirect()->route('admin.courier-addresses.index')->with('success', 'Address saved.');
    }

    public function edit(CourierAddress $courierAddress)
    {
        return $this->form($courierAddress);
    }

    public function update(Request $request, CourierAddress $courierAddress)
    {
        $courierAddress->update($this->validated($request));

        return redirect()->route('admin.courier-addresses.index')->with('success', 'Address updated.');
    }

    public function destroy(CourierAddress $courierAddress)
    {
        $courierAddress->delete();

        return redirect()->route('admin.courier-addresses.index')->with('success', 'Address deleted.');
    }

    public function print(Request $request)
    {
        $addresses = $this->selectedAddresses($request);

        return response()->view('admin.bulk_labels.print', [
            'orders' => $addresses,
            'fromAddress' => config('business.courier_sender_address', []),
            'customerId' => config('business.courier_customer_id', ''),
            'layout' => $request->input('layout'),
            'filename' => now()->toDateString().'_courier_address_label.pdf',
            'downloadRoute' => route('admin.courier-addresses.download-pdf'),
            'idField' => 'address_ids',
        ])->header('Content-Disposition', 'inline');
    }

    public function downloadPdf(Request $request, BulkLabelPdf $pdf)
    {
        $addresses = $this->selectedAddresses($request);
        $filename = now()->toDateString().'_courier_address_label.pdf';
        $content = $pdf->make(
            $addresses,
            $request->input('layout'),
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

    private function form(CourierAddress $address)
    {
        return view('admin.courier_addresses.form', [
            'address' => $address,
            'geminiApiKeys' => GeminiApiKey::query()->orderByDesc('is_active')->orderByDesc('id')->get(),
        ]);
    }

    private function selectedAddresses(Request $request)
    {
        $data = $request->validate([
            'address_ids' => ['required', 'array', 'min:1', 'max:500'],
            'address_ids.*' => ['required', 'integer', 'distinct'],
            'layout' => ['required', 'in:vertical,split'],
        ]);

        $addresses = CourierAddress::query()->whereIn('id', $data['address_ids'])->orderByDesc('id')->get();
        abort_if($addresses->isEmpty(), 422, 'No addresses were selected.');

        return $addresses;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'house_building' => ['required', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'pin_code' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
        ], [
            'pin_code.regex' => 'PIN code must be 6 digits.',
        ]);
    }
}
