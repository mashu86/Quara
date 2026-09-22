<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SizeMaster;
use App\Models\SizeMasterRow;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SizeMasterController extends Controller
{
    public function index(Request $request)
    {
        $siteName = Setting::get('site_name', config('app.name', 'Quara'));
        $logoUrl = Setting::logoUrl();

        $sizeMasters = SizeMaster::with('rows')->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        $selectedMasterId = $request->input('category_id');
        $selectedMaster = null;

        if ($selectedMasterId) {
            $selectedMaster = $sizeMasters->firstWhere('id', (int) $selectedMasterId);
        }

        if (!$selectedMaster && $sizeMasters->isNotEmpty()) {
            $selectedMaster = $sizeMasters->first();
        }

        return view('admin.products.size_guide', compact('siteName', 'logoUrl', 'sizeMasters', 'selectedMaster'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:size_masters,name',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $count = 1;
        while (SizeMaster::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        $maxSort = SizeMaster::max('sort_order') ?? 0;

        $master = SizeMaster::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->route('admin.size-guide.index', ['category_id' => $master->id])
            ->with('success', 'Size Category "' . $master->name . '" created successfully.');
    }

    public function updateCategory(Request $request, SizeMaster $sizeMaster)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:size_masters,name,' . $sizeMaster->id,
        ]);

        $sizeMaster->update([
            'name' => trim($validated['name']),
        ]);

        return redirect()->route('admin.size-guide.index', ['category_id' => $sizeMaster->id])
            ->with('success', 'Size Category updated successfully.');
    }

    public function destroyCategory(SizeMaster $sizeMaster)
    {
        $name = $sizeMaster->name;
        $sizeMaster->delete();

        return redirect()->route('admin.size-guide.index')
            ->with('success', 'Size Category "' . $name . '" deleted successfully.');
    }

    public function storeRow(Request $request, SizeMaster $sizeMaster)
    {
        $validated = $request->validate([
            'size_label' => 'required|string|max:50',
            'chest' => 'nullable|string|max:50',
            'waist' => 'nullable|string|max:50',
            'length' => 'nullable|string|max:50',
        ]);

        $maxSort = $sizeMaster->rows()->max('sort_order') ?? 0;

        SizeMasterRow::create([
            'size_master_id' => $sizeMaster->id,
            'size_label' => trim($validated['size_label']),
            'chest' => !empty($validated['chest']) ? trim($validated['chest']) : null,
            'waist' => !empty($validated['waist']) ? trim($validated['waist']) : null,
            'length' => !empty($validated['length']) ? trim($validated['length']) : null,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->route('admin.size-guide.index', ['category_id' => $sizeMaster->id])
            ->with('success', 'Size row added to ' . $sizeMaster->name . '.');
    }

    public function updateRow(Request $request, SizeMasterRow $row)
    {
        $validated = $request->validate([
            'size_label' => 'required|string|max:50',
            'chest' => 'nullable|string|max:50',
            'waist' => 'nullable|string|max:50',
            'length' => 'nullable|string|max:50',
        ]);

        $row->update([
            'size_label' => trim($validated['size_label']),
            'chest' => !empty($validated['chest']) ? trim($validated['chest']) : null,
            'waist' => !empty($validated['waist']) ? trim($validated['waist']) : null,
            'length' => !empty($validated['length']) ? trim($validated['length']) : null,
        ]);

        return redirect()->route('admin.size-guide.index', ['category_id' => $row->size_master_id])
            ->with('success', 'Size row updated.');
    }

    public function destroyRow(SizeMasterRow $row)
    {
        $masterId = $row->size_master_id;
        $row->delete();

        return redirect()->route('admin.size-guide.index', ['category_id' => $masterId])
            ->with('success', 'Size row removed.');
    }

    public function getChartJson(SizeMaster $sizeMaster)
    {
        $sizeMaster->load('rows');
        return response()->json([
            'id' => $sizeMaster->id,
            'name' => $sizeMaster->name,
            'slug' => $sizeMaster->slug,
            'rows' => $sizeMaster->rows,
        ]);
    }
}
