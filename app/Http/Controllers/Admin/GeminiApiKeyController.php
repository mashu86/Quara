<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeminiApiKey;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class GeminiApiKeyController extends Controller
{
    /**
     * Display a listing of Gemini API Keys.
     */
    public function index()
    {
        $keys = GeminiApiKey::query()
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $activeKey = $keys->firstWhere('is_active', true);

        return view('admin.gemini_keys.index', [
            'keys' => $keys,
            'activeKey' => $activeKey,
        ]);
    }

    /**
     * Store a newly created Gemini API Key in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'api_key' => ['required', 'string', 'max:1000'],
            'set_active' => ['nullable', 'boolean'],
        ]);

        $rawKey = trim($validated['api_key']);
        $encryptedKey = Crypt::encryptString($rawKey);
        $isFirstKey = GeminiApiKey::count() === 0;
        $shouldMakeActive = $isFirstKey || !empty($validated['set_active']);

        $geminiKey = GeminiApiKey::create([
            'name' => trim($validated['name']),
            'api_key' => $encryptedKey,
            'is_active' => $shouldMakeActive,
        ]);

        if ($shouldMakeActive) {
            $geminiKey->setActive();
        }

        $msg = "Gemini API Key '{$geminiKey->name}' added successfully.";
        if (!str_starts_with($rawKey, 'AIzaSy')) {
            $msg .= " ⚠️ Warning: The key entered starts with '" . substr($rawKey, 0, 8) . "...'. Real Google Gemini API keys start with 'AIzaSy...'. Please ensure you copied your key from https://aistudio.google.com/app/apikey.";
        }

        return redirect()
            ->route('admin.gemini-keys.index')
            ->with('success', $msg);
    }

    /**
     * Update the specified Gemini API Key in storage.
     */
    public function update(Request $request, GeminiApiKey $geminiKey)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'api_key' => ['nullable', 'string', 'max:1000'],
        ]);

        $geminiKey->name = trim($validated['name']);

        $warningMsg = '';
        if (!empty($validated['api_key'])) {
            $rawKey = trim($validated['api_key']);
            $geminiKey->api_key = Crypt::encryptString($rawKey);
            if (!str_starts_with($rawKey, 'AIzaSy')) {
                $warningMsg = " ⚠️ Warning: Key starts with '" . substr($rawKey, 0, 8) . "...'. Real Google Gemini API keys start with 'AIzaSy...' from https://aistudio.google.com/app/apikey.";
            }
        }

        $geminiKey->save();

        if ($geminiKey->is_active) {
            Setting::set('gemini_api_key', $geminiKey->api_key, 'ai');
        }

        return redirect()
            ->route('admin.gemini-keys.index')
            ->with('success', "Gemini API Key '{$geminiKey->name}' updated successfully." . $warningMsg);
    }

    /**
     * Set the specified Gemini API Key as the active key.
     */
    public function activate(GeminiApiKey $geminiKey)
    {
        $geminiKey->setActive();

        return redirect()
            ->route('admin.gemini-keys.index')
            ->with('success', "Active Gemini API Key changed to '{$geminiKey->name}'.");
    }

    /**
     * Remove the specified Gemini API Key from storage.
     */
    public function destroy(GeminiApiKey $geminiKey)
    {
        $wasActive = $geminiKey->is_active;
        $name = $geminiKey->name;
        $geminiKey->delete();

        if ($wasActive) {
            $nextKey = GeminiApiKey::query()->orderByDesc('id')->first();
            if ($nextKey) {
                $nextKey->setActive();
            } else {
                Setting::set('gemini_api_key', '', 'ai');
            }
        }

        return redirect()
            ->route('admin.gemini-keys.index')
            ->with('success', "Gemini API Key '{$name}' deleted successfully.");
    }
}
