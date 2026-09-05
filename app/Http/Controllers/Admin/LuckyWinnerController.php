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
        $draws = LuckyDraw::with('winners')->latest('id')->paginate(12);

        return view('admin.luckywinner.history', compact('draws'));
    }

    public function show(LuckyDraw $draw)
    {
        $draw->load('winners');

        return view('admin.luckywinner.show', compact('draw'));
    }
}
