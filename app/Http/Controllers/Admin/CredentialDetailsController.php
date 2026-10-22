<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CredentialDetailsController extends Controller
{
    public function index()
    {
        return $this->page();
    }

    public function encrypt(Request $request)
    {
        $validated = $request->validate(['text' => ['required', 'string', 'max:10000']]);

        return $this->page($validated['text'], Crypt::encryptString($validated['text']), 'Encrypted');
    }

    public function decrypt(Request $request)
    {
        $validated = $request->validate(['text' => ['required', 'string', 'max:10000']]);

        try {
            return $this->page($validated['text'], Crypt::decryptString($validated['text']), 'Decrypted');
        } catch (DecryptException) {
            return $this->page($validated['text'], null, null, 'Could not decrypt this value. Check that it was encrypted by this application and that the APP_KEY has not changed.');
        }
    }

    private function page(string $input = '', ?string $result = null, ?string $mode = null, ?string $error = null)
    {
        return response()->view('admin.credential_details.index', compact('input', 'result', 'mode', 'error'))
            ->header('Cache-Control', 'no-store, private');
    }
}
