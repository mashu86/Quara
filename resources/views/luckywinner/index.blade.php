@extends('luckywinner.layout')

@section('content')
<div class="lw-intro">
    <div><p class="lw-eyebrow"><span class="lw-spark">✦</span> GOOD THINGS ARE COMING</p><h1>A little luck.<br><em>A lovely surprise.</em></h1></div>
    <p>Your customers made your story special.<br>Now it’s their turn to be celebrated.</p>
</div>

<div id="lw-error" class="lw-error" role="alert" hidden></div>

<section class="lw-setup" id="lw-setup" aria-label="Set up a giveaway">
    <form id="lw-period-form">
        <div class="lw-setup-title"><span class="lw-step">01</span><div><h2>Choose your moment</h2><p>Select the orders for today’s giveaway.</p></div></div>
        <div class="lw-segment" role="group" aria-label="Draw period type">
            <label><input type="radio" name="draw_type" value="month" checked><span>By month</span></label>
            <label><input type="radio" name="draw_type" value="range"><span>Date range</span></label>
        </div>
        <div id="lw-month-field" class="lw-field"><label for="lw-month">Giveaway month</label><select id="lw-month" name="month">@foreach($months as $month)<option value="{{ $month['value'] }}">{{ $month['label'] }}</option>@endforeach</select></div>
        <div id="lw-range-fields" class="lw-date-fields" hidden>
            <div class="lw-field"><label for="lw-start">Start date</label><input type="date" id="lw-start" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}"></div>
            <div class="lw-field"><label for="lw-end">End date</label><input type="date" id="lw-end" name="end_date" value="{{ now()->toDateString() }}"></div>
        </div>
        <button class="lw-button lw-dark" id="lw-load" type="submit">Load eligible orders <span aria-hidden="true">↗</span></button>
    </form>
    <div class="lw-setup-note"><span class="lw-status-dot"></span> Paid, confirmed purchases · Website + manual orders · One order, one entry</div>
</section>

<section class="lw-studio" id="lw-studio" aria-label="Lucky Winner draw studio">
    <div class="lw-stage" id="lw-stage">
        <div class="lw-stage-grain" aria-hidden="true"></div>
        <div class="lw-stage-top">
            <div class="lw-stage-brand"><img src="{{ $siteLogoUrl }}" alt=""><span>{{ $siteName }}</span></div>
            <button type="button" class="lw-icon-button" id="lw-fullscreen" aria-label="Enter full screen" title="Enter full screen">⛶</button>
        </div>
        <div class="lw-live"><span></span> THE LUCKY WINNER EXPERIENCE</div>
        <h2 class="lw-game-title">Lucky <em>Winner</em><span aria-hidden="true">✦</span></h2>
        <p class="lw-stage-period" id="lw-stage-period">A special surprise awaits</p>

        <div class="lw-machine" id="lw-machine">
            <div class="lw-orbit lw-orbit-one" aria-hidden="true"></div><div class="lw-orbit lw-orbit-two" aria-hidden="true"></div>
            <span class="lw-floating-star lw-star-one" aria-hidden="true">✦</span><span class="lw-floating-star lw-star-two" aria-hidden="true">✧</span>
            <div class="lw-gift" id="lw-gift" aria-hidden="true"><div class="lw-bow"></div><div class="lw-gift-lid"></div><div class="lw-gift-box"></div></div>
            <div class="lw-reel" id="lw-reel" hidden>
                <div class="lw-reel-ghost" id="lw-reel-before" aria-hidden="true"></div>
                <div class="lw-reel-window"><span class="lw-reel-marker" aria-hidden="true">›</span><div><p class="lw-reveal-caption" id="lw-reveal-caption">YOUR NEXT LUCKY WINNER</p><h3 id="lw-reel-name"></h3><p id="lw-reel-order"></p><p id="lw-reel-address" class="lw-reel-address" style="display: none;"></p><p id="lw-reel-contact" class="lw-reel-contact" style="display: none;"></p></div><span class="lw-reel-marker" aria-hidden="true">‹</span></div>
                <div class="lw-reel-ghost" id="lw-reel-after" aria-hidden="true"></div>
            </div>
            <div id="lw-final" class="lw-final" hidden><p class="lw-reveal-caption">A ROUND OF APPLAUSE FOR</p><h3>Our lucky winners</h3><div id="lw-final-list"></div></div>
        </div>

        <div class="lw-stage-stats"><div><strong id="lw-entry-count">—</strong><span>SUCCESSFUL ORDERS</span></div><i></i><div><strong id="lw-gift-count">—</strong><span>GIFTS TO GIVE</span></div><i></i><div><strong id="lw-winner-count">0</strong><span>WINNERS REVEALED</span></div></div>
        <div class="lw-stage-action"><button type="button" class="lw-button lw-gold" id="lw-draw" disabled><span aria-hidden="true">✦</span> Start lucky draw <span aria-hidden="true">→</span></button><p id="lw-stage-hint" role="status" aria-live="polite">Choose a period to unlock the magic.</p></div>
        <div class="lw-celebration" id="lw-celebration" aria-hidden="true"></div>
        <div class="lw-stage-bottom"><span>MADE POSSIBLE BY YOU</span><span>WITH LOVE, {{ $siteName }}</span></div>
    </div>

    <aside class="lw-sidebar">
        <div class="lw-gifts-panel"><div class="lw-panel-heading"><span class="lw-step">02</span><h2>Make someone’s day</h2></div><label for="lw-gifts">How many gifts?</label><div class="lw-gift-input"><span aria-hidden="true">✧</span><input type="number" id="lw-gifts" min="1" step="1" value="1" disabled><span>winners</span></div><p>Every winning order gets one gift.<br>The next surprise could be theirs.</p></div>
        <div class="lw-participants-panel"><div class="lw-panel-heading"><h2>The lucky crowd</h2><span class="lw-count-pill" id="lw-participant-count">0</span></div><p>One successful order. One chance.</p><label class="lw-search"><span aria-hidden="true">⌕</span><input type="search" id="lw-search" placeholder="Find a name or order" aria-label="Search participants" disabled></label><div class="lw-participant-list" id="lw-participants"><div class="lw-empty"><span>✧</span><strong>Your crowd is waiting</strong><p>Load a period to meet<br>your giveaway participants.</p></div></div><div class="lw-list-pager" id="lw-list-pager" hidden><button type="button" id="lw-prev" aria-label="Previous participants">←</button><span id="lw-page-info"></span><button type="button" id="lw-next" aria-label="Next participants">→</button></div></div>
    </aside>
