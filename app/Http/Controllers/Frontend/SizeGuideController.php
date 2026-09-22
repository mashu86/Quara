<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SizeMaster;
use Illuminate\Http\Request;

class SizeGuideController extends Controller
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

        return view('frontend.size_guide', compact('siteName', 'logoUrl', 'sizeMasters', 'selectedMaster'));
    }
}
