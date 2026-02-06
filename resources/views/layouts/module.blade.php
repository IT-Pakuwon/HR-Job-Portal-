@php $noBack = true; @endphp
<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <!-- HEADER -->
    <x-app.header variant="v2" /> --}}

    <!-- MAIN WRAPPER (LOCKED SCREEN, NO SCROLL) -->
    <div class="flex h-screen w-full flex-col gap-6 overflow-hidden bg-gray-100 dark:bg-gray-900">

        {{-- ================= LEFT : APPLICATION MODULES ================= --}}

        <div class="grid h-[50%] grid-cols-1 gap-4 md:gap-6 lg:grid-cols-1 xl:grid-cols-1">
            {{-- <div class="flex flex-col gap-4 rounded-xl border bg-white p-4 dark:border-gray-600 dark:bg-gray-800">


                <!-- TITLE -->
                <div class="shrink-0">
                    <h1 class="text-lg font-bold text-gray-900 md:text-lg dark:text-gray-100">Application Modules
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Select a module to continue</p>
                </div>

                @php
                    /* MOCK COUNTS */ $counts = [
                        'recruitment' => 3,
                        'applicants' => 5,
                        'purchase' => 2,
                        'warehouse' => 1,
                        'request' => 0,
                        'workorder' => 0,
                        'bast' => 0,
                        'rfca' => 0,
                        'calr' => 0,
                    ];
                    $card =
                        ' group relative cursor-pointer rounded-xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm flex flex-col items-center justify-center text-center ';
                    $icon = 'mb-2 text-lg';
                    $label = ' text-sm  font-semibold text-gray-800';
                    $badge =
                        ' absolute top-2 right-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5  text-sm  font-semibold text-white shadow ';
                @endphp
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">

                    <div class="group relative">
                        <div
                            class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            @if ($counts['recruitment'] > 0)
                                <span
                                    class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                    {{ $counts['recruitment'] }}
                                </span>
                            @endif
                            <div class="mb-2 text-lg">👥</div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recruitment</div>
                        </div>

                        <div
                            class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                            <a href="{{ route('personnels') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                PRF
                            </a>

                            <a href="{{ route('jobapplicant') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                Applicant Portal
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('applicants') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['applicants'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                {{ $counts['applicants'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">🧾</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Applicants</div>
                    </a>

                    <div class="group relative">
                        <div
                            class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            @if ($counts['purchase'] > 0)
                                <span
                                    class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                    {{ $counts['purchase'] }}
                                </span>
                            @endif
                            <div class="mb-2 text-lg">🛒</div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Purchase</div>
                        </div>

                        <div
                            class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                            <a href="{{ route('polist') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                PO List
                            </a>

                            <a href="{{ route('receiptlist') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                Receipt List
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('wos') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">🛠️</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Work Order</div>
                    </a>

                    <a href="{{ route('spbs') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['warehouse'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                {{ $counts['warehouse'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">📦</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Warehouse</div>
                    </a>

                    <a href="{{ route('bastlist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">📑</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">BAST</div>
                    </a>

                    <a href="{{ route('rfcalist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">💵</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">RFCA</div>
                    </a>

                    <a href="{{ route('calrlist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">📝</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">CALR</div>
                    </a>

                    <a href="https://mail3.pakuwon.com/" target="#"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">


                        <svg class="h-8 w-8 text-indigo-500 transition group-hover:scale-110" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75m19.5 0L12 13.5 2.25 6.75" />
                        </svg>

                        <span class="mt-3 text-sm font-medium text-gray-700">
                            Email
                        </span>
                    </a>

                    <a href="https://pakuwon.isort.id/login" target="_blank"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">

                        <svg class="h-8 w-8 text-indigo-500 transition group-hover:scale-110" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v18h18M7.5 15v-6m4.5 6V6m4.5 9v-3" />
                        </svg>

                        <span class="mt-3 text-sm font-medium text-gray-700">
                            ISort
                        </span>
                    </a>


                </div>
            </div> --}}


            @include('partials.calendar-widget')

        </div>

        <!-- ================= Today’s Summary ================= -->
        <div
            class="grid h-[50%] w-full grid-cols-1 gap-6 rounded-xl border bg-white px-4 py-4 dark:border-gray-600 dark:bg-gray-800">

            <!-- Header -->
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Today’s Summary
                    </h3>
                    <p class="text-sm text-gray-500">
                        Choose up to 5 analytics cards to build your daily dashboard.
                    </p>
                    <p id="dashboardCardCounter" class="mt-1 text-xs text-gray-400">
                        Selected: 0 / 5
                    </p>
                </div>

                <button id="btnAddDashboardCard"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    + Add Card
                </button>
            </div>

            <!-- Dashboard Cards -->
            <div id="dashboardCardContainer" class="grid min-h-[120px] grid-cols-1 gap-4 md:grid-cols-2">
                <div class="text-sm text-gray-400">
                    No analytics added yet.
                </div>
            </div>
        </div>

        <!-- ================= Add Analytics Card Modal ================= -->
        <div id="addDashboardCardModal" class="fixed inset-0 z-50 hidden">
            <div class="flex min-h-screen items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-lg">

                    <!-- Modal Header -->
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Add Analytics Card</h3>
                        <p class="text-sm text-gray-500">
                            Select up to 5 cards to customize your dashboard.
                        </p>
                    </div>

                    <!-- Card List -->
                    <div id="dashboardCardList" class="space-y-3"></div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end">
                        <button id="closeDashboardCardModal"
                            class="rounded-md bg-gray-100 px-4 py-2 text-sm hover:bg-gray-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // ================= Card Registry =================
            const DASHBOARD_CARDS = [{
                    id: 'po_status',
                    title: 'PO Status',
                    description: 'Waiting vs Approved',
                    chartType: 'pie'
                },
                {
                    id: 'weekly_orders',
                    title: 'Weekly Orders',
                    description: 'Orders per day',
                    chartType: 'bar'
                },
                {
                    id: 'pending_tasks',
                    title: 'Pending Tasks',
                    description: 'Today pending tasks',
                    chartType: 'bar'
                },
                {
                    id: 'notification_summary',
                    title: 'Notifications',
                    description: 'Unread notifications',
                    chartType: 'line'
                }
            ];

            const MAX_DASHBOARD_CARDS = 5;
            let selectedDashboardCards = [];
            const dashboardCharts = {};

            // ================= Counter =================
            function updateDashboardCounter() {
                $('#dashboardCardCounter').text(
                    `Selected: ${selectedDashboardCards.length} / ${MAX_DASHBOARD_CARDS}`
                );
            }

            // ================= Modal Open / Close =================
            $('#btnAddDashboardCard').on('click', function() {
                renderDashboardCardList();
                $('#addDashboardCardModal').removeClass('hidden');
            });

            $('#closeDashboardCardModal').on('click', function() {
                $('#addDashboardCardModal').addClass('hidden');
            });

            // ================= Render Modal Card List =================
            function renderDashboardCardList() {
                const $list = $('#dashboardCardList');
                $list.empty();

                DASHBOARD_CARDS.forEach(card => {
                    const isAdded = selectedDashboardCards.includes(card.id);
                    const isDisabled = !isAdded && selectedDashboardCards.length >= MAX_DASHBOARD_CARDS;

                    $list.append(`
                <div class="flex items-center justify-between rounded-lg border p-3">
                    <div>
                        <div class="font-medium">${card.title}</div>
                        <div class="text-sm text-gray-500">${card.description}</div>
                    </div>

                    <button
                        class="btnAddCard rounded-md px-3 py-1.5 text-sm
                        ${isAdded ? 'bg-gray-300 text-gray-600' : 'bg-indigo-600 text-white hover:bg-indigo-700'}
                        ${isDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                        data-id="${card.id}"
                        ${isAdded || isDisabled ? 'disabled' : ''}>
                        ${isAdded ? 'Added' : 'Add'}
                    </button>
                </div>
            `);
                });
            }

            // ================= Add Card =================
            $(document).on('click', '.btnAddCard', function() {
                const cardId = $(this).data('id');

                if (
                    selectedDashboardCards.length >= MAX_DASHBOARD_CARDS ||
                    selectedDashboardCards.includes(cardId)
                ) return;

                selectedDashboardCards.push(cardId);

                renderDashboardCardList();
                renderDashboardCards();
                updateDashboardCounter();
            });

            // ================= Render Dashboard Cards =================
            function renderDashboardCards() {
                const $container = $('#dashboardCardContainer');
                $container.empty();

                if (!selectedDashboardCards.length) {
                    $container.append(`
      <div class="col-span-12 text-sm text-gray-400">
        No analytics added yet.
      </div>
    `);
                    return;
                }

                selectedDashboardCards.forEach((cardId, index) => {
                    const card = DASHBOARD_CARDS.find(c => c.id === cardId);
                    if (!card) return;

                    const canvasId = `chart_${card.id}`;

                    // 🔥 layout rule
                    const colSpan =
                        index < 2 ?
                        'col-span-12 md:col-span-6' :
                        'col-span-12';

                    $container.append(`
      <div class="${colSpan} rounded-lg border bg-white p-4 dark:bg-gray-800">
        <div class="mb-3 flex items-center justify-between">
          <h4 class="font-medium">${card.title}</h4>
          <button class="btnRemoveCard text-sm text-red-500" data-id="${card.id}">
            Remove
          </button>
        </div>
        <div class="relative h-48">
          <canvas id="${canvasId}"></canvas>
        </div>
      </div>
    `);

                    renderChart(card, canvasId);
                });
            }


            // ================= Remove Card =================
            $(document).on('click', '.btnRemoveCard', function() {
                const cardId = $(this).data('id');
                const canvasId = `chart_${cardId}`;

                if (dashboardCharts[canvasId]) {
                    dashboardCharts[canvasId].destroy();
                    delete dashboardCharts[canvasId];
                }

                selectedDashboardCards = selectedDashboardCards.filter(id => id !== cardId);

                renderDashboardCards();
                updateDashboardCounter();
            });

            // ================= Chart Data =================
            function getChartData(cardId) {
                switch (cardId) {
                    case 'po_status':
                        return {
                            labels: ['Waiting', 'Approved'], data: [12, 8]
                        };
                    case 'weekly_orders':
                        return {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], data: [5, 9, 7, 12, 6]
                        };
                    case 'pending_tasks':
                        return {
                            labels: ['Morning', 'Afternoon', 'Evening'], data: [3, 4, 2]
                        };
                    case 'notification_summary':
                        return {
                            labels: ['Unread', 'Read'], data: [6, 14]
                        };
                    default:
                        return {
                            labels: [], data: []
                        };
                }
            }

            function renderChart(card, canvasId) {
                const ctx = document.getElementById(canvasId);
                if (!ctx) return;

                if (dashboardCharts[canvasId]) {
                    dashboardCharts[canvasId].destroy();
                }

                const chartData = getChartData(card.id);

                dashboardCharts[canvasId] = new Chart(ctx, {
                    type: card.chartType,
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: ['#6366F1', '#22C55E', '#F59E0B', '#EF4444', '#3B82F6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: card.chartType !== 'bar'
                            }
                        },
                        scales: card.chartType === 'bar' ? {
                            y: {
                                beginAtZero: true
                            }
                        } : {}
                    }
                });
            }

            // ================= INIT =================
            updateDashboardCounter();

        });
    </script>






</x-app-layout>
