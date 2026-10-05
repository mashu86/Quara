document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('qw-track-hero');
    if (!track) return;

    const feature = track.closest('.qw-hero-feature');
    const stage = feature.querySelector('.qw-hero-stage');
    const slides = [...track.querySelectorAll('.qw-hero-card')];
    const dots = feature.querySelector('[data-dots-for]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const loop = track.dataset.loop !== '0';
    const autoplay = track.dataset.autoplay !== '0';
    const interval = Math.max(1500, Number(track.dataset.interval) || 5000);
    let current = 0;
    let timer;
    let paused = reducedMotion.matches;
    let hovered = false;
    let touchStart = null;

    track.style.setProperty('--hero-speed', `${Number(track.dataset.speed) || 600}ms`);

    function fitActiveImage() {
        const image = slides[current]?.querySelector('img');
        if (image?.naturalWidth && image.naturalHeight) {
            feature.style.setProperty('--hero-image-ratio', String(image.naturalWidth / image.naturalHeight));
        }
    }

    function stop() {
        window.clearTimeout(timer);
    }

    function schedule() {
        stop();
        if (!autoplay || paused || hovered || document.hidden || slides.length < 2) return;
        if (!loop && current === slides.length - 1) return;
        timer = window.setTimeout(() => goTo(current + 1), interval);
    }

    function goTo(index) {
        current = loop
            ? (index + slides.length) % slides.length
            : Math.max(0, Math.min(slides.length - 1, index));
        const activeImage = slides[current]?.querySelector('img');
        if (activeImage) activeImage.loading = 'eager';
        fitActiveImage();
        track.style.transform = `translateX(-${current * 100}%)`;
        slides.forEach((slide, i) => {
            const active = i === current;
            slide.classList.toggle('is-active', active);
            slide.inert = !active;
            slide.setAttribute('aria-hidden', String(!active));
        });
        dots?.querySelectorAll('button').forEach((button, i) => {
            button.classList.toggle('active', i === current);
            button.setAttribute('aria-current', String(i === current));
        });
        feature.querySelectorAll('[data-hero-dir]').forEach(button => {
            button.disabled = !loop && (Number(button.dataset.heroDir) < 0 ? current === 0 : current === slides.length - 1);
        });
        schedule();
    }

    slides.forEach((slide, i) => {
        slide.querySelector('img')?.addEventListener('load', () => {
            if (i === current) fitActiveImage();
        });
        if (!dots) return;
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-label', `Go to banner ${i + 1}`);
        button.setAttribute('aria-controls', track.id);
        button.addEventListener('click', () => goTo(i));
        dots.append(button);
    });

    feature.querySelectorAll('[data-hero-dir]').forEach(button => {
        button.addEventListener('click', () => goTo(current + Number(button.dataset.heroDir)));
    });
    if (track.dataset.pauseHover !== '0') {
        feature.addEventListener('mouseenter', () => { hovered = true; stop(); });
        feature.addEventListener('mouseleave', () => { hovered = false; schedule(); });
    }
    feature.addEventListener('focusin', stop);
    feature.addEventListener('focusout', () => window.setTimeout(schedule, 0));
    feature.addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        // Move focus out of a slide before it becomes inert.
        if (track.contains(document.activeElement)) {
            const control = feature.querySelector('[data-hero-dir]:not(:disabled), [data-dots-for] button');
            if (!control) return;
            control.focus();
        }
        goTo(current + (event.key === 'ArrowRight' ? 1 : -1));
    });
    stage.addEventListener('touchstart', event => {
        touchStart = event.touches.length === 1 ? event.touches[0] : null;
        stop();
    }, { passive: true });
    stage.addEventListener('touchend', event => {
        if (touchStart) {
            const dx = event.changedTouches[0].clientX - touchStart.clientX;
            const dy = event.changedTouches[0].clientY - touchStart.clientY;
            if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) goTo(current + (dx < 0 ? 1 : -1));
        }
        touchStart = null;
        schedule();
    }, { passive: true });
    stage.addEventListener('touchcancel', () => { touchStart = null; schedule(); }, { passive: true });
    document.addEventListener('visibilitychange', schedule);
    // A page restored from the back/forward cache does not fire DOMContentLoaded
    // again. Restart the timer when the browser returns to this page.
    window.addEventListener('pageshow', schedule);
    reducedMotion.addEventListener('change', event => {
        paused = event.matches;
        schedule();
    });

    goTo(0);
});
