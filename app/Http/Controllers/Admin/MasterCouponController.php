<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MasterCoupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterCouponController extends Controller
{
    public function index() { return view('admin.master_coupons.index', ['coupons' => MasterCoupon::with('categories')->latest()->get(), 'categories' => Category::where('status', 'active')->orderBy('name')->get()]); }
    public function store(Request $request) { return $this->save($request, new MasterCoupon()); }
    public function update(Request $request, MasterCoupon $masterCoupon) { return $this->save($request, $masterCoupon); }

    public function toggleStatus(MasterCoupon $masterCoupon)
    {
        $masterCoupon->status = ! $masterCoupon->status;
        $masterCoupon->save();
        return response()->json(['success' => true, 'status' => (bool) $masterCoupon->status, 'label' => $masterCoupon->status ? 'Active' : 'Inactive']);
    }

    private function save(Request $request, MasterCoupon $coupon)
    {
        // Category picker submits checked IDs only. No checked category means all active categories.
        $request->merge(['all_categories' => empty($request->input('category_ids')) ? 1 : 0]);
        $data = $request->validate([
            'code' => ['required','string','max:80', Rule::unique('master_coupons','code')->ignore($coupon->id)->where(fn($q) => $q->whereRaw('LOWER(code) = ?', [mb_strtolower(trim((string)$request->input('code')))]))],
            'name' => 'required|string|max:150', 'discount_type' => 'required|in:fixed,percentage', 'discount_value' => 'required|numeric|min:0.01',
            'minimum_purchase' => 'required|numeric|min:0', 'starts_at' => 'required|date', 'ends_at' => 'required|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1', 'usage_limit_mode' => 'required|in:all,limited', 'allow_with_offer' => 'required|boolean', 'all_categories' => 'required|boolean',
            'status' => 'required|boolean', 'category_ids' => 'nullable|array', 'category_ids.*' => 'integer|exists:categories,id',
        ]);
        if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        $data['code'] = mb_strtoupper(trim($data['code']));
        $data['usage_limit'] = $data['usage_limit_mode'] === 'limited' ? ($data['usage_limit'] ?? null) : null;
        if ($data['all_categories']) $data['category_ids'] = [];
        elseif (empty($data['category_ids'])) return back()->withErrors(['category_ids' => 'Select at least one active category.'])->withInput();
        elseif (Category::whereIn('id', $data['category_ids'])->where('status', 'active')->count() !== count(array_unique($data['category_ids']))) return back()->withErrors(['category_ids' => 'Only active categories can be selected.'])->withInput();
        $categories = $data['category_ids'] ?? [];
        unset($data['category_ids'], $data['usage_limit_mode']);
        $coupon->fill($data)->save();
        $coupon->categories()->sync($categories);
        return redirect()->route('admin.master-coupons.index')->with('success', 'Coupon saved successfully.');
    }
}
