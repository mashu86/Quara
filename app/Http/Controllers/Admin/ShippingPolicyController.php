<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingPolicy;
use Illuminate\Http\Request;

class ShippingPolicyController extends Controller
{
    public function index()
    {
        $policies = ShippingPolicy::orderBy('priority', 'asc')->orderBy('id', 'desc')->get();
        return view('admin.shipping_policies.index', compact('policies'));
    }

    public function create()
    {
        return view('admin.shipping_policies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'criteria_type' => 'required|in:cart_count,cart_price',
            'from_value' => 'required|numeric|min:0',
            'from_operator' => 'required|in:<,<=,>,>=',
            'to_value' => 'nullable|numeric|min:0',
            'to_operator' => 'nullable|in:<,<=,>,>=',
            'delivery_type' => 'required|in:free,custom',
            'charge_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'priority' => 'nullable|integer|min:0',
        ]);

        $validated['charge_amount'] = ($validated['delivery_type'] === 'free') ? 0.00 : ($validated['charge_amount'] ?? 0.00);
        $validated['priority'] = $validated['priority'] ?? 0;

        ShippingPolicy::create($validated);

        return redirect()->route('admin.shipping-policies.index')->with('success', 'Shipping policy created successfully!');
    }

    public function edit(ShippingPolicy $shippingPolicy)
    {
        return view('admin.shipping_policies.edit', compact('shippingPolicy'));
    }

    public function update(Request $request, ShippingPolicy $shippingPolicy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'criteria_type' => 'required|in:cart_count,cart_price',
            'from_value' => 'required|numeric|min:0',
            'from_operator' => 'required|in:<,<=,>,>=',
            'to_value' => 'nullable|numeric|min:0',
            'to_operator' => 'nullable|in:<,<=,>,>=',
            'delivery_type' => 'required|in:free,custom',
            'charge_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'priority' => 'nullable|integer|min:0',
        ]);

        $validated['charge_amount'] = ($validated['delivery_type'] === 'free') ? 0.00 : ($validated['charge_amount'] ?? 0.00);
        $validated['priority'] = $validated['priority'] ?? 0;

        $shippingPolicy->update($validated);

        return redirect()->route('admin.shipping-policies.index')->with('success', 'Shipping policy updated successfully!');
    }

    public function destroy(ShippingPolicy $shippingPolicy)
    {
        $shippingPolicy->delete();
        return redirect()->route('admin.shipping-policies.index')->with('success', 'Shipping policy deleted successfully!');
    }
}
