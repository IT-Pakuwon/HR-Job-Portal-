// assets/js/shared/mention-autocomplete.js
// Lightweight @mention autocomplete for comment/discussion textareas.
// Usage: attachMentionAutocomplete({ inputSelector: '#discussionInput', fetchUrlFn: () => `/access-request/mentionable-users/${hash}` });

function attachMentionAutocomplete({ inputSelector, fetchUrlFn }) {

    let users = null;
    let fetchedForUrl = null;
    let activeIndex = 0;
    let matches = [];

    function $input() {
        return $(inputSelector);
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function ensureDropdown() {
        let $dd = $('#mentionAutocompleteDropdown');
        if ($dd.length === 0) {
            $dd = $('<div id="mentionAutocompleteDropdown" class="fixed z-[10050] hidden max-h-56 w-64 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-[#0f172a]"></div>');
            $('body').append($dd);
        }
        return $dd;
    }

    function hideDropdown() {
        ensureDropdown().addClass('hidden').empty();
        matches = [];
    }

    function positionDropdown($dd) {
        const el = $input().get(0);
        if (!el) return;
        const rect = el.getBoundingClientRect();
        $dd.css({
            left: rect.left + 'px',
            top: (rect.top - $dd.outerHeight() - 6) + 'px',
            width: Math.max(rect.width, 220) + 'px',
        });
    }

    function renderDropdown() {
        const $dd = ensureDropdown();
        $dd.empty();

        if (!matches.length) {
            hideDropdown();
            return;
        }

        matches.forEach((u, i) => {
            $dd.append(`
                <div class="mention-option cursor-pointer px-3 py-2 text-sm ${i === activeIndex ? 'bg-slate-100 dark:bg-white/10' : ''}" data-index="${i}">
                    <span class="font-medium text-slate-700 dark:text-slate-200">${escapeHtml(u.name || u.username)}</span>
                    <span class="ml-1 text-xs text-slate-400">@${escapeHtml(u.username)}</span>
                </div>
            `);
        });

        positionDropdown($dd);
        $dd.removeClass('hidden');
    }

    function currentToken(el) {
        const value = el.value;
        const caret = el.selectionStart;
        const upToCaret = value.slice(0, caret);
        const at = upToCaret.lastIndexOf('@');
        if (at === -1) return null;

        const token = upToCaret.slice(at + 1);
        if (/\s/.test(token)) return null;

        return { start: at, query: token };
    }

    function loadUsers(callback) {
        const url = fetchUrlFn();
        if (!url) return;

        if (users && fetchedForUrl === url) {
            callback(users);
            return;
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                users = Array.isArray(response) ? response : (response.data || []);
                fetchedForUrl = url;
                callback(users);
            },
            error: function () {
                users = [];
                fetchedForUrl = url;
                callback(users);
            },
        });
    }

    function selectMatch(index) {
        const el = $input().get(0);
        if (!el || !matches[index]) return;

        const token = currentToken(el);
        if (!token) return;

        const value = el.value;
        const before = value.slice(0, token.start);
        const after = value.slice(el.selectionStart);
        const insertion = '@' + matches[index].username + ' ';

        el.value = before + insertion + after;

        const newCaret = (before + insertion).length;
        el.focus();
        el.setSelectionRange(newCaret, newCaret);

        hideDropdown();
    }

    $(document).on('input', inputSelector, function () {
        const token = currentToken(this);

        if (!token) {
            hideDropdown();
            return;
        }

        loadUsers(function (list) {
            const q = token.query.toLowerCase();
            matches = list.filter(u =>
                (u.username || '').toLowerCase().includes(q) ||
                (u.name || '').toLowerCase().includes(q)
            ).slice(0, 8);
            activeIndex = 0;
            renderDropdown();
        });
    });

    // Registered ahead of each page's own "Enter submits" keydown/keypress handler, so when the
    // mention dropdown is open we must stopImmediatePropagation or the comment would also be sent.
    $(document).on('keydown', inputSelector, function (e) {
        if (!matches.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            e.stopImmediatePropagation();
            activeIndex = Math.min(activeIndex + 1, matches.length - 1);
            renderDropdown();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            e.stopImmediatePropagation();
            activeIndex = Math.max(activeIndex - 1, 0);
            renderDropdown();
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            e.preventDefault();
            e.stopImmediatePropagation();
            selectMatch(activeIndex);
        } else if (e.key === 'Escape') {
            e.stopImmediatePropagation();
            hideDropdown();
        }
    });

    $(document).on('mousedown', '#mentionAutocompleteDropdown .mention-option', function (e) {
        e.preventDefault();
        selectMatch(Number($(this).data('index')));
    });

    $(document).on('blur', inputSelector, function () {
        setTimeout(hideDropdown, 150);
    });
}

// Escapes HTML then wraps @username tokens in a styled span. Used when rendering
// comment/discussion messages so mentions are visually distinct from plain text.
function highlightMentions(text) {
    const escaped = String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    return escaped.replace(/@([\w.]+)/g, '<span class="text-indigo-600 dark:text-indigo-400 font-semibold">@$1</span>');
}
