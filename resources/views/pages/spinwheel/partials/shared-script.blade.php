{{-- Shared state, utilities and Winner History DataTable wiring for the admin and audience spinwheel screens.
     Each view must define its own currentEventId() and resetRouletteIdle() before this runs. --}}

<script>
    // event currently marked "live" by the operator (server-rendered on load, kept in sync via polling/actions below)
    let liveEventId = @json($activeEvent->event_id ?? null);

    let tableWinner;
    let spinning = false;
    let lastSeenBatchId = null;
    let currentBatchId = null;
    let currentDisplayCombo = 'name_company';

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function candidateLabel(candidate, combo) {

        const name = escapeHtml(candidate.customer_name);
        const company = escapeHtml(candidate.company_name);
        const refNbr = escapeHtml(candidate.ref_nbr);

        if (combo === 'name_refnbr') {
            return `${name}${refNbr ? ' — ' + refNbr : ''}`;
        }

        return `${name}${company ? ' — ' + company : ''}`;

    }

    function applySelect2(el, options = {}) {

        if (el.hasClass('select2-hidden-accessible')) {
            el.select2('destroy');
        }

        el.select2($.extend({
            width: '100%',
            dropdownParent: $('body'),
            dropdownCssClass: 'sw-select2-dropdown'
        }, options));

    }

    const swalDarkTheme = {
        background: '#171532',
        color: '#f1f5f9'
    };

    function showLoading(title = 'Processing...') {
        Swal.fire({
            title,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            ...swalDarkTheme
        });
    }

    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 1500,
            showConfirmButton: false,
            ...swalDarkTheme
        });
    }

    function showError(message = 'Something went wrong') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#a855f7',
            ...swalDarkTheme
        });
    }

    function fireConfetti() {
        if (typeof confetti === 'function') {
            confetti({
                particleCount: 150,
                spread: 90,
                origin: {
                    y: 0.6
                }
            });
        }
    }

    function showCongratsPopup(candidates, combo) {

        const rows = candidates.map(candidate => {

            const name = escapeHtml(candidate.customer_name);
            const detailLabel = combo === 'name_refnbr' ? 'Ref Nbr' : 'Company';
            const detailValue = combo === 'name_refnbr' ?
                escapeHtml(candidate.ref_nbr) :
                (escapeHtml(candidate.company_name) || '-');

            return `
                <div class="mt-3 rounded-xl border border-fuchsia-400/20 border-l-4 border-l-fuchsia-400 bg-white/5 p-3 text-left">
                    <div class="text-base font-bold text-white">${name}</div>
                    <div class="mt-0.5 text-sm text-slate-400">${detailLabel}: <span class="font-semibold text-slate-200">${detailValue}</span></div>
                </div>
            `;

        }).join('');

        fireConfetti();

        Swal.fire({
            icon: 'success',
            title: '🎉 Congratulations!',
            html: `<div class="text-left">${rows}</div>`,
            confirmButtonText: 'Continue',
            confirmButtonColor: '#ec4899',
            ...swalDarkTheme
        });

    }

    function loadSummary(eventId) {

        $.get(`/spinwheel/summary/${eventId}`, function(response) {

            $('#statTotalEntries').text(response.total_entries);
            $('#statEligible').text(response.eligible_participants);
            $('#statWinners').text(response.winners_drawn);

            window.sampleNames = response.sample_names ?? [];

            // admin console has no roulette visual, so it doesn't define resetRouletteIdle()
            if (!spinning && typeof resetRouletteIdle === 'function') {
                resetRouletteIdle();
            }

        });

    }

    function isSpinwheelFullscreen() {
        return !!(document.fullscreenElement || document.webkitFullscreenElement);
    }

    $('#fullscreenBtn').on('click', function() {

        const root = document.getElementById('spinwheelRoot');

        if (!isSpinwheelFullscreen()) {
            (root.requestFullscreen || root.webkitRequestFullscreen).call(root);
        } else {
            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
        }

    });

    $(document).on('fullscreenchange webkitfullscreenchange', function() {
        $('#fullscreenBtn')
            .text(isSpinwheelFullscreen() ? '⤢' : '⛶')
            .attr('title', isSpinwheelFullscreen() ? 'Exit Fullscreen' : 'Toggle Fullscreen');
    });

    $('#toggleHistoryBtn').on('click', function() {

        const hidden = $('#winnerHistoryPanel').toggleClass('hidden').hasClass('hidden');

        $('#drawWinnersPanel')
            .toggleClass('lg:col-span-3', !hidden)
            .toggleClass('lg:col-span-5', hidden);

        $('#toggleHistoryLabel').text(hidden ? 'Show Table' : 'Hide Table');

    });

    function initWinnerTable() {

        return $('#tableWinner').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,

            ajax: function(data, callback) {

                const eventId = currentEventId();

                if (!eventId) {
                    callback({
                        data: [],
                        recordsTotal: 0,
                        recordsFiltered: 0
                    });
                    return;
                }

                data.event_id = eventId;

                $.get('{{ route('spinwheel.winnerJson') }}', data, callback);

            },

            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '5%'
                },
                {
                    data: 'ref_nbr',
                    name: 'ref_nbr',
                    render: function(data) {
                        return `<span class="font-mono text-sm text-slate-400">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    render: function(data) {
                        return `<span class="font-semibold text-white">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    data: 'prize_name',
                    name: 'prize_name',
                    render: function(data) {
                        return `<span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-fuchsia-500 to-purple-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm">🏆 ${escapeHtml(data ?? '-')}</span>`;
                    }
                }
            ]
        });

    }
</script>
