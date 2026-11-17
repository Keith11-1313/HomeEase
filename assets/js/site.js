// site.js — navigation, chapter ink, reveals and the quote calculator.

document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('site-header');
    const toggle = document.getElementById('nav-toggle');

    // ---- mobile navigation -------------------------------------
    if (toggle && header) {
        toggle.addEventListener('click', () => {
            const open = header.classList.toggle('nav-open');
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            const label = toggle.querySelector('.nav-toggle-label');
            if (label) label.textContent = open ? 'Close' : 'Menu';
        });
    }

    // ---- confirm modal -----------------------------------------
    const confirmModal = document.getElementById('confirm-modal');
    const confirmMsg   = document.getElementById('confirm-modal-msg');
    const confirmOk    = document.getElementById('confirm-modal-ok');
    const confirmCancel = document.getElementById('confirm-modal-cancel');

    function showConfirm(message) {
        return new Promise((resolve) => {
            if (!confirmModal) { resolve(window.confirm(message)); return; }
            confirmMsg.textContent = message;
            confirmModal.removeAttribute('hidden');
            confirmOk.focus();
            const cleanup = (result) => {
                confirmModal.setAttribute('hidden', '');
                resolve(result);
            };
            confirmOk.onclick    = () => cleanup(true);
            confirmCancel.onclick = () => cleanup(false);
            confirmModal.onclick  = (e) => { if (e.target === confirmModal) cleanup(false); };
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (await showConfirm(form.dataset.confirm)) form.submit();
        });
    });
    document.querySelectorAll('[data-confirm]:not(form)').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();
            if (await showConfirm(button.dataset.confirm)) {
                button.removeAttribute('data-confirm');
                button.click();
            }
        });
    });

    document.querySelectorAll('select[data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form?.requestSubmit());
    });

    // ---- admin burger menu -----------------------------------------
    const burger  = document.getElementById('admin-burger');
    const side    = document.getElementById('admin-side');
    const overlay = document.getElementById('admin-overlay');
    const sideClose = document.getElementById('admin-side-close');

    if (burger && side) {
        const openDrawer = () => {
            side.classList.add('is-open');
            burger.setAttribute('aria-expanded', 'true');
            overlay?.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
        };
        const closeDrawer = () => {
            side.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
            overlay?.setAttribute('hidden', '');
            document.body.style.overflow = '';
        };
        burger.addEventListener('click', openDrawer);
        sideClose?.addEventListener('click', closeDrawer);
        overlay?.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });
    }
    const photoList = document.getElementById('photo-list');
    if (photoList) {
        let dragging = null;

        photoList.addEventListener('dragstart', (e) => {
            dragging = e.target.closest('tr');
            dragging?.classList.add('is-dragging');
        });

        photoList.addEventListener('dragover', (e) => {
            e.preventDefault();
            const over = e.target.closest('tr');
            if (!over || over === dragging) return;
            const rows = [...photoList.querySelectorAll('tr')];
            const isBefore = rows.indexOf(dragging) > rows.indexOf(over);
            over.parentNode.insertBefore(dragging, isBefore ? over : over.nextSibling);
        });

        photoList.addEventListener('dragend', async () => {
            dragging?.classList.remove('is-dragging');
            const ids = [...photoList.querySelectorAll('tr[data-id]')].map((r) => r.dataset.id).join(',');
            const url = photoList.dataset.reorderUrl;
            const csrf = photoList.dataset.csrf;
            const body = new FormData();
            body.append('action', 'reorder');
            body.append('ids', ids);
            body.append('_csrf', csrf);
            await fetch(url, { method: 'POST', body });
            dragging = null;
        });

        photoList.querySelectorAll('tr').forEach((row) => row.setAttribute('draggable', 'true'));
    }

    const mediaTypes = document.querySelectorAll('[name="media_type"]');
    if (mediaTypes.length) {
        const imageUrl = document.querySelector('[name="url"]');
        const videoUrl = document.querySelector('[name="video_url"]');
        const syncMediaFields = () => {
            const video = document.querySelector('[name="media_type"]:checked')?.value === 'video';
            document.querySelectorAll('[data-media-field="image"]').forEach((field) => { field.hidden = video; });
            document.querySelectorAll('[data-media-field="video"]').forEach((field) => { field.hidden = !video; field.querySelectorAll('input').forEach((input) => { input.disabled = !video; }); });
            imageUrl.disabled = video;
            imageUrl.required = !video;
            videoUrl.disabled = !video;
            videoUrl.required = video;
            const mediaLabel = document.getElementById('media-submit-label');
            if (mediaLabel) mediaLabel.textContent = video ? 'Add video' : 'Add photo';
            const button = document.getElementById('media-submit');
            if (button) {
                button.querySelector('.icon')?.remove();
                button.insertAdjacentHTML('afterbegin', `<img class="icon" src="${button.dataset[video ? 'videoIcon' : 'photoIcon']}" width="20" height="20" alt="" aria-hidden="true">`);
            }
        };
        mediaTypes.forEach((input) => input.addEventListener('change', syncMediaFields));
        syncMediaFields();
    }

    // ---- header ink follows the field under it -----------------
    const darkFields = Array.from(document.querySelectorAll('[data-field="dark"]'));
    if (header && darkFields.length) {
        let queued = false;

        const paintHeader = () => {
            const line = 62;
            const onDark = darkFields.some((el) => {
                const rect = el.getBoundingClientRect();
                return rect.top <= line && rect.bottom > line;
            });
            header.classList.toggle('is-light', onDark);
            queued = false;
        };

        const requestPaint = () => {
            if (queued) return;
            queued = true;
            requestAnimationFrame(paintHeader);
        };

        paintHeader();
        window.addEventListener('scroll', requestPaint, { passive: true });
        window.addEventListener('resize', requestPaint);
    }

    // ---- section entrances -------------------------------------
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        reveals.forEach((el) => observer.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-in'));
    }

    // ---- quote calculator --------------------------------------
    const form = document.getElementById('quote-form');
    const out = document.getElementById('quote-result');
    if (!form || !out) return;

    const submit = form.querySelector('button[type="submit"]');

    const number = (n) => new Intl.NumberFormat('en-PH', { maximumFractionDigits: 2 }).format(n);

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const render = (q) => {
        const rows = q.items.map((item) => `
            <li>
                <span>${escapeHtml(item.label)}${item.note ? `<em>${escapeHtml(item.note)}</em>` : ''}</span>
                <b>${escapeHtml(q.currency + number(item.amount))}</b>
            </li>`).join('');

        out.innerHTML = `
            <h3>${escapeHtml(q.unit.name)} · ${escapeHtml(q.slot_label)}</h3>
            <p class="quote-meta">${escapeHtml(q.date_label)} · ${escapeHtml(q.day_label)} · ${q.hours}h · ${q.pax} pax</p>
            <ul class="quote-lines">${rows}</ul>
            <div class="quote-total">
                <span>Total</span>
                <strong>${escapeHtml(q.currency + number(q.total))}</strong>
            </div>
            ${q.deposit ? `<p class="quote-deposit">A refundable deposit of <strong>${escapeHtml(q.currency + number(q.deposit))}</strong> is collected separately and returned after check-out.</p>` : ''}
            <a class="btn btn--block" href="${escapeHtml(q.messenger_url)}" target="_blank" rel="noopener">Send this on Messenger</a>
            <p class="quote-note">An estimate only. Availability and the final price are confirmed in chat.</p>
        `;
        out.hidden = false;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const original = submit.textContent;
        submit.disabled = true;
        submit.textContent = 'Calculating…';

        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Could not calculate that quote.');
            render(data);
        } catch (error) {
            out.hidden = false;
            out.innerHTML = `<p class="quote-error">${escapeHtml(error.message)}</p>`;
        } finally {
            submit.disabled = false;
            submit.textContent = original;
        }
    });

    form.addEventListener('change', () => form.requestSubmit());
    form.requestSubmit();
});
