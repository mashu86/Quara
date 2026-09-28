<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistrictOffer;
use App\Services\DistrictOfferService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DistrictOfferController extends Controller
{
    public function index(Request $request)
    {
        $districts = config('district_offers.districts');
        $latestSavedDistrict = DistrictOffer::where('state', config('district_offers.state'))
            ->whereIn('district', $districts)->latest('id')->value('district');
        $selectedDistrict = $request->query('district', old('district', $latestSavedDistrict ?? ''));
        if (!in_array($selectedDistrict, $districts, true)) {
            $selectedDistrict = '';
        }
        $offer = $selectedDistrict ? DistrictOffer::where('district', $selectedDistrict)
            ->where('state', config('district_offers.state'))->latest('id')->first() : null;
        $restoreInput = old('district') === $selectedDistrict;

        return view('admin.district_offers.index', compact('districts', 'selectedDistrict', 'offer', 'restoreInput'));
    }

    public function update(Request $request, string $district)
    {
        abort_unless(in_array($district, config('district_offers.districts'), true), 404);
        $request->merge(['district' => $district]);
        $data = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'method' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'gt:0', 'max:'.($request->input('method') === 'percentage' ? '100' : '9999999999.99')],
            'is_active' => ['required', 'boolean'],
        ]);
        DistrictOffer::create($data + ['district' => $district, 'state' => config('district_offers.state'), 'created_by' => $request->user()->id]);

        return redirect()->route('admin.district-offers.index', ['district' => $district])
            ->with('success', $district.' offer updated. Existing orders retain their saved discount.');
    }

    public function preview(Request $request, DistrictOfferService $offers)
    {
        $data = $request->validate([
            'district' => 'required|string|max:100', 'state' => 'nullable|string|max:100',
            'sale_date' => 'required|date_format:Y-m-d',
        ]);
        $offer = $offers->eligible($data['district'], $data['state'] ?? 'Kerala', $data['sale_date']);

        return response()->json(['offer' => $offer ? $offer->only(['id', 'district', 'method', 'value']) : null]);
    }
}
