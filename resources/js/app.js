import './bootstrap';

// ===== Responsive data tables =====
// List tables with a header row are progressively enhanced:
//  - any table without a scroll container is wrapped in one, and
//  - each cell is labelled with its column heading so the stacked
//    "card" layout from app.css remains meaningful on phones.
// Tables with no header, plus the COT/EPOC rating sheets and printable
// report tables, are left untouched.
(function () {
    const SKIP_CLASSES = ['cot-table', 'epoc-table', 'info-table', 'rating-table', 'comparison-table', 'signatures', 'bordered'];

    function hasHeader(table) {
        return table.querySelector('thead tr th') !== null;
    }

    function isSkipped(table) {
        for (let i = 0; i < SKIP_CLASSES.length; i++) {
            if (table.classList.contains(SKIP_CLASSES[i])) return true;
        }
        return false;
    }

    function cleanLabel(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
    }

    function enhance() {
        document.querySelectorAll('main table').forEach(function (table) {
            if (isSkipped(table) || !hasHeader(table) || table.dataset.responsive === '1') return;
            table.dataset.responsive = '1';

            const firstRow = table.querySelector('thead tr');
            const labels = Array.from(firstRow.querySelectorAll('th')).map(function (th) {
                return cleanLabel(th.textContent);
            });
            if (!labels.length) return;

            if (!table.closest('.overflow-x-auto, .table-scroll')) {
                const wrap = document.createElement('div');
                wrap.className = 'table-scroll';
                table.parentNode.insertBefore(wrap, table);
                wrap.appendChild(table);
            }

            table.classList.add('rsp-table');

            table.querySelectorAll('tbody tr').forEach(function (tr) {
                const cells = tr.querySelectorAll('td');
                cells.forEach(function (td, i) {
                    if (td.hasAttribute('colspan') && (td.getAttribute('colspan') || '1') !== '1') {
                        td.classList.add('rsp-span');
                        return;
                    }
                    const label = labels[i] || '';
                    td.setAttribute('data-label', label);
                    if (!label) td.classList.add('rsp-actions');
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhance);
    } else {
        enhance();
    }
})();