<x-app-layout>

    <div class="mb-4 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 text-lg text-white shadow-sm">
                    ⚙️
                </div>

                <div>
                    <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-200">
                        Event Location Setup
                    </h1>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Manage the locations available for calendar events
                    </p>
                </div>

            </div>

            <div class="flex items-center gap-2">

                <a href="{{ route('event-calendar') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Back to Calendar
                </a>

                <button type="button" id="openCreateLocationModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                    <i class="fa-solid fa-plus text-xs"></i>
                    New Location
                </button>

            </div>

        </div>

    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
            <thead class="bg-gray-50 dark:bg-white/[0.03]">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Location ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Location Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Area (m²)</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody id="locationTableBody" class="divide-y divide-gray-200 dark:divide-white/10">
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">Loading...</td>
                </tr>
            </tbody>
        </table>

    </div>

    {{-- CREATE / EDIT MODAL --}}
    <div id="locationModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel relative z-10 w-full max-w-lg translate-y-4 scale-[0.98] rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <form id="locationForm" class="flex flex-col">

                @csrf

                <input type="hidden" id="location_row_id" name="id">

                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-white/10">
                    <h2 id="locationModalTitle" class="text-sm font-bold text-slate-900 dark:text-white">
                        Create Event Location
                    </h2>
                    <button type="button" id="closeLocationModal"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 bg-slate-50 p-6 dark:bg-[#0b1220]">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Company *
                        </label>
                        <select id="cpny_id" name="cpny_id" required
                            class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                            <option value="">Select Company</option>
                            @foreach ($companies as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Location ID *
                        </label>
                        <input type="text" id="event_location_id" name="event_location_id" required
                            placeholder="e.g. LOC001"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Location Name *
                        </label>
                        <input type="text" id="event_location_name" name="event_location_name" required
                            placeholder="e.g. Ground Floor Atrium"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Total Area (m²)
                        </label>
                        <input type="number" step="0.01" min="0" id="event_total_area" name="event_total_area"
                            placeholder="0.00"
                            class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Department
                        </label>
                        <select id="event_department_id" name="event_department_id[]" multiple
                            class="w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                            @foreach ($departments as $d)
                                <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex cursor-pointer items-center gap-2.5">
                            <input type="checkbox" id="status_active" checked
                                class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400 dark:border-white/20">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Active
                            </span>
                        </label>
                        <input type="hidden" id="status" name="status" value="A">
                    </div>

                </div>

                <div class="border-t border-slate-200 px-6 py-4 dark:border-white/10">
                    <div class="flex items-center justify-between">

                        <button type="button" id="deleteLocationBtn"
                            class="hidden text-sm font-semibold text-red-600 hover:text-red-700">
                            Delete Location
                        </button>

                        <div class="ml-auto flex items-center gap-3">
                            <button type="button" id="cancelLocationBtn"
                                class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                                Cancel
                            </button>

                            <button type="submit"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                Save Location
                            </button>
                        </div>

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