</section>

<section class="lw-save-panel" id="lw-save-panel" hidden>
    <div><p class="lw-eyebrow">A MOMENT WORTH KEEPING</p><h2 id="lw-save-title">The winners are ready. Make it official.</h2><p id="lw-save-description">Store this draw to add these winners to your permanent archive.</p></div>
    <button type="button" class="lw-button lw-dark" id="lw-store">Store winners <span aria-hidden="true">↗</span></button>
    <a id="lw-saved-link" class="lw-button lw-dark" hidden>View saved draw ↗</a>
</section>
<div class="lw-after-controls"><button type="button" class="lw-text-button" id="lw-new" hidden>＋ Start a new lucky draw</button><p>Each order can win once per draw. Customers with more orders have more entries.</p></div>
<details class="lw-rules"><summary>How entries &amp; selection work <span>＋</span></summary><div><p>Paid orders with Confirmed, Processing, Packed, Shipped or Delivered status are eligible. Dates use the sale date, or order creation date when no sale date is set, in {{ config('luckywinner.timezone') }}. Same-day ranges include the entire day. Test orders are excluded.</p><p>Active return activity in this period reduces the weight of that order and other entries matching the customer’s phone, email or account. Current weights: normal {{ config('luckywinner.normal_weight') }}, return {{ min(config('luckywinner.normal_weight'), max(1, config('luckywinner.return_weight'))) }}. Weighting applies once, regardless of the number of returns.</p><p>The server selects randomly from the complete weighted pool; animation only presents the result. Entries are checked again when the first winner is selected, then frozen for that draw. Temporary draws can be resumed for {{ config('luckywinner.draft_lifetime_hours') }} hours. Only Store Winners creates permanent history.</p></div></details>
@endsection

@section('scripts')
<script>window.luckyWinner = {{ Illuminate\Support\Js::from(['prepareUrl' => route('luckywinner.prepare'), 'baseUrl' => route('luckywinner.index'), 'activeDraft' => $activeDraft]) }};</script>
<script src="{{ asset('js/luckywinner.js') }}?v=1" defer></script>
@endsection
