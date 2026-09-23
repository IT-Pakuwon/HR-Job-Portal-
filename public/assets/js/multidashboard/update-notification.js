(function () {
    const LAST_SEEN_KEY = 'updateNotificationLastSeenAt';
    const BADGE_FRESH_HOURS = 7;
    const POPUP_LAST_ID_KEY = 'updateNotificationPopupLastId';
    const POPUP_PERIODS_KEY = 'updateNotificationPopupPeriods';
    const POPUP_FRESH_DAYS = 1;
    const POPUP_AUTO_HIDE_MS = 10000;
    const POLL_INTERVAL_MS = 20000;

    let popupHideTimer = null;

    const TYPE_META = {
        new_feature: {
            icon: '✨',
            cardClasses: 'border-green-200 bg-green-50 dark:border-green-900/40 dark:bg-green-900/10',
            iconClasses: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            pickerSelectedClasses: 'border-green-400 bg-green-50 ring-1 ring-green-400 dark:bg-green-900/10',
        },
        maintenance: {
            icon: '🔧',
            cardClasses: 'border-orange-200 bg-orange-50 dark:border-orange-900/40 dark:bg-orange-900/10',
            iconClasses: 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
            pickerSelectedClasses: 'border-orange-400 bg-orange-50 ring-1 ring-orange-400 dark:bg-orange-900/10',
        },
        information: {
            icon: 'ℹ️',
            cardClasses: 'border-blue-200 bg-blue-50 dark:border-blue-900/40 dark:bg-blue-900/10',
            iconClasses: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
            pickerSelectedClasses: 'border-blue-400 bg-blue-50 ring-1 ring-blue-400 dark:bg-blue-900/10',
        },
    };
    const DEFAULT_TYPE = 'information';

    let notifications = [];
    let selectedType = null;

    function typeMeta(type) {
        return TYPE_META[type] || TYPE_META[DEFAULT_TYPE];
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function formatDescription(text) {
        return escapeHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }

    function wrapSelection(marker) {
        const el = document.getElementById('updateNotificationDescInput');
        if (!el) return;

        const start = el.selectionStart;
        const end = el.selectionEnd;
        const value = el.value;
        const selected = value.slice(start, end) || 'text';

        el.value = value.slice(0, start) + marker + selected + marker + value.slice(end);
        el.focus();
        el.setSelectionRange(start + marker.length, start + marker.length + selected.length);
    }

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        const date = new Date(String(dateStr).replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';
        const diff = Math.floor((Date.now() - date.getTime()) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 2592000) return Math.floor(diff / 86400) + 'd ago';
        return date.toLocaleDateString();
    }

    function isOpen() {
        return $('#updateNotificationPanel').hasClass('translate-x-0');
    }

    function openPanel() {
        $('#updateNotificationOverlay').removeClass('hidden');
        $('#updateNotificationPanel').removeClass('translate-x-full').addClass('translate-x-0');
        $('#updateNotificationBtn').addClass('bg-slate-100 dark:bg-slate-700');
    }

    function closePanel() {
        $('#updateNotificationOverlay').addClass('hidden');
        $('#updateNotificationPanel').removeClass('translate-x-0').addClass('translate-x-full');
        $('#updateNotificationBtn').removeClass('bg-slate-100 dark:bg-slate-700');
    }

    function lastSeenAt() {
        const stored = localStorage.getItem(LAST_SEEN_KEY);
        return stored ? new Date(stored) : null;
    }

    function updateBadge() {
        const seen = lastSeenAt();
        const freshCutoff = Date.now() - BADGE_FRESH_HOURS * 60 * 60 * 1000;

        const unread = notifications.filter(function (n) {
            const created = new Date(String(n.created_at).replace(' ', 'T')).getTime();
            const isUnseen = !seen || created > seen.getTime();
            const isFresh = created > freshCutoff;
            return isUnseen && isFresh;
        }).length;

        const badge = $('#updateNotificationBadge');
        if (unread > 0) {
            badge.text(unread > 99 ? '99+' : unread).removeClass('hidden');
        } else {
            badge.addClass('hidden');
        }
    }

    function markAllSeen() {
        if (notifications.length === 0) return;
        localStorage.setItem(LAST_SEEN_KEY, notifications[0].created_at);
        updateBadge();
    }

    function renderNotifications() {
        const list = $('#updateNotificationList');
        list.empty();

        if (notifications.length === 0) {
            list.html('<p class="py-4 text-center text-sm italic text-slate-400">No updates yet.</p>');
            return;
        }

        notifications.forEach(function (n) {
            const meta = typeMeta(n.type);
            list.append(`
                <div class="rounded-lg border p-3 ${meta.cardClasses}">
                    <div class="flex items-start gap-2">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm ${meta.iconClasses}">${meta.icon}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">${n.title}</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">${formatDescription(n.description)}</p>
                            <p class="mt-2 text-[10px] text-slate-400">${timeAgo(n.created_at)}</p>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    function hidePopup() {
        clearTimeout(popupHideTimer);
        $('#updateNotificationToast').addClass('-translate-y-4 opacity-0');
        setTimeout(function () {
            $('#updateNotificationToast').addClass('hidden');
        }, 300);
    }

    function playChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const play = function (freq, startAt, duration, vol) {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, ctx.currentTime + startAt);
                gain.gain.setValueAtTime(vol || 0.25, ctx.currentTime + startAt);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + startAt + duration);
                osc.start(ctx.currentTime + startAt);
                osc.stop(ctx.currentTime + startAt + duration);
            };
            // Distinct 3-note descending chime, so it doesn't sound like the document notification ping.
            play(1046, 0, 0.16);
            play(784, 0.1, 0.16);
            play(659, 0.2, 0.28);
        } catch (e) {
            // AudioContext not supported
        }
    }

    function showPopup(n) {
        const meta = typeMeta(n.type);
        const toast = $('#updateNotificationToast');

        toast.removeClass(
            'border-green-200 bg-green-50 dark:border-green-900/40 dark:bg-green-900/10 ' +
            'border-orange-200 bg-orange-50 dark:border-orange-900/40 dark:bg-orange-900/10 ' +
            'border-blue-200 bg-blue-50 dark:border-blue-900/40 dark:bg-blue-900/10'
        ).addClass(meta.cardClasses);

        $('#updateNotificationToastIcon').attr('class', 'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-base ' + meta.iconClasses).text(meta.icon);
        $('#updateNotificationToastTitle').text(n.title);
        $('#updateNotificationToastDesc').html(formatDescription(n.description));

        toast.removeClass('hidden');
        requestAnimationFrame(function () {
            toast.removeClass('-translate-y-4 opacity-0');
        });

        playChime();

        clearTimeout(popupHideTimer);
        popupHideTimer = setTimeout(hidePopup, POPUP_AUTO_HIDE_MS);
    }

    function localDateStr(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Pagi / Siang / Sore — splits the day into 3 windows so the popup can fire at most once per window.
    function dayPeriod(date) {
        const hour = date.getHours();
        if (hour < 11) return 'pagi';
        if (hour < 15) return 'siang';
        return 'sore';
    }

    function readPopupPeriods() {
        try {
            return JSON.parse(localStorage.getItem(POPUP_PERIODS_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function popupAllowedThisPeriod() {
        const now = new Date();
        const stored = readPopupPeriods();
        return stored.date !== localDateStr(now) || !(stored.shown || []).includes(dayPeriod(now));
    }

    function markPopupShownThisPeriod() {
        const now = new Date();
        const today = localDateStr(now);
        let stored = readPopupPeriods();

        if (stored.date !== today) {
            stored = { date: today, shown: [] };
        }

        if (!stored.shown.includes(dayPeriod(now))) {
            stored.shown.push(dayPeriod(now));
        }

        localStorage.setItem(POPUP_PERIODS_KEY, JSON.stringify(stored));
    }

    function maybeShowPopup() {
        if (notifications.length === 0) return;
        if (!popupAllowedThisPeriod()) return;

        const newest = notifications[0];
        const created = new Date(String(newest.created_at).replace(' ', 'T')).getTime();
        const freshCutoff = Date.now() - POPUP_FRESH_DAYS * 24 * 60 * 60 * 1000;

        if (created <= freshCutoff) return;
        if (String(localStorage.getItem(POPUP_LAST_ID_KEY)) === String(newest.id)) return;

        localStorage.setItem(POPUP_LAST_ID_KEY, String(newest.id));
        markPopupShownThisPeriod();
        showPopup(newest);
    }

    function loadNotifications() {
        $.ajax({
            url: '/update-notification/json',
            type: 'GET',
            success: function (response) {
                notifications = response.data || [];
                renderNotifications();
                updateBadge();
                maybeShowPopup();
            },
            error: function (xhr) {
                console.error('Error fetching update notifications:', xhr.responseText);
                $('#updateNotificationList').html('<p class="text-red-500 italic">Failed to load updates.</p>');
            }
        });
    }

    function selectType(type) {
        selectedType = type;

        $('.update-type-option').each(function () {
            const option = $(this);
            const meta = typeMeta(option.data('type'));

            option.removeClass(meta.pickerSelectedClasses);

            if (option.data('type') === type) {
                option.addClass(meta.pickerSelectedClasses);
            }
        });
    }

    function submitNotification() {
        const descInput = $('#updateNotificationDescInput');
        const description = descInput.val().trim();

        if (!selectedType) {
            alert('Please choose a notification type.');
            return;
        }

        if (!description) {
            alert('Please describe what changed.');
            return;
        }

        const btn = $('#updateNotificationSubmitBtn');
        btn.prop('disabled', true).addClass('opacity-60');

        $.ajax({
            url: '/update-notification',
            type: 'POST',
            data: {
                type: selectedType,
                description: description,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                descInput.val('');
                loadNotifications();
            },
            error: function (xhr) {
                console.error('Error posting update notification:', xhr);
                alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown Error'));
            },
            complete: function () {
                btn.prop('disabled', false).removeClass('opacity-60');
            }
        });
    }

    $(document).ready(function () {
        const btn = document.getElementById('updateNotificationBtn');
        if (!btn) return;

        loadNotifications();

        setInterval(function () {
            if (!document.hidden) {
                loadNotifications();
            }
        }, POLL_INTERVAL_MS);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                loadNotifications();
            }
        });

        $(document).on('click', '#updateNotificationBtn', function () {
            if (isOpen()) {
                closePanel();
                return;
            }
            openPanel();
            markAllSeen();
        });

        $(document).on('click', '#updateNotificationCloseBtn, #updateNotificationOverlay', function () {
            closePanel();
        });

        $(document).on('click', '#updateNotificationToastClose', function () {
            hidePopup();
        });

        $(document).on('click', '#updateNotificationToastSeeMore', function () {
            hidePopup();
            openPanel();
            markAllSeen();
        });

        $(document).on('click', '.update-type-option', function () {
            selectType($(this).data('type'));
        });

        $(document).on('click', '#updateNotificationBoldBtn', function (e) {
            e.preventDefault();
            wrapSelection('**');
        });

        $(document).on('click', '#updateNotificationItalicBtn', function (e) {
            e.preventDefault();
            wrapSelection('*');
        });

        $(document).on('click', '#updateNotificationSubmitBtn', function () {
            submitNotification();
        });
    });
})();
