<x-app-layout>

    <div class="mb-4 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 text-lg text-white shadow-sm">
                    📅
                </div>

                <div>
                    <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-200">
                        Event Calendar
                    </h1>
                    <p class="mt-0.5 text-sm text-gray-500">
                        Manage casual leasing, promotion, and operation/internal events
                    </p>
                </div>

            </div>

            <div class="flex items-center gap-2">

                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-1">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#3D8C8C]"></span> Casual Leasing
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#F2A73B]"></span> Promotion Event
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#D65A4A]"></span> Operation/Internal Event
                    </span>
                </div>

                <button type="button" id="openCreateEventModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                    <i class="fa-solid fa-plus text-xs"></i>
                    New Event
                </button>

            </div>

        </div>

    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
        <div class="p-4">
            <div id="calendar"></div>
        </div>
    </div>

    <style>
        /* Linear-style event banners: full-width, square, aligned to day columns */
        #calendar .fc-daygrid-day-frame {
            padding-left: 0;
            padding-right: 0;
            min-height: 90px;
        }

        #calendar .fc-daygrid-body tr {
            height: auto;
        }

        #calendar .fc-daygrid-day-top {
            padding: 6px 8px 0;
        }

        #calendar .fc-daygrid-day-events {
            padding: 0;
            margin-top: 2px;
        }

        #calendar .fc-daygrid-event-harness {
            margin: 0 !important;
        }

        #calendar .fc-daygrid-event-harness + .fc-daygrid-event-harness {
            margin-top: 1px !important;
        }

        #calendar .fc-event {
            border-radius: 0 !important;
            margin: 0 !important;
            padding: 4px 8px !important;
            font-weight: 600;
        }

        #calendar .fc-daygrid-more-link {
            margin: 2px 8px 0 !important;
        }

        /* Keep month-grid bars flat pastel; the colored border is only
           meant to drive the list view's dot indicator */
        #calendar .fc-daygrid-event {
            border-color: transparent !important;
        }

        /* Tentative events render at half opacity; finalized events stay solid */
        #calendar .fc-event.fc-event-tentative {
            opacity: 0.55 !important;
        }

        /* Event bar content: ID badge + title + location meta + creator avatar */
        #calendar .fc-event-main-frame {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
        }

        #calendar .fc-event-id-badge {
            flex-shrink: 0;
            font-size: 10px;
            font-weight: 700;
            opacity: 0.8;
        }

        #calendar .fc-event-main-frame .fc-event-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #calendar .fc-event-main-frame .fc-event-meta {
            flex-shrink: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            opacity: 0.75;
        }

        #calendar .fc-event-creator-avatar {
            flex-shrink: 0;
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.14);
            font-size: 9px;
            font-weight: 700;
            line-height: 1;
        }

        /* List view: badges sit right next to the title instead of
           being pushed to the far edge of the wide title cell */
        #calendar .fc-list-event-title .fc-event-main-frame {
            display: inline-flex;
            width: auto;
        }

        #calendar .fc-list-event-title .fc-event-creator-avatar {
            margin-left: 6px;
        }
    </style>

    {{-- CREATE / EDIT MODAL --}}
    <div id="eventModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-3xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <form id="eventForm" class="flex flex-col">

                @csrf

                <input type="hidden" id="event_row_id" name="id">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>
                        <h2 id="eventModalTitle" class="text-sm font-bold text-slate-900 dark:text-white">
                            Create Event
                        </h2>
                        <p id="eventModalSubtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Schedule a new calendar event.
                        </p>
                    </div>

                    <button type="button" id="closeEventModal"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                </div>

                <div class="space-y-4 bg-slate-50 p-5 dark:bg-[#0b1220]">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Event Name *
                            </label>
                            <input type="text" id="event_name" name="event_name" required
                                placeholder="e.g. Ramadan Bazaar"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Tenant / Event Company Name
                            </label>
                            <input type="text" id="event_company_name" name="event_company_name"
                                placeholder="e.g. PT Sinar Jaya"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Company *
                            </label>
                            <select id="cpnyid" name="cpnyid" required
                                class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                <option value="">Select Company</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Location *
                            </label>
                            <select id="location_id" name="location_id" required
                                class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                <option value="">Select Company first</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Sub Location
                            </label>
                            <select id="sub_location_id" name="sub_location_id"
                                class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                <option value="">Select Location first</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Event Type *
                            </label>
                            <select id="event_type" name="event_type" required
                                class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                <option value="">Select Event Type</option>
                                @foreach ($eventTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Event Status *
                            </label>
                            <select id="event_status" name="event_status" required
                                class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                <option value="">Select Event Status</option>
                                @foreach ($eventStatuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Start Date *
                            </label>
                            <input type="date" id="event_start_date" name="event_start_date" required
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                End Date *
                            </label>
                            <input type="date" id="event_end_date" name="event_end_date" required
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

                        <div class="hidden">
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Product Check Expiry
                            </label>
                            <input type="date" id="product_check_exp" name="product_check_exp"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div class="flex items-end">
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

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Description
                        </label>
                        <textarea id="event_description" name="event_description" rows="3"
                            placeholder="Describe the event..."
                            class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white"></textarea>
                    </div>

                </div>

                <div
                    class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <div class="flex items-center justify-between">

                        <button type="button" id="deleteEventBtn"
                            class="hidden text-sm font-semibold text-red-600 hover:text-red-700">
                            Delete Event
                        </button>

                        <div class="ml-auto flex items-center gap-3">
                            <button type="button" id="cancelEventBtn"
                                class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                                Cancel
                            </button>

                            <button type="submit"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                Save Event
                            </button>
                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <script>
        window.EventCalendarLocations = {!! json_encode($locations->map(fn($l) => [
            'cpny_id' => $l->cpny_id,
            'location_id' => $l->location_id,
            'location_name' => $l->location_name,
        ])) !!};

        window.EventCalendarSubLocations = {!! json_encode($subLocations->map(fn($s) => [
            'cpny_id' => $s->cpny_id,
            'location_id' => $s->location_id,
            'sub_location_id' => $s->sub_location_id,
            'sub_location_name' => $s->sub_location_name,
        ])) !!};

        window.EventCalendarRoutes = {
            json: '{{ route('event-calendar.json') }}',
            store: '{{ route('event-calendar.store') }}',
            update: (id) => `{{ url('event-calendar/update') }}/${id}`,
            status: (id) => `{{ url('event-calendar/status') }}/${id}`,
            destroy: (id) => `{{ url('event-calendar/destroy') }}/${id}`,
        };
    </script>

    <script src="{{ asset('assets/js/event_calendar/calendar.js') }}"></script>

</x-app-layout>
