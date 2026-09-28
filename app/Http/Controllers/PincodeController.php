<?php

namespace App\Http\Controllers;

use App\Services\PincodeService;
use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function __invoke(Request $request, PincodeService $pins)
    {
        $data = $request->validate(['pin_code' => 'required|regex:/^[1-9][0-9]{5}$/']);

        return response()->json($pins->lookup($data['pin_code']));
    }
}
