<div class="w-full rounded-xl border bg-white px-5 py-4">

    <!-- HEADER -->
    <div class="mb-5 flex items-start justify-between">
        <div>
            <h3 class="text-lg font-semibold">Today’s Summary</h3>
            <p class="text-sm text-gray-500">
                Choose up to 5 analytics cards
            </p>
            <p id="dashboardCardCounter" class="mt-1 text-xs text-gray-400">
                Selected: 0 / 5
            </p>
        </div>

        <button id="btnAddDashboardCard" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white">
            + Add Card
        </button>
    </div>

    <!-- CARDS -->
    <div id="dashboardCardContainer" class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="empty-state text-sm text-gray-400">
            No analytics added yet.
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="addDashboardCardModal" class="fixed inset-0 z-50 hidden bg-black/40">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-lg rounded-xl bg-white p-5">
            <h3 class="mb-1 text-lg font-semibold">
                Add Analytics Card
            </h3>
            <p class="mb-4 text-sm text-gray-500">
                Select cards to show on dashboard
            </p>

            <div id="dashboardCardList" class="space-y-3"></div>

            <div class="mt-4 text-right">
                <button id="closeDashboardCardModal" class="rounded bg-gray-200 px-4 py-2 text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(async function() {

        /* ================= CONFIG ================= */
        const MAX_CARD = 5;
        const charts = {};
        const cache = {};

        /* ================= MASTER CARD REGISTRY ================= */
        const MASTER_CARDS = {

            po_status: {
                title: 'PO Status',
                type: 'chart',
                chartType: 'pie',
                query: '/api/analytics/po',
                transform: (data) => ({
                    labels: data.po_status.labels,
                    datasets: [{
                        data: data.po_status.data,
                        backgroundColor: ['#6366F1', '#22C55E', '#F59E0B']
                    }]
                })
            },

            total_po_amount: {
                title: 'Total PO Amount',
                type: 'number',
                query: '/api/analytics/po',
                transform: data => formatRupiah(data.total_po_amount)
            },

            po_count: {
                title: 'PO Count',
                type: 'number',
                query: '/api/analytics/po',
                transform: (data) => data.po_count
            },

            po_by_company: {
                title: 'PO by Company',
                type: 'chart',
                chartType: 'bar',
                query: '/api/analytics/po',
                transform: (data) => ({
                    labels: data.po_by_company.map(i => i.cpny_id),
                    datasets: [{
                        data: data.po_by_company.map(i => i.total),
                        backgroundColor: '#6366F1'
                    }]
                })
            },

            po_trend: {
                title: 'PO Trend',
                type: 'chart',
                chartType: 'line',
                query: '/api/analytics/po',
                transform: (data) => ({
                    labels: data.po_trend.map(i => i.podate),
                    datasets: [{
                        data: data.po_trend.map(i => i.total),
                        borderColor: '#6366F1',
                        tension: 0.3
                    }]
                })
            },

            top_po: {
                title: 'Top PO',
                type: 'table',
                query: '/api/analytics/po',
                transform: (data) => data.top_po
            },
            po_pending_aging: {
                title: 'Pending PO Aging',
                type: 'chart',
                chartType: 'bar',
                query: '/api/analytics/po',
                transform: data => ({
                    labels: data.po_pending_aging.map(i => i.bucket),
                    datasets: [{
                        label: 'Pending PO',
                        data: data.po_pending_aging.map(i => i.total),
                        backgroundColor: '#F59E0B'
                    }]
                })
            },
            po_created_vs_completed: {
                title: 'Created vs Completed (30 days)',
                type: 'chart',
                chartType: 'bar',
                query: '/api/analytics/po',
                transform: data => ({
                    labels: ['Created', 'Completed'],
                    datasets: [{
                        data: [
                            data.po_created_vs_completed.created,
                            data.po_created_vs_completed.completed
                        ],
                        backgroundColor: ['#6366F1', '#22C55E']
                    }]
                })
            },
            po_avg_completion_days: {
                title: 'Avg PO Completion Time',
                type: 'number',
                query: '/api/analytics/po',
                transform: data => `${data.po_avg_completion_days} days`
            },
            po_vendor_count: {
                title: 'Top Vendors (PO Count)',
                type: 'chart',
                chartType: 'bar',
                query: '/api/analytics/po',
                transform: data => ({
                    labels: data.po_vendor_count.map(i => i.vendorname),
                    datasets: [{
                        data: data.po_vendor_count.map(i => i.total),
                        backgroundColor: '#6366F1'
                    }]
                })
            },
            po_completion_rate: {
                title: 'PO Completion Rate',
                type: 'number',
                query: '/api/analytics/po',
                transform: data => `${data.po_completion_rate}%`
            },


        };

        /* ================= HELPERS ================= */
        const count = () =>
            $('#dashboardCardContainer .dashboard-card').length;

        const isAdded = (id) =>
            $(`.dashboard-card[data-id="${id}"]`).length > 0;

        const updateCounter = () =>
            $('#dashboardCardCounter')
            .text(`Selected: ${count()} / ${MAX_CARD}`);

        async function fetchQuery(query) {
            if (!cache[query]) {
                const res = await fetch(query);
                cache[query] = await res.json();
            }
            return cache[query];
        }

        /* ================= MODAL ================= */
        $('#btnAddDashboardCard').click(() => {
            renderModal();
            $('#addDashboardCardModal').removeClass('hidden');
        });

        $('#closeDashboardCardModal').click(() =>
            $('#addDashboardCardModal').addClass('hidden')
        );

        function formatRupiah(value) {
            if (value === null || value === undefined) return '-';

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }

        function renderModal() {
            const $list = $('#dashboardCardList').empty();

            Object.entries(MASTER_CARDS).forEach(([id, card]) => {
                const added = isAdded(id);

                $list.append(`
                <div class="flex items-center justify-between rounded-lg border p-3">
                    <span class="font-medium">${card.title}</span>
                    <button
                        class="btnAddCard px-3 py-1 text-sm rounded
                        ${added ? 'bg-gray-200 text-gray-400' : 'bg-indigo-600 text-white'}"
                        data-id="${id}"
                        ${added ? 'disabled' : ''}>
                        ${added ? 'Added' : 'Add'}
                    </button>
                </div>
            `);
            });
        }

        /* ================= ADD CARD ================= */
        $(document).on('click', '.btnAddCard', async function() {
            if (count() >= MAX_CARD) return;
            const id = $(this).data('id');
            await renderCard(id);
            updateCounter();
            renderModal();
        });

        /* ================= REMOVE CARD ================= */
        $(document).on('click', '.btnRemoveCard', function() {
            const id = $(this).data('id');

            charts[id]?.destroy();
            delete charts[id];

            $(`.dashboard-card[data-id="${id}"]`).remove();

            if (!count()) {
                $('#dashboardCardContainer').html(`
                <div class="empty-state text-sm text-gray-400">
                    No analytics added yet.
                </div>
            `);
            }

            updateCounter();
            renderModal();
        });

        /* ================= RENDER CARD ================= */
        async function renderCard(cardId) {
            const card = MASTER_CARDS[cardId];
            const raw = await fetchQuery(card.query);
            const result = card.transform(raw);

            $('#dashboardCardContainer .empty-state').remove();

            const base = `
<div class="dashboard-card group relative rounded-2xl bg-white p-5
            shadow-sm ring-1 ring-gray-100 transition hover:shadow-md"
     data-id="${cardId}">

    <button
        class="btnRemoveCard absolute right-4 top-4 hidden text-xs
               text-red-400 group-hover:block hover:text-red-600"
        data-id="${cardId}">
        ✕
    </button>

    <div class="mb-3">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">
            ${card.title}
        </p>
    </div>
`;


            // NUMBER
            if (card.type === 'number') {
                const value =
                    typeof result === 'number' ?
                    result.toLocaleString() :
                    result; // already formatted string

                $('#dashboardCardContainer').append(`
        ${base}
        <div class="mt-2 text-3xl font-semibold text-gray-900">
            ${value}
        </div>
        <div class="mt-1 text-xs text-gray-400">
            Updated today
        </div>
        </div>
    `);
                return;
            }


            // TABLE
            if (card.type === 'table') {
                const rows = result.map(r => `
                <tr class="border-b">
                    <td class="py-1">${r.ponbr}</td>
                    <td>${r.cpny_id}</td>
                    <td class="text-right">
                        ${Number(r.grandtotalamt).toLocaleString()}
                    </td>
                </tr>
            `).join('');

                $('#dashboardCardContainer').append(`
                ${base}
                    <table class="w-full text-sm">${rows}</table>
                </div>
            `);
                return;
            }

            // CHART
            $('#dashboardCardContainer').append(`
            ${base}
                <div class="h-44">
                    <canvas id="chart_${cardId}"></canvas>
                </div>
            </div>
        `);
            charts[cardId] = new Chart(
                document.getElementById(`chart_${cardId}`), {
                    type: card.chartType,
                    data: result,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        layout: {
                            padding: {
                                top: 8,
                                bottom: 0
                            }
                        },

                        plugins: {
                            legend: {
                                position: 'bottom',
                                align: 'center',

                                // 🔑 this helps prevent wrapping
                                maxWidth: 360,

                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    padding: 16,

                                    usePointStyle: true,
                                    pointStyle: 'rect',

                                    color: '#6B7280', // gray-500
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            }
                        }
                    }
                }
            );
        }

        updateCounter();

    });
</script>
