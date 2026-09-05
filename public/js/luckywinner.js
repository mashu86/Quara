(() => {
    'use strict';
    const config = window.luckyWinner;
    const $ = id => document.getElementById(`lw-${id}`);
    let state = config.activeDraft;
    let busy = false;
    let finalVisible = false;
    let participantPage = 0;
    let confettiTimer;
    const pageSize = 50;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const text = (tag, value, className) => {
        const node = document.createElement(tag);
        node.textContent = value;
        if (className) node.className = className;
        return node;
    };
    function error(message) {
        $('error').textContent = message;
        $('error').hidden = !message;
        if (message) $('stage-hint').textContent = message;
    }
    async function post(url, data = {}) {
        const controller = new AbortController();
        const timer = window.setTimeout(() => controller.abort(), 30000);
        try {
            const response = await fetch(url, {
                method: 'POST', credentials: 'same-origin', signal: controller.signal,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(data),
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (response.status === 419) throw new Error('Your session has expired. Refresh and sign in again to continue.');
                throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'Something went wrong. Please try again.');
            }
            return payload;
        } catch (exception) {
            if (exception.name === 'AbortError' || exception instanceof TypeError) {
                throw new Error('Connection interrupted. Retry this step; an already selected winner will be kept.');
            }
            throw exception;
        } finally { window.clearTimeout(timer); }
    }
    const draftUrl = action => `${config.baseUrl.replace(/\/$/, '')}/draft/${state.token}/${action}`;
    function setBusy(value) {
        busy = value;
        $('load').disabled = value || Boolean(state);
        $('gifts').disabled = value || !state || Boolean(state.gift_count);
        $('draw').disabled = value || !state || finalVisible;
        $('store').disabled = value || Boolean(state?.stored);
        $('new').disabled = value;
    }
    function renderParticipants() {
        const container = $('participants');
        if (!state) return;
        const query = $('search').value.trim().toLocaleLowerCase();
        const participants = query ? state.entries.filter(entry => `${entry.customer_name} ${entry.order_number}`.toLocaleLowerCase().includes(query)) : state.entries;
        const totalPages = Math.max(1, Math.ceil(participants.length / pageSize));
        participantPage = Math.min(participantPage, totalPages - 1);
        const winningIds = new Set(state.winners.map(winner => winner.order_id));
        const fragment = document.createDocumentFragment();
        for (const entry of participants.slice(participantPage * pageSize, (participantPage + 1) * pageSize)) {
            const isWinner = winningIds.has(entry.order_id);
            const row = text('div', '', `lw-participant${isWinner ? ' is-winner' : ''}`);
            const initials = entry.customer_name.trim().split(/\s+/).slice(0, 2).map(name => Array.from(name)[0] || '').join('').toUpperCase();
            row.append(text('span', initials, 'lw-avatar'));
            const name = text('div', '', 'lw-participant-name');
            const contactInfo = [entry.masked_phone, entry.customer_address].filter(Boolean).join(' · ');
            name.append(text('strong', entry.customer_name), text('small', `${entry.order_number}${contactInfo ? ' · ' + contactInfo : ''}`));
            row.append(name);
            if (isWinner) row.append(text('span', 'WINNER', 'lw-winner-tag'));
            fragment.append(row);
        }
        if (!participants.length) fragment.append(text('div', 'No matching entries.', 'lw-empty'));
        container.replaceChildren(fragment);
        container.scrollTop = 0;
        $('list-pager').hidden = participants.length <= pageSize;
        $('page-info').textContent = `${participantPage * pageSize + 1}–${Math.min((participantPage + 1) * pageSize, participants.length)} of ${participants.length}`;
        $('prev').disabled = participantPage === 0;
        $('next').disabled = participantPage >= totalPages - 1;
    }
    function configurePeriod() {
        const range = $('period-form').elements.draw_type.value === 'range';
        $('month-field').hidden = range;
        $('range-fields').hidden = !range;
        $('month').disabled = range || Boolean(state);
        $('start').disabled = !range || Boolean(state);
        $('end').disabled = !range || Boolean(state);
        $('start').required = range;
        $('end').required = range;
        $('end').min = $('start').value;
    }
    function renderState() {
        if (!state) return;
        $('period-form').elements.draw_type.value = state.period.draw_type;
        $('start').value = state.period.start_date;
        $('end').value = state.period.end_date;
        if (state.period.selected_month) {
            if (![...$('month').options].some(option => option.value === state.period.selected_month)) {
                $('month').add(new Option(state.period.period_label, state.period.selected_month));
            }
            $('month').value = state.period.selected_month;
        }
        configurePeriod();
        $('stage-period').textContent = state.period.period_label;
        $('entry-count').textContent = state.total_entries.toLocaleString();
        $('participant-count').textContent = state.total_entries.toLocaleString();
        $('winner-count').textContent = state.winners.length;
        $('gifts').max = state.total_entries;
        if (state.gift_count) $('gifts').value = state.gift_count;
        $('gift-count').textContent = $('gifts').value;
        $('search').disabled = false;
        $('new').hidden = false;
        for (const input of $('period-form').querySelectorAll('input,select')) input.disabled = true;
        $('load').textContent = '✓ Entries loaded';
        renderParticipants();
        if (state.winners.length) {
            showWinner(state.winners[state.winners.length - 1], false);
        } else {
            $('stage-hint').textContent = 'Your crowd is ready. Let’s make someone’s day.';
        }
        setBusy(false);
    }
    function showWinner(winner, celebrate = true) {
        $('gift').hidden = true;
        $('reel').hidden = false;
        $('stage').classList.remove('is-shuffling');
        $('stage').classList.add('is-revealed');
        $('reveal-caption').textContent = `CONGRATULATIONS · WINNER ${winner.position}`;
        $('reel-name').textContent = winner.customer_name;
        $('reel-order').textContent = winner.order_number;
        if ($('reel-address')) {
            const addr = winner.customer_address || '';
            $('reel-address').textContent = addr;
            $('reel-address').style.display = addr ? 'block' : 'none';
        }
        if ($('reel-contact')) {
            const contact = [winner.masked_phone, winner.masked_email].filter(Boolean).join(' · ');
            $('reel-contact').textContent = contact;
            $('reel-contact').style.display = contact ? 'block' : 'none';
        }
        $('winner-count').textContent = state.winners.length;
        $('draw').textContent = state.winners.length === state.gift_count ? 'See all winners ✦' : 'Next winner →';
        $('stage-hint').textContent = `A lovely surprise for ${winner.customer_name}.`;
        if (celebrate) confetti();
    }
    function confetti() {
        if (reducedMotion.matches) return;
        window.clearTimeout(confettiTimer);
        const fragment = document.createDocumentFragment();
        const colors = ['#edc781', '#cfb3e0', '#fff0ce', '#b89bca', '#d98b78'];
        for (let i = 0; i < 65; i++) {
            const piece = text('i', '', 'lw-confetti');
            piece.style.setProperty('--confetti-left', `${Math.random() * 100}%`);
            piece.style.setProperty('--confetti-color', colors[i % colors.length]);
            piece.style.setProperty('--fall-delay', `${Math.random() * 1.1}s`);
            piece.style.setProperty('--fall-duration', `${2.4 + Math.random() * 2}s`);
            piece.style.setProperty('--confetti-rotation', `${Math.random() * 360}deg`);
            fragment.append(piece);
        }
        $('celebration').replaceChildren(fragment);
        confettiTimer = window.setTimeout(() => $('celebration').replaceChildren(), 6000);
    }
    function showFinal() {
        finalVisible = true;
        $('stage').classList.add('is-final');
        $('gift').hidden = true;
        $('reel').hidden = true;
        $('final').hidden = false;
        const fragment = document.createDocumentFragment();
        for (const winner of state.winners) {
            const card = text('div', '', 'lw-final-winner');
            const contactStr = [winner.masked_phone, winner.masked_email].filter(Boolean).join(' · ');
            card.append(
                text('span', `WINNER ${String(winner.position).padStart(2, '0')}`),
                text('strong', winner.customer_name),
                text('small', winner.order_number + (winner.customer_address ? ` · ${winner.customer_address}` : '')),
                contactStr ? text('div', contactStr, 'lw-contact-muted') : text('span', '')
            );
            fragment.append(card);
        }
        $('final-list').replaceChildren(fragment);
        $('save-panel').hidden = false;
        $('draw').textContent = 'Our lucky winners ✦';
        $('draw').disabled = true;
        $('stage-hint').textContent = inPresentation() ? 'Exit full screen to store your winners.' : 'A little luck. A moment to remember.';
        if (state.stored) showStored();
        confetti();
    }
    function showStored() {
        $('store').disabled = true;
        $('store').hidden = true;
        $('saved-link').hidden = false;
        $('saved-link').href = state.stored.url;
        $('save-title').textContent = 'Lucky draw saved successfully.';
        $('save-description').textContent = `${state.stored.draw_number} · Your winners are safely in the archive.`;
    }
    function animate(winner, pool) {
        // Decorative names never determine the result. The server already chose it.
        const duration = reducedMotion.matches ? 400 : 6100;
        const start = performance.now();
        let lastChange = -Infinity;
        $('stage').classList.remove('is-revealed');
        $('stage').classList.add('is-shuffling');
        $('reveal-caption').textContent = 'A LITTLE LUCK IS ON ITS WAY';
        $('gift').hidden = true;
        $('reel').hidden = false;
        $('draw').textContent = 'Finding a lucky one…';
        return new Promise(resolve => {
            function frame(now) {
                const progress = Math.min(1, (now - start) / duration);
                if (progress >= 1) { resolve(); return; }
                const interval = 55 + 680 * Math.pow(progress, 3.5);
                if (now - lastChange > interval) {
                    lastChange = now;
                    const candidate = progress > .94 ? winner : pool[Math.floor(Math.random() * pool.length)];
                    $('reel-name').textContent = candidate.customer_name;
                    $('reel-order').textContent = candidate.order_number;
                    if ($('reel-address')) {
                        const addr = candidate.customer_address || '';
                        $('reel-address').textContent = addr;
                        $('reel-address').style.display = addr ? 'block' : 'none';
                    }
                    if ($('reel-contact')) {
                        const contact = [candidate.masked_phone, candidate.masked_email].filter(Boolean).join(' · ');
                        $('reel-contact').textContent = contact;
                        $('reel-contact').style.display = contact ? 'block' : 'none';
                    }
                    $('reel-before').textContent = pool[Math.floor(Math.random() * pool.length)].customer_name;
                    $('reel-after').textContent = pool[Math.floor(Math.random() * pool.length)].customer_name;
                    if (progress > .62) {
                        $('reveal-caption').textContent = 'THE MOMENT IS ALMOST HERE';
                        $('stage-hint').textContent = 'One name. One lovely surprise.';
                    }
                }
                requestAnimationFrame(frame);
            }
            requestAnimationFrame(frame);
        });
    }

    $('period-form').addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || state) return;
        error('');
        setBusy(true);
        $('load').textContent = 'Meeting your crowd…';
        try {
            state = await post(config.prepareUrl, Object.fromEntries(new FormData(event.currentTarget)));
            participantPage = 0;
            renderState();
        } catch (exception) {
            $('load').textContent = 'Load eligible orders ↗';
            error(exception.message);
        } finally { setBusy(false); }
    });
    $('draw').addEventListener('click', async () => {
        if (busy || !state || finalVisible) return;
        if (state.gift_count && state.winners.length === state.gift_count) { showFinal(); return; }
        if (!$('gifts').reportValidity()) return;
        const gifts = Number($('gifts').value);
        if (!Number.isInteger(gifts) || gifts < 1 || gifts > state.total_entries) { error('Choose a valid number of gifts, up to the total entries.'); return; }
        error('');
        setBusy(true);
        const oldButton = $('draw').textContent;
        $('draw').textContent = 'The moment is coming…';
        $('stage-hint').textContent = 'Getting ready to reveal a lucky winner.';
        try {
            const winningIds = new Set(state.winners.map(winner => winner.order_id));
            const pool = state.entries.filter(entry => !winningIds.has(entry.order_id));
            const next = await post(draftUrl('select'), { gift_count: gifts, position: state.winners.length + 1 });
            const winner = next.winners[state.winners.length];
            await animate(winner, pool);
            state = next;
            showWinner(winner);
            renderParticipants();
        } catch (exception) {
            $('stage').classList.remove('is-shuffling');
            $('draw').textContent = oldButton;
            error(exception.message);
        } finally { setBusy(false); }
    });
    $('store').addEventListener('click', async () => {
        if (busy || state?.stored || !finalVisible) return;
        setBusy(true);
        error('');
        $('store').textContent = 'Keeping this moment…';
        try {
            state.stored = await post(draftUrl('store'));
            showStored();
        } catch (exception) {
            $('store').textContent = 'Retry storing winners ↗';
            error(exception.message);
        } finally { setBusy(false); }
    });
    $('new').addEventListener('click', () => {
        if (busy) return;
        const message = state?.stored ? 'Start a separate new draw? You can intentionally draw the same period again. Your saved draw will stay in the archive.' : 'Start a new draw? These temporary participants and any unsaved winners will be replaced when you load the new period.';
        if (!window.confirm(message)) return;
        state = null;
        finalVisible = false;
        $('save-panel').hidden = true;
        $('store').hidden = false;
        $('store').textContent = 'Store winners ↗';
        $('saved-link').hidden = true;
        $('save-title').textContent = 'The winners are ready. Make it official.';
        $('save-description').textContent = 'Store this draw to add these winners to your permanent archive.';
        $('new').hidden = true;
        $('gift').hidden = false;
        $('reel').hidden = true;
        $('final').hidden = true;
        $('gifts').value = 1;
        $('search').value = '';
        $('search').disabled = true;
        $('stage').classList.remove('is-shuffling', 'is-revealed', 'is-final');
        $('stage-period').textContent = 'A special surprise awaits';
        $('entry-count').textContent = '—';
        $('gift-count').textContent = '—';
        $('winner-count').textContent = '0';
        $('participant-count').textContent = '0';
        $('participants').replaceChildren(text('div', 'Load a period to meet your next lucky crowd.', 'lw-empty'));
        $('list-pager').hidden = true;
        $('draw').textContent = '✦ Start lucky draw →';
        $('stage-hint').textContent = 'Choose a period to unlock the magic.';
        $('load').textContent = 'Load eligible orders ↗';
        for (const input of $('period-form').querySelectorAll('input,select')) input.disabled = false;
        configurePeriod();
        error('');
        setBusy(false);
        $('setup').scrollIntoView({ behavior: reducedMotion.matches ? 'instant' : 'smooth', block: 'start' });
    });
    function inPresentation() { return Boolean(document.fullscreenElement) || $('stage').classList.contains('lw-presentation'); }
    function updatePresentation() {
        const active = inPresentation();
        $('fullscreen').setAttribute('aria-label', active ? 'Exit full screen' : 'Enter full screen');
        $('fullscreen').title = active ? 'Exit full screen' : 'Enter full screen';
        if (finalVisible) $('stage-hint').textContent = active ? 'Exit full screen to store your winners.' : 'A little luck. A moment to remember.';
    }
    $('fullscreen').addEventListener('click', async () => {
        if (document.fullscreenElement) { await document.exitFullscreen(); return; }
        if ($('stage').classList.contains('lw-presentation')) {
            $('stage').classList.remove('lw-presentation'); document.body.classList.remove('lw-presentation-open');
        } else {
            try {
                if (!$('stage').requestFullscreen) throw new Error('Fullscreen unavailable');
                await $('stage').requestFullscreen();
            } catch {
                $('stage').classList.add('lw-presentation'); document.body.classList.add('lw-presentation-open');
            }
        }
        updatePresentation();
    });
    document.addEventListener('fullscreenchange', updatePresentation);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && $('stage').classList.contains('lw-presentation')) {
            $('stage').classList.remove('lw-presentation'); document.body.classList.remove('lw-presentation-open'); updatePresentation();
        }
    });
    $('period-form').elements.draw_type.forEach(input => input.addEventListener('change', configurePeriod));
    $('start').addEventListener('change', configurePeriod);
    $('gifts').addEventListener('input', () => { $('gift-count').textContent = $('gifts').value || '—'; });
    $('search').addEventListener('input', () => { participantPage = 0; renderParticipants(); });
    $('prev').addEventListener('click', () => { participantPage--; renderParticipants(); });
    $('next').addEventListener('click', () => { participantPage++; renderParticipants(); });
    configurePeriod();
    if (state) {
        renderState();
        if (state.gift_count && state.winners.length === state.gift_count) showFinal();
    }
})();
