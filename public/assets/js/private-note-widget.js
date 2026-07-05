(function () {
    let currentDoctype = null;
    let currentRefnbr = null;

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

    function setTarget(doctype, refnbr, label) {
        currentDoctype = doctype;
        currentRefnbr = refnbr;
        $('#privateNoteTarget').text(label ? ` — ${label}` : (refnbr ? ` — ${refnbr}` : ''));
    }

    function isOpen() {
        return $('#privateNotePanel').hasClass('flex');
    }

    function openPanel() {
        $('#privateNotePanel').removeClass('hidden').addClass('flex');
    }

    function closePanel() {
        $('#privateNotePanel').removeClass('flex').addClass('hidden');
    }

    function updateToggleBadge(count) {
        const badge = $('#privateNoteToggleBadge');
        if (count > 0) {
            badge.text(count > 99 ? '99+' : count).removeClass('hidden');
        } else {
            badge.addClass('hidden');
        }
        $(document).trigger('privatenote:count-updated', [currentDoctype, currentRefnbr, count]);
    }

    function loadNotes() {
        const list = $('#privateNoteList');

        if (!currentDoctype || !currentRefnbr) {
            list.html('<p class="py-4 text-center text-sm italic text-gray-500">Select a document to view notes.</p>');
            return;
        }

        list.html(`
            <div class="flex h-full flex-col items-center justify-center gap-2 text-gray-400">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-xs italic">Loading notes...</span>
            </div>
        `);

        $.ajax({
            url: `/private-notes/${currentDoctype}/${currentRefnbr}`,
            type: 'GET',
            success: function (response) {
                list.empty();

                if (!response.notes || response.notes.length === 0) {
                    list.append('<p class="py-4 text-center text-sm italic text-gray-500">No private notes yet.</p>');
                    updateToggleBadge(0);
                    return;
                }

                updateToggleBadge(response.notes.length);

                response.notes.forEach(function (note) {
                    const ago = timeAgo(note.message_date || note.created_at);
                    list.append(`
                        <div class="mb-2 rounded-lg border border-gray-200 bg-gray-100 p-3 dark:border-gray-600 dark:bg-gray-700/50">
                            <p class="text-sm font-semibold">
                                ${note.username}
                                <span class="text-sm text-gray-500">(${ago})</span>
                            </p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">${note.message}</p>
                        </div>
                    `);
                });
            },
            error: function (xhr) {
                console.error('Error fetching private notes:', xhr.responseText);
                list.html('<p class="text-red-500 italic">Failed to load private notes.</p>');
            }
        });
    }

    function addNote() {
        if (!currentDoctype || !currentRefnbr) return;

        const input = $('#privateNoteInput');
        const value = input.val().trim();
        if (value === '') {
            alert('Please enter a note.');
            return;
        }

        const btn = $('#postPrivateNoteBtn');
        btn.prop('disabled', true).addClass('opacity-60').html(
            '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>'
        );

        $.ajax({
            url: `/private-notes/${currentDoctype}/${currentRefnbr}`,
            type: 'POST',
            data: {
                note: value,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status === 'success') {
                    input.val('');
                    loadNotes();
                }
            },
            error: function (xhr) {
                console.error('Error adding private note:', xhr);
                alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown Error'));
            },
            complete: function () {
                btn.prop('disabled', false).removeClass('opacity-60').html('➤');
            }
        });
    }

    window.PrivateNote = {
        open: function (doctype, refnbr, label) {
            setTarget(doctype, refnbr, label);
            $('#privateNoteToggleBtn').removeClass('hidden');
            openPanel();
            loadNotes();
        },
        close: closePanel,
    };

    $(document).ready(function () {
        const widget = document.getElementById('privateNoteWidget');
        if (!widget) return;

        const initialDoctype = widget.dataset.doctype || null;
        const initialRefnbr = widget.dataset.refnbr || null;
        if (initialRefnbr) {
            setTarget(initialDoctype, initialRefnbr, null);
        }

        $(document).on('click', '#privateNoteToggleBtn', function () {
            if (isOpen()) {
                closePanel();
                return;
            }
            if (!currentRefnbr) return;
            openPanel();
            loadNotes();
        });

        $(document).on('click', '#privateNoteCloseBtn', function () {
            closePanel();
        });

        $(document).on('click', '#postPrivateNoteBtn', function (e) {
            e.preventDefault();
            addNote();
        });

        $(document).on('keypress', '#privateNoteInput', function (event) {
            if (event.which === 13 && !event.shiftKey) {
                event.preventDefault();
                addNote();
            }
        });
    });
})();
