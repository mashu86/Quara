<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SizeGuideController extends Controller
{
    public function index()
    {
        $siteName = Setting::get('site_name', config('app.name', 'Quara'));
        $logoUrl = Setting::logoUrl();

        return view('frontend.size_guide', compact('siteName', 'logoUrl'));
    }
}
