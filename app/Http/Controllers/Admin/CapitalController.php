<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Capital;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CapitalController extends Controller
{
    public function index(Request $request)
    {
        $query = Capital::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('capital_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('capital_date', '<=', $request->end_date);
        }

        $totalCapital = (float) Capital::sum('amount');
        $filteredCapitalTotal = (float) (clone $query)->sum('amount');
        $capitalCount = Capital::count();

        $capitals = $query->orderBy('capital_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.capitals.index', compact(
            'capitals',
            'totalCapital',
            'filteredCapitalTotal',
            'capitalCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capital_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        Capital::create($validated);

        return redirect()->route('admin.capitals.index')->with('success', 'Capital investment recorded successfully!');
    }

    public function update(Request $request, Capital $capital)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capital_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $capital->update($validated);

        return redirect()->route('admin.capitals.index')->with('success', 'Capital investment updated successfully!');
    }

    public function destroy(Capital $capital)
    {
        $capital->delete();

        return redirect()->route('admin.capitals.index')->with('success', 'Capital investment deleted successfully.');
    }
}
