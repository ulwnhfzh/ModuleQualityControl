import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function parseDateEntry(value) {
    const input = value.trim();
    if (!input) return { empty: true, iso: '' };

    let day; let month; let year;
    const iso = input.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    const numeric = input.match(/^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$/);
    const compact = input.match(/^(\d{2})(\d{2})(\d{4})$/);
    const compactShortYear = input.match(/^(\d{2})(\d{2})(\d{2})$/);

    if (iso) [, year, month, day] = iso;
    else if (numeric) [, day, month, year] = numeric;
    else if (compact) [, day, month, year] = compact;
    else if (compactShortYear) [, day, month, year] = [
        compactShortYear[0], compactShortYear[1], compactShortYear[2], `20${compactShortYear[3]}`,
    ];
    else return { empty: false, iso: '' };

    day = Number(day); month = Number(month); year = Number(year);
    const check = new Date(Date.UTC(year, month - 1, day));
    if (year < 1000 || check.getUTCFullYear() !== year || check.getUTCMonth() !== month - 1 || check.getUTCDate() !== day) {
        return { empty: false, iso: '' };
    }
    return { empty: false, iso: `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}` };
}

function syncDateEntry(entry, showError = false) {
    const text = entry.querySelector('[data-date-text]');
    const hidden = entry.querySelector('[data-date-value]');
    const error = entry.querySelector('[data-date-error]');
    const parsed = parseDateEntry(text.value);
    hidden.value = parsed.iso;
    const invalid = !parsed.empty && !parsed.iso;
    error?.classList.toggle('hidden', !(invalid && showError));
    text.setAttribute('aria-invalid', invalid && showError ? 'true' : 'false');
    return !invalid && (!text.required || !parsed.empty);
}

document.querySelectorAll('[data-date-entry]').forEach((entry) => {
    const text = entry.querySelector('[data-date-text]');
    const picker = entry.querySelector('[data-date-picker]');
    const initial = parseDateEntry(text.value);
    if (initial.iso) entry.querySelector('[data-date-value]').value = initial.iso;

    text.addEventListener('input', () => {
        if (/^\d{6}$/.test(text.value.trim())) {
            const digits = text.value.trim();
            text.value = `${digits.slice(0, 2)}/${digits.slice(2, 4)}/20${digits.slice(4)}`;
        } else if (/^\d{8}$/.test(text.value.trim())) {
            const digits = text.value.trim();
            text.value = `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
        }
        syncDateEntry(entry);
    });
    text.addEventListener('blur', () => {
        if (/^\d{8}$/.test(text.value.trim())) {
            const digits = text.value.trim();
            text.value = `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
        } else if (/^\d{6}$/.test(text.value.trim())) {
            const digits = text.value.trim();
            text.value = `${digits.slice(0, 2)}/${digits.slice(2, 4)}/20${digits.slice(4)}`;
        }
        syncDateEntry(entry, true);
    });
    entry.querySelector('[data-date-open]')?.addEventListener('click', () => {
        const parsed = parseDateEntry(text.value);
        if (parsed.iso) picker.value = parsed.iso;
        if (typeof picker.showPicker === 'function') picker.showPicker();
        else picker.click();
    });
    picker.addEventListener('change', () => {
        if (!picker.value) return;
        const [year, month, day] = picker.value.split('-');
        text.value = `${day}/${month}/${year}`;
        syncDateEntry(entry);
    });
});

document.addEventListener('submit', (event) => {
    const entries = [...event.target.querySelectorAll('[data-date-entry]')];
    const invalidEntry = entries.find((entry) => !syncDateEntry(entry, true));
    if (invalidEntry) {
        event.preventDefault();
        invalidEntry.querySelector('[data-date-text]')?.focus();
    }
}, true);

// ---------------------------------------------------------------------------
// Header tabel menempel (sticky) saat scroll.
// Menghitung "top" tiap baris header di dalam .table-sticky-scroll supaya
// header 2-3 baris (rowspan/colspan) menumpuk rapat tanpa celah.
// ---------------------------------------------------------------------------
function setupStickyTableHeaders() {
    const update = (thead) => {
        let offset = -1;  // sejajar dengan top: -1px di CSS
        thead.querySelectorAll(':scope > tr').forEach((tr) => {
            tr.querySelectorAll(':scope > th').forEach((th) => {
                th.style.top = `${offset}px`;
            });
            offset += tr.getBoundingClientRect().height;
        });
    };

    const heads = document.querySelectorAll('.table-sticky-scroll thead');

    heads.forEach((thead) => {
        update(thead);
        if ('ResizeObserver' in window) {
            new ResizeObserver(() => update(thead)).observe(thead);
        }
    });

    window.addEventListener('resize', () => heads.forEach(update));
}

setupStickyTableHeaders();

// ---------------------------------------------------------------------------
// Tabel "penuh layar": tinggi wrapper = sisa layar dari posisi tabel sampai
// tepi bawah layar (tanpa ruang kosong di bawah). Opt-in lewat class
// .table-fill-screen di samping .table-sticky-scroll.
// ---------------------------------------------------------------------------
function setupFillScreenTables() {
    const MIN_HEIGHT = 280; // layar sangat pendek: tabel tidak boleh gepeng, halaman boleh scroll

    const fit = (wrap) => {
        const container = wrap.closest('main > div');
        if (!container) return;

        // padding bawah pembungkus halaman dihilangkan agar tabel benar-benar mentok ke bawah layar
        container.style.paddingBottom = '0px';

        const wrapRect = wrap.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        // yang ada di bawah tabel (pagination, catatan, tepi kartu) tetap diberi tempat
        const belowTable = Math.max(0, containerRect.bottom - wrapRect.bottom);
        const topOnPage = wrapRect.top + window.scrollY;

        const height = Math.max(MIN_HEIGHT, Math.floor(window.innerHeight - topOnPage - belowTable));
        wrap.style.maxHeight = `${height}px`;
    };

    const run = () => document.querySelectorAll('.table-fill-screen').forEach(fit);

    run();
    window.addEventListener('load', run);
    window.addEventListener('resize', run);
    window.addEventListener('orientationchange', run);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(run);
}

setupFillScreenTables();