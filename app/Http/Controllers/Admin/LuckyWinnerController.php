<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyDraw;
use App\Services\LuckyWinnerDrafts;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LuckyWinnerController extends Controller
{
    public function index(Request $request, LuckyWinnerDrafts $drafts)
    {
        $months = collect(range(0, 3))->map(function ($offset) {
            $month = CarbonImmutable::now(config('luckywinner.timezone'))->startOfMonth()->subMonths($offset);

            return ['value' => $month->format('Y-m'), 'label' => $month->format('F Y')];
        });
        $activeDraft = null;
        if ($token = $request->session()->get('luckywinner.active_draft')) {
            try {
                $activeDraft = $drafts->publicState($drafts->get($token, $request->user()));
            } catch (HttpException $exception) {
                if (! in_array($exception->getStatusCode(), [403, 410])) {
                    throw $exception;
                }
                $request->session()->forget('luckywinner.active_draft');
            }
        }

        return view('luckywinner.index', compact('months', 'activeDraft'));
    }

    public function prepare(Request $request, LuckyWinnerDrafts $drafts)
    {
        $input = $request->validate([
            'draw_type' => 'required|in:month,range',
            'month' => 'required_if:draw_type,month|nullable|date_format:Y-m',
            'start_date' => 'required_if:draw_type,range|nullable|date_format:Y-m-d',
            'end_date' => 'required_if:draw_type,range|nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        $draft = $drafts->create($input, $request->user());
        $request->session()->put('luckywinner.active_draft', $draft['token']);

        return response()->json($drafts->publicState($draft));
    }

    public function select(Request $request, string $token, LuckyWinnerDrafts $drafts)
    {
        $input = $request->validate(['gift_count' => 'required|integer|min:1', 'position' => 'required|integer|min:1']);

        return response()->json($drafts->publicState($drafts->select($token, $request->user(), (int) $input['gift_count'], (int) $input['position'])));
    }

    public function store(Request $request, string $token, LuckyWinnerDrafts $drafts)
    {
        return response()->json($drafts->storedResponse($drafts->store($token, $request->user())));
    }

    public function history()
    {
        $draws = LuckyDraw::with('winners')->orderBy('drawn_at', 'desc')->orderBy('id', 'desc')->paginate(15);
        $showLastLuckyDraw = \App\Models\Setting::get('show_last_lucky_draw', '0');
        $totalDrawsCount = LuckyDraw::count();

        return view('admin.luckywinner.history', compact('draws', 'showLastLuckyDraw', 'totalDrawsCount'));
    }

    public function toggleVisibility(Request $request)
    {
        $totalDrawsCount = LuckyDraw::count();
        if ($totalDrawsCount === 0) {
            \App\Models\Setting::set('show_last_lucky_draw', '0');
            return response()->json([
                'success' => false,
                'message' => 'ഇതുവരെ Lucky Draw ഒന്നും എടുത്തിട്ടില്ല (No Lucky Draw Conducted Yet).'
            ], 422);
        }

        $request->validate([
            'show_last_lucky_draw' => 'required|in:0,1',
        ]);

        $status = $request->input('show_last_lucky_draw');
        \App\Models\Setting::set('show_last_lucky_draw', $status);

        $msg = $status === '1'
            ? 'Last Lucky Draw winners visibility enabled on client site!'
            : 'Last Lucky Draw winners visibility disabled on client site!';

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $msg
        ]);
    }

    public function show(LuckyDraw $draw)
    {
        $draw->load('winners');

        return view('admin.luckywinner.show', compact('draw'));
    }

    public function destroy(Request $request, LuckyDraw $draw, LuckyWinnerDrafts $drafts)
    {
        $drafts->delete($draw);

        if ($request->session()->get('luckywinner.active_draft') === $draw->draft_token) {
            $request->session()->forget('luckywinner.active_draft');
        }

        return redirect()->route('admin.luckywinner.history')
            ->with('success', 'Lucky draw and its winner history deleted successfully.');
    }

    public function updateTitle(Request $request, LuckyDraw $draw)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $draw->update([
            'title' => $validated['title'] ? trim($validated['title']) : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lucky draw title updated successfully!',
                'title' => $draw->title,
                'display_title' => $draw->display_title,
            ]);
        }

        return redirect()->back()->with('success', 'Lucky draw name/title updated successfully!');
    }
}
