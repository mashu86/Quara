(() => {
    const chart = document.getElementById('business-statistics-chart');
    if (!chart) return;

    const days = JSON.parse(document.getElementById('business-statistics-data').textContent);
    const form = document.getElementById('business-statistics-form');
    const period = document.getElementById('statistics-period');
    const start = document.getElementById('statistics-start');
    const end = document.getElementById('statistics-end');
    const detail = document.getElementById('business-statistics-detail');
    const checkboxes = Array.from(form.querySelectorAll('[data-statistics-metric]'));
    const metrics = {
        sales: { label: 'Sales', color: '#2563eb' },
        expense: { label: 'Expense', color: '#dc2626' },
        revenue: { label: 'Revenue', color: '#16a34a' },
    };
    const currency = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 });
    const shortCurrency = new Intl.NumberFormat('en-IN', { notation: 'compact', maximumFractionDigits: 1 });
    const dateLabel = (date) => new Date(`${date}T00:00:00`).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    const selected = () => checkboxes.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    const element = (tag, attributes, text) => {
        const node = document.createElementNS('http://www.w3.org/2000/svg', tag);
        Object.entries(attributes).forEach(([name, value]) => node.setAttribute(name, value));
        if (text !== undefined) node.textContent = text;
        chart.appendChild(node);
        return node;
    };
    const left = 82;
    const right = 930;
    const top = 24;
    const bottom = 270;
    const position = (index) => days.length === 1 ? (left + right) / 2 : left + index * (right - left) / (days.length - 1);
    let cursor;

    const showDay = (index) => {
        const active = selected();
        const day = days[index];
        if (!day || !active.length) return;
        cursor.setAttribute('x1', position(index));
        cursor.setAttribute('x2', position(index));
        cursor.setAttribute('visibility', 'visible');
        detail.textContent = `${dateLabel(day.date)} — ${active.map((metric) => `${metrics[metric].label}: ${currency.format(day[metric])}`).join(' · ')}`;
        chart.setAttribute('aria-valuetext', detail.textContent);
    };

    const draw = () => {
        chart.replaceChildren();
        const active = selected();
        const values = days.flatMap((day) => active.map((metric) => day[metric]));
        const minimum = values.reduce((lowest, value) => Math.min(lowest, value), 0);
        const maximum = values.reduce((highest, value) => Math.max(highest, value), 0);
        const lower = minimum < 0 ? minimum * 1.1 : 0;
        const upper = maximum > 0 ? maximum * 1.1 : 1;
        const height = (value) => bottom - (value - lower) / (upper - lower) * (bottom - top);

        for (let tick = 0; tick <= 4; tick += 1) {
            const value = lower + (upper - lower) * tick / 4;
            const vertical = height(value);
            element('line', { x1: left, x2: right, y1: vertical, y2: vertical, stroke: '#e5e7eb' });
            element('text', { x: left - 12, y: vertical + 4, 'text-anchor': 'end', fill: '#6b7280', 'font-size': 12 }, `₹${shortCurrency.format(value)}`);
        }

        const labelCount = Math.min(days.length, 6);
        for (let tick = 0; tick < labelCount; tick += 1) {
            const index = labelCount === 1 ? 0 : Math.round(tick * (days.length - 1) / (labelCount - 1));
            element('text', { x: position(index), y: bottom + 26, 'text-anchor': tick === 0 && labelCount > 1 ? 'start' : tick === labelCount - 1 && labelCount > 1 ? 'end' : 'middle', fill: '#6b7280', 'font-size': 12 }, dateLabel(days[index].date));
        }

        [...active].reverse().forEach((metric) => {
            const points = days.map((day, index) => `${position(index)},${height(day[metric])}`).join(' ');
            element('polyline', { points, fill: 'none', stroke: metrics[metric].color, 'stroke-width': metric === 'revenue' ? 4 : 2.5, 'stroke-linejoin': 'round', 'stroke-linecap': 'round', 'stroke-dasharray': metric === 'sales' ? '7 3' : 'none', 'data-series': metric });
            if (days.length <= 31) {
                days.forEach((day, index) => {
                    const point = element('circle', { cx: position(index), cy: height(day[metric]), r: metric === 'revenue' ? 5 : 3, fill: metrics[metric].color });
                    const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
                    title.textContent = `${dateLabel(day.date)} · ${metrics[metric].label}: ${currency.format(day[metric])}`;
                    point.appendChild(title);
                });
            }
        });

        cursor = element('line', { x1: left, x2: left, y1: top, y2: bottom, stroke: '#6b7280', 'stroke-dasharray': '3 3', visibility: 'hidden', 'pointer-events': 'none' });
        if (!active.length) {
            element('text', { x: 480, y: 145, 'text-anchor': 'middle', fill: '#6b7280', 'font-size': 16 }, 'Select Sales, Expense or Revenue to show a line.');
            detail.textContent = 'No graph options selected.';
        } else {
            showDay(days.length - 1);
        }
    };

    chart.addEventListener('pointermove', (event) => {
        const bounds = chart.getBoundingClientRect();
        const horizontal = (event.clientX - bounds.left) * 960 / bounds.width;
        const index = Math.max(0, Math.min(days.length - 1, Math.round((horizontal - left) / (right - left) * (days.length - 1))));
        showDay(index);
    });

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', draw));
    const updateDates = () => {
        const custom = period.value === 'range';
        form.querySelectorAll('[data-statistics-range]').forEach((container) => { container.hidden = !custom; });
        start.disabled = !custom;
        end.disabled = !custom;
        end.min = start.value;
    };
    period.addEventListener('change', () => {
        updateDates();
        if (period.value !== 'range') form.requestSubmit();
    });
    start.addEventListener('change', updateDates);
    updateDates();
    draw();
})();
