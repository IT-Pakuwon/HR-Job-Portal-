{{-- Typing rules for the number fields of the Canvass BQ pages (Qty, prices, Duration).
     Delegated from the document, so rows added later are covered too. --}}
<script>
    (function() {
        const DECIMAL = '.bq-qty,.bq-price-mat,.bq-price-jsa';
        const WHOLE = '.bq-duration-input'; // saved as a whole number
        const isOurs = el => !!(el && el.matches && el.matches(DECIMAL + ',' + WHOLE));

        // inserts at the cursor / over the selection (number inputs have no selection API)
        function insert(el, text) {
            if (document.execCommand('insertText', false, text)) return;
            el.value = text;
            el.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }

        // "12,5", "1.234,56" and "1,234.56" become 12.5, 1234.56 and 1234.56
        function toPlainNumber(text) {
            const s = String(text).replace(/[^0-9.,]/g, '');
            let at = Math.max(s.lastIndexOf('.'), s.lastIndexOf(','));
            if (at >= 0 && s.indexOf(s[at]) !== at) at = -1; // a repeated mark separates thousands
            const whole = (at < 0 ? s : s.slice(0, at)).replace(/[.,]/g, '');
            const fraction = at < 0 ? '' : s.slice(at + 1);
            return fraction ? (whole || '0') + '.' + fraction : whole;
        }

        document.addEventListener('keydown', e => {
            const el = e.target;
            if (!isOurs(el)) return;
            if (e.ctrlKey || e.metaKey) return; // copy, paste, select all, undo

            if (e.key.length > 1) { // Backspace, Tab, arrows...
                if (['Enter', 'ArrowUp', 'ArrowDown'].includes(e.key)) e.preventDefault();
                return;
            }
            if (e.key >= '0' && e.key <= '9') return;

            if (e.key === '.' || e.key === ',') {
                const hasDot = (el.value || '').includes('.');
                if (e.key === '.' && !hasDot) return;
                e.preventDefault();
                if (!hasDot) insert(el, '.'); // a comma is a decimal point
                return;
            }
            e.preventDefault();
        });

        document.addEventListener('paste', e => {
            if (!isOurs(e.target)) return;
            e.preventDefault();
            const number = toPlainNumber((e.clipboardData || window.clipboardData).getData('text'));
            if (number === '') return;
            // a field still showing 0 / 0.00 is replaced, not added to
            if (e.target.value !== '' && Number(e.target.value) === 0) e.target.select();
            insert(e.target, number);
        });

        // Duration: let the decimal be typed, then say clearly that only the whole number is kept
        document.addEventListener('change', e => {
            const el = e.target;
            if (!el.matches || !el.matches(WHOLE) || el.value === '') return;
            const typed = Number(el.value);
            if (Number.isInteger(typed)) {
                el.value = String(typed);
                return;
            }
            el.value = String(Math.trunc(typed));
            if (window.toastr) toastr.warning(`Duration must be a whole number. ${typed} was changed to ${el.value}.`);
            el.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }, true);
    })();
</script>
