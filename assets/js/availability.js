document.addEventListener('DOMContentLoaded', () => {
    const section = document.querySelector('[data-calendar]');
    if (!section) return;
    const container = section.querySelector('[data-calendar-months]');
    const data = JSON.parse(section.dataset.calendar || '{}');
    // blocks: array of {block_date, slot, note}
    const blocksArr = data.blocks || [];
    const ranges = data.ranges || [];

    // Index blocks by date -> { overnight, daycation, all, notes[] }
    const blockMap = {};
    for (const b of blocksArr) {
        const d = b.block_date;
        if (!blockMap[d]) blockMap[d] = { slots: new Set(), notes: [] };
        blockMap[d].slots.add(b.slot);
        if (b.note) blockMap[d].notes.push(b.note);
    }

    const pad = (n) => String(n).padStart(2, '0');
    const fmtDate = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;

    const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    function renderMonth(year, month) {
        const wrap = document.createElement('div');
        wrap.className = 'avail-month';

        const heading = document.createElement('p');
        heading.className = 'avail-month-label';
        heading.textContent = new Date(year, month, 1).toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        wrap.append(heading);

        const grid = document.createElement('div');
        grid.className = 'calendar-grid';

        DAYS.forEach((day) => {
            const h = document.createElement('span');
            h.className = 'calendar-day-label';
            h.textContent = day;
            grid.append(h);
        });

        const firstDay = new Date(year, month, 1).getDay();
        const offset = (firstDay + 6) % 7;
        for (let i = 0; i < offset; i++) grid.append(document.createElement('span'));

        const count = new Date(year, month + 1, 0).getDate();
        for (let day = 1; day <= count; day++) {
            const date = fmtDate(year, month, day);
            const cell = document.createElement('span');
            const info = blockMap[date];
            const range = ranges.find(({ start, end }) => (!start || date >= start) && (!end || date <= end) && (start || end));

            const slots = info?.slots ?? new Set();
            const isFull = slots.has('all') || (slots.has('overnight') && slots.has('daycation'));
            const isOvernight = !isFull && slots.has('overnight');
            const isDaycation = !isFull && slots.has('daycation');

            const classes = ['calendar-date'];
            if (isFull) classes.push('is-unavailable');
            if (isOvernight) classes.push('is-overnight');
            if (isDaycation) classes.push('is-daycation');
            if (range && !isFull) classes.push('is-announced');
            cell.className = classes.join(' ');

            cell.textContent = String(day);

            // add time labels for split cells
            if (isOvernight || isDaycation) {
                cell.textContent = '';
                const num = document.createElement('span');
                num.className = 'cal-day-num';
                num.textContent = String(day);
                const top = document.createElement('span');
                top.className = 'cal-slot-label';
                top.textContent = '7am\u20137pm';
                top.setAttribute('aria-hidden', 'true');
                const bot = document.createElement('span');
                bot.className = 'cal-slot-label';
                bot.textContent = '7pm\u20137am';
                bot.setAttribute('aria-hidden', 'true');
                if (isDaycation) top.classList.add('is-booked');
                if (isOvernight) bot.classList.add('is-booked');
                cell.append(num, top, bot);
            }

            const tooltipParts = [];
            if (isFull) tooltipParts.push('Fully booked');
            else if (isOvernight) tooltipParts.push('Overnight unavailable');
            else if (isDaycation) tooltipParts.push('Daycation unavailable');
            if (info?.notes.length) tooltipParts.push(...info.notes);
            if (range) tooltipParts.push(range.title);
            if (tooltipParts.length) cell.title = tooltipParts.join(' — ');

            grid.append(cell);
        }

        wrap.append(grid);
        return wrap;
    }

    function render() {
        container.replaceChildren();
        const today = new Date();
        const y0 = today.getFullYear(), m0 = today.getMonth();
        const next = new Date(y0, m0 + 1, 1);
        container.append(renderMonth(y0, m0));
        container.append(renderMonth(next.getFullYear(), next.getMonth()));
    }

    render();
});
