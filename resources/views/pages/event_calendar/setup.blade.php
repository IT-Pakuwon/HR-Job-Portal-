<x-app-layout>
    <x-spp-list-styles />
    <x-spp-form-styles />
    <x-event-form-styles />
    <style>
        /* the global table margin pushed the Actions column past the card edge */
        #locationTable { margin-left: 0 !important; margin-right: 0 !important; }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Event Location Setup" subtitle="Manage the locations available for calendar events"
                icon='<i class="fa-solid fa-map-location-dot"></i>'>
                <div class="sppb-f relative w-64">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-building text-xs"></i></span>
                    <select id="filterCompany" aria-label="Company">
                        <option value="">All Companies</option>
                        @foreach ($companies as $c)
                            <option value="{{ $c->cpny_name }}">{{ $c->cpny_name }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ route('event-calendar') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Back to Calendar
                </a>

                <button type="button" id="openCreateLocationModal" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="fa-solid fa-plus text-xs"></i>
                    New Location
                </button>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="locationTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Location ID</th>
                            <th class="px-4 py-3 text-left font-medium">Location Name</th>
                            <th class="px-4 py-3 text-left font-medium">Total Area (m²)</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="w-24 px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CREATE / EDIT MODAL --}}
    <div id="locationModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4">

        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel relative z-10 flex max-h-[92vh] w-full translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10 max-w-lg">

            <form id="locationForm" class="flex min-h-0 flex-col">

                @csrf

                <input type="hidden" id="location_row_id" name="id">

                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <h2 id="locationModalTitle" class="text-base font-bold text-gray-800 dark:text-white">Create Event Location</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Area that events can be booked into</p>
                        </div>
                    </div>
                    <button type="button" id="closeLocationModal" aria-label="Close"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <div class="modal-scroll min-h-0 space-y-4 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-900/60">

                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div class="sf-field sm:col-span-2">
                                <label for="cpny_id" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <select id="cpny_id" name="cpny_id" required class="select2 w-full">
                                    <option value="">Select Company</option>
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="event_location_name" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location Name</label>
                                <input type="text" id="event_location_name" name="event_location_name" required placeholder="e.g. Ground Floor Atrium"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <div>
                                <label for="event_location_id" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location ID</label>
                                <input type="text" id="event_location_id" name="event_location_id" readonly placeholder="Auto-generated on save"
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-400">
                            </div>

                            <div>
                                <label for="event_total_area" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Area (m²)</label>
                                <input type="number" step="0.01" min="0" id="event_total_area" name="event_total_area" placeholder="0.00"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <div class="ev-ts sm:col-span-2">
                                <label for="event_department_id" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select id="event_department_id" name="event_department_id[]" multiple class="w-full">
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="flex w-full cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 transition has-[:checked]:border-[#bbf7d0] has-[:checked]:bg-[#f0fdf4] dark:border-gray-600 dark:bg-gray-700/40 dark:has-[:checked]:border-green-500/30 dark:has-[:checked]:bg-green-500/10">
                                    <input type="checkbox" id="status_active" checked
                                        class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-400 dark:border-gray-500">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Active</span>
                                    <span class="text-xs text-gray-400">Only active locations show on the calendar</span>
                                </label>
                                <input type="hidden" id="status" name="status" value="A">
                            </div>

                        </div>
                    </div>

                </div>

                <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" id="deleteLocationBtn" class="hidden inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        Delete Location
                    </button>

                    <div class="ml-auto flex items-center gap-3">
                        <button type="button" id="cancelLocationBtn" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>

                        <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Save Location
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        window.EventLocationSetupRoutes = {
            json: '{{ route('event-calendar.setup.json') }}',
            store: '{{ route('event-calendar.setup.store') }}',
            update: (id) => `{{ url('event-calendar/setup/update') }}/${id}`,
            destroy: (id) => `{{ url('event-calendar/setup/destroy') }}/${id}`,
        };
    </script>

    <script src="{{ asset('assets/js/event_calendar/setup.js') }}?v={{ filemtime(public_path('assets/js/event_calendar/setup.js')) }}"></script>

</x-app-layout>
