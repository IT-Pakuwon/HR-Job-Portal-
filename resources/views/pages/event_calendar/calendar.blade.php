<x-app-layout>
    <x-spp-form-styles />
    <x-event-form-styles />

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Event Calendar"
                subtitle="Manage casual leasing exhibition, promotion, and mall sales promotion events"
                icon='<i class="fa-solid fa-calendar-days"></i>'>
                <div class="flex flex-wrap items-center gap-2 lg:mr-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FFF6DB] px-3 py-1 text-xs font-semibold text-[#8A6600] ring-1 ring-inset ring-[#F3DF9B]">
                            <span class="h-2 w-2 rounded-full bg-[#8A6600]"></span>Booked
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E4F6EC] px-3 py-1 text-xs font-semibold text-[#1B7A43] ring-1 ring-inset ring-[#BAE3CB]">
                            <span class="h-2 w-2 rounded-full bg-[#1B7A43]"></span>Confirmed
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E5EEFF] px-3 py-1 text-xs font-semibold text-[#2149B3] ring-1 ring-inset ring-[#C3D4FB]">
                            <span class="h-2 w-2 rounded-full bg-[#2149B3]"></span>Paid
                        </span>
                </div>

                {{-- same role the Setup page itself requires (ADEVENTACCESS) --}}
                @if ($isAdmin)
                    <a href="{{ route('event-calendar.setup.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fa-solid fa-gear text-xs"></i>
                        Setup
                    </a>
                @endif

                @if ($canCreate)
                    <button type="button" id="openCreateEventModal" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <i class="fa-solid fa-plus text-xs"></i>
                        New Event
                    </button>
                @endif
            </x-list-page-header>

            <div class="p-4">
                @if ($locations->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 py-16 text-center dark:border-gray-700">
                        <span class="mb-1 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">No event locations available to you yet</p>
                        <p class="text-xs text-gray-400">Ask an admin to set up a location for your company and department.</p>
                    </div>
                @else
                    <div id="calendar"></div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Selectors start with #calendar so they win over the global FullCalendar rules in layouts/calendar. */

        /* ---- Toolbar ---- */
        #calendar .fc-header-toolbar { margin-bottom: 14px; padding: 0; gap: 10px; }
        #calendar .fc-toolbar-title { font-size: 18px; font-weight: 700; letter-spacing: -0.01em; color: #1f2937; }
        #calendar .fc-button-group { display: inline-flex; gap: 0; padding: 0; background: transparent; }
        #calendar .fc-button {
            display: inline-flex; align-items: center; justify-content: center; height: 36px; min-width: 36px; padding: 0 12px !important;
            background: #fff !important; border: 1px solid #d1d5db !important; border-radius: 8px; color: #374151 !important;
            font-size: 13px; font-weight: 600; text-transform: capitalize; box-shadow: 0 1px 2px rgba(0, 0, 0, .05) !important;
            transition: background-color .15s, border-color .15s, color .15s;
        }
        #calendar .fc-button:hover:not(:disabled) { background: #eff6ff !important; border-color: #93c5fd !important; color: #1d4ed8 !important; }
        #calendar .fc-button:focus-visible { outline: 2px solid #93c5fd; outline-offset: 1px; }
        #calendar .fc-button:disabled { opacity: 1; color: #9ca3af !important; background: #f9fafb !important; border-color: #e5e7eb !important; box-shadow: none !important; cursor: default; }
        #calendar .fc-button-group > .fc-button { border-radius: 0; }
        #calendar .fc-button-group > .fc-button:first-child { border-radius: 8px 0 0 8px; }
        #calendar .fc-button-group > .fc-button:last-child { border-radius: 0 8px 8px 0; margin-left: -1px; }

        .dark #calendar .fc-toolbar-title { color: #f1f5f9; }
        .dark #calendar .fc-button { background: #1f2937 !important; border-color: #4b5563 !important; color: #e5e7eb !important; }
        .dark #calendar .fc-button:hover:not(:disabled) { background: rgba(59, 130, 246, .16) !important; border-color: #60a5fa !important; color: #bfdbfe !important; }
        .dark #calendar .fc-button:disabled { background: transparent !important; border-color: #374151 !important; color: #6b7280 !important; }

        /* ---- Grid frame (clip, not hidden, so the sticky date header keeps working) ---- */
        #calendar .fc-view-harness { border: 1px solid #e5e7eb; border-radius: 12px; overflow: clip; }
        .dark #calendar .fc-view-harness { border-color: rgba(255, 255, 255, .08); }
        #calendar.fc-theme-standard td, #calendar.fc-theme-standard th,
        #calendar .fc-theme-standard td, #calendar .fc-theme-standard th { border-color: #eef2f6 !important; }
        .dark #calendar.fc-theme-standard td, .dark #calendar.fc-theme-standard th,
        .dark #calendar .fc-theme-standard td, .dark #calendar .fc-theme-standard th { border-color: rgba(255, 255, 255, .07) !important; }
        #calendar .fc-resource-timeline-divider { width: 1px; border-width: 0 1px 0 0; border-color: #e5e7eb !important; background: transparent; }
        .dark #calendar .fc-resource-timeline-divider { border-color: rgba(255, 255, 255, .1) !important; }

        /* ---- Date header ---- */
        #calendar .fc-timeline-header, #calendar .fc-datagrid-header { background: #f9fafb; }
        .dark #calendar .fc-timeline-header, .dark #calendar .fc-datagrid-header { background: #111c2f; }
        .dark #calendar .fc-datagrid-header * { background-color: transparent !important; }
        .dark #calendar .fc-datagrid-header .fc-datagrid-cell-frame { border: 0; }
        #calendar .fc-timeline-header .fc-timeline-slot-cushion { padding: 5px 2px; font-size: 11px; font-weight: 600; color: #6b7280; }
        #calendar .fc-timeline-header tr:first-child .fc-timeline-slot-cushion { font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
        #calendar .fc-timeline-header tr:last-child .fc-timeline-slot-cushion {
            display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; padding: 0; margin: 2px 0;
            border-radius: 9999px; font-size: 12px; font-weight: 700; color: #374151;
        }
        .dark #calendar .fc-timeline-header .fc-timeline-slot-cushion { color: #9ca3af; }
        .dark #calendar .fc-timeline-header tr:last-child .fc-timeline-slot-cushion { color: #e5e7eb; }

        /* Off days (weekends + national holidays): soft red on the day labels and the body lane */
        #calendar .fc-offday-slot { background: rgba(239, 68, 68, .055) !important; }
        #calendar .fc-timeline-header .fc-offday-slot .fc-timeline-slot-cushion { color: #dc2626; }
        .dark #calendar .fc-offday-slot { background: rgba(248, 113, 113, .09) !important; }
        .dark #calendar .fc-timeline-header .fc-offday-slot .fc-timeline-slot-cushion { color: #f87171; }

        /* Today */
        #calendar .fc-timeline-slot-lane.fc-day-today { background: rgba(59, 130, 246, .09) !important; }
        #calendar .fc-timeline-header tr:last-child .fc-day-today .fc-timeline-slot-cushion { background: #2563eb; color: #fff; }

        /* ---- Resource column ---- */
        #calendar .fc-datagrid-cell { height: 40px !important; padding: 0 !important; vertical-align: middle; }
        #calendar .fc-timeline-lane { height: 40px !important; border-bottom-width: 1px !important; border-bottom-color: #eef2f6 !important; }
        .dark #calendar .fc-timeline-lane { border-bottom-color: rgba(255, 255, 255, .06) !important; }
        .dark #calendar .fc-datagrid-body .fc-datagrid-cell-frame { background: transparent; border-right: 0; }
        #calendar .fc-datagrid-header .fc-datagrid-cell-cushion {
            padding: 10px 16px; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #6b7280;
        }
        .dark #calendar .fc-datagrid-header .fc-datagrid-cell-cushion { color: #9ca3af; }
        #calendar .fc-datagrid-body .fc-datagrid-cell-cushion { padding: 6px 12px; font-size: 12.5px; line-height: 1.35; color: #374151; white-space: normal; }
        .dark #calendar .fc-datagrid-body .fc-datagrid-cell-cushion { color: #cbd5e1; }
        #calendar .fc-datagrid-body .fc-datagrid-cell-cushion { display: flex; align-items: center; }
        #calendar .fc-datagrid-body .fc-datagrid-cell-main { min-width: 0; }
        #calendar .fc-datagrid-expander { margin-right: 6px; cursor: pointer; }
        #calendar .fc-datagrid-body tr:not(:has(.fc-resource-group)):hover .fc-datagrid-cell { background: #f8fafc; }
        .dark #calendar .fc-datagrid-body tr:not(:has(.fc-resource-group)):hover .fc-datagrid-cell { background: rgba(255, 255, 255, .03); }

        /* Company + Department render as one full-width group band (see resourceGroupLabelContent in calendar.js) */
        #calendar .fc-datagrid-cell.fc-resource-group, #calendar .fc-timeline-lane.fc-resource-group, #calendar .fc-cell-shaded { background: #f1f5f9 !important; }
        .dark #calendar .fc-datagrid-cell.fc-resource-group, .dark #calendar .fc-timeline-lane.fc-resource-group, .dark #calendar .fc-cell-shaded { background: #172338 !important; }
        #calendar .fc-datagrid-cell-cushion:has(.fc-group-company) { display: flex; align-items: center; }
        #calendar .fc-datagrid-cell-main:has(.fc-group-company) { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; min-width: 0; }
        #calendar .fc-group-company { font-size: 12px; font-weight: 700; letter-spacing: .01em; color: #0f172a; }
        .dark #calendar .fc-group-company { color: #f1f5f9; }
        #calendar .fc-group-dept {
            display: inline-flex; align-items: center; padding: 1px 8px; border: 1px solid #bfdbfe; border-radius: 9999px;
            background: #eff6ff; color: #1d4ed8; font-size: 10px; font-weight: 700; letter-spacing: .04em;
        }
        #calendar .fc-group-dept.fc-dept-loyalty { border-color: #e9d5ff; background: #faf5ff; color: #7e22ce; }
        .dark #calendar .fc-group-dept { border-color: rgba(96, 165, 250, .3); background: rgba(59, 130, 246, .15); color: #bfdbfe; }
        .dark #calendar .fc-group-dept.fc-dept-loyalty { border-color: rgba(192, 132, 252, .3); background: rgba(147, 51, 234, .18); color: #e9d5ff; }

        /* ---- Event bars: title + creator avatar, accent stripe in the status colour ---- */
        #calendar .fc-timeline-event { margin: 0 1px 3px; padding: 0 !important; border-radius: 7px !important; overflow: hidden; cursor: pointer; font-size: 12px; font-weight: 600; transition: filter .15s, box-shadow .15s; }
        #calendar .fc-timeline-event:hover { filter: brightness(.96); box-shadow: 0 4px 10px -4px rgba(15, 23, 42, .35); }
        #calendar .fc-timeline-events { padding-top: 5px; padding-bottom: 2px; }
        #calendar .fc-timeline-event .fc-event-main { padding: 4px 6px 4px 10px; box-shadow: inset 3px 0 0 currentColor; }
        #calendar .fc-event-main-frame { display: flex; align-items: center; gap: 6px; min-height: 20px; overflow: hidden; }
        #calendar .fc-event-main-frame .fc-event-title { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; font-weight: 600; }
        #calendar .fc-event-creator-avatar {
            flex-shrink: 0; margin-left: auto; display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px;
            border-radius: 9999px; background: rgba(255, 255, 255, .75); font-size: 9px; font-weight: 700; line-height: 1;
        }
        #calendar .fc-highlight { background: rgba(59, 130, 246, .18); }

        @media (max-width: 768px) {
            #calendar .fc-header-toolbar { flex-direction: row; flex-wrap: wrap; align-items: center; }
            #calendar .fc-view-harness { min-width: 760px; }
            #calendar .fc-timeline-lane { height: 40px !important; }
        }

        /* ---- Event hover tooltip (tippy.js) ---- */
        .tippy-box[data-theme~='light-border'] { border-radius: 12px; border-color: #e5e7eb; box-shadow: 0 12px 28px -8px rgba(15, 23, 42, .25); }
        .ecap-tip { min-width: 200px; padding: 4px 2px; text-align: left; }
        .ecap-tip-title { margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f1f5f9; font-size: 13px; font-weight: 700; color: #0f172a; }
        .ecap-tip-row { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 5px; font-size: 12px; line-height: 1.4; color: #475569; }
        .ecap-tip-row:last-child { margin-bottom: 0; }
        .ecap-tip-row i { width: 12px; flex-shrink: 0; margin-top: 2px; text-align: center; color: #94a3b8; }

        /* ---- View modal: status badges + PIC chips ---- */
        .ecap-badge { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 700; }
        .ecap-badge::before { content: ''; width: 6px; height: 6px; border-radius: 9999px; background: currentColor; }
        .ecap-chip {
            display: inline-flex; align-items: center; margin: 0 6px 6px 0; padding: 3px 10px; border: 1px solid #bfdbfe; border-radius: 9999px;
            background: #eff6ff; color: #1d4ed8; font-size: 12px; font-weight: 600;
        }
        .dark .ecap-chip { border-color: rgba(96, 165, 250, .3); background: rgba(59, 130, 246, .18); color: #bfdbfe; }
    </style>

    {{-- CREATE / EDIT MODAL --}}
    <div id="eventModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4">

        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel relative z-10 flex max-h-[92vh] w-full translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10 max-w-3xl">

            <form id="eventForm" class="flex min-h-0 flex-col">

                @csrf

                <input type="hidden" id="event_row_id" name="id">

                <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-calendar-plus"></i></span>
                        <div>
                            <h2 id="eventModalTitle" class="text-base font-bold text-gray-800 dark:text-white">Create Event</h2>
                            <p id="eventModalSubtitle" class="text-xs text-gray-500 dark:text-gray-400">Schedule a new calendar event.</p>
                        </div>
                    </div>
                    <button type="button" id="closeEventModal" aria-label="Close"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <div class="modal-scroll min-h-0 space-y-4 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-900/60">

                    <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-calendar-day"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Event Information</h3>
                                <p class="text-xs text-gray-400">What the event is and where it stands</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="event_name" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Event Name</label>
                                <input id="event_name" name="event_name" type="text" required placeholder="e.g. Ramadan Bazaar"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="event_company_name" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant / Event Company Name</label>
                                <input id="event_company_name" name="event_company_name" type="text" placeholder="e.g. PT Sinar Jaya"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div class="sf-field sm:col-span-2">
                                <label for="event_type" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Event Type</label>
                                <select id="event_type" name="event_type" required class="select2 w-full">
                                    <option value="">Select Event Type</option>
                                    @foreach ($eventTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sf-field sm:col-span-2">
                                <label for="event_status" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Event Status</label>
                                <select id="event_status" name="event_status" required class="select2 w-full">
                                    <option value="">Select Event Status</option>
                                    @foreach ($eventStatuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="event_total_contract" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Contract</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">Rp</span>
                                    <input type="text" inputmode="numeric" id="event_total_contract" name="event_total_contract" placeholder="e.g. 1.000.000"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                </div>
                            </div>

                            <div id="statusActiveField" class="hidden items-end sm:col-span-6">
                                <label class="flex w-full cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 transition has-[:checked]:border-[#bbf7d0] has-[:checked]:bg-[#f0fdf4] dark:border-gray-600 dark:bg-gray-700/40 dark:has-[:checked]:border-green-500/30 dark:has-[:checked]:bg-green-500/10">
                                    <input type="checkbox" id="status_active" checked
                                        class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-400 dark:border-gray-500">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Active</span>
                                    <span class="text-xs text-gray-400">Untick to remove this event from the calendar</span>
                                </label>
                                <input type="hidden" id="status" name="status" value="A">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Schedule &amp; Location</h3>
                                <p class="text-xs text-gray-400">Where and when it takes place</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                            <div class="ev-ts sm:col-span-6">
                                <label for="location_row_id" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</label>
                                <select id="location_row_id" name="location_row_id" required class="w-full">
                                    <option value=""></option>
                                    @foreach ($locations as $l)
                                        <option value="{{ $l->id }}">{{ $l->event_location_name }}{{ optional($l->company)->cpny_name ? ' - '.$l->company->cpny_name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="event_start_date" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Start Date</label>
                                <input id="event_start_date" name="event_start_date" type="date" required
                                    class="ev-date h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="event_end_date" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">End Date</label>
                                <input id="event_end_date" name="event_end_date" type="date" required
                                    class="ev-date h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div class="hidden">
                                <label for="product_check_exp" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product Check Expiry</label>
                                <input type="date" id="product_check_exp" name="product_check_exp" class="ev-date h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-user-group"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Person in Charge</h3>
                                <p class="text-xs text-gray-400">Who is responsible, inside and outside</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                            <div class="ev-ts sm:col-span-6">
                                <label for="pic_event" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PIC Event</label>
                                <select id="pic_event" name="pic_event[]" multiple class="w-full">
                                    @foreach ($users as $u)
                                        <option value="{{ $u->name }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="pic_event_external" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PIC External</label>
                                <input id="pic_event_external" name="pic_event_external" type="text" placeholder="e.g. John Doe"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="pic_event_external_hp" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PIC External Phone Number</label>
                                <input id="pic_event_external_hp" name="pic_event_external_hp" type="text" placeholder="e.g. 0812xxxxxxx"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-align-left"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Description</h3>
                                <p class="text-xs text-gray-400">Anything else the team should know</p>
                            </div>
                        </div>
                        <textarea id="event_description" name="event_description" rows="2" aria-label="Description" placeholder="Describe the event..."
                            class="min-h-[4.5rem] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea>
                    </section>

                </div>

                <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" id="deleteEventBtn" class="hidden inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        Delete Event
                    </button>

                    <div class="ml-auto flex items-center gap-3">
                        <button type="button" id="cancelEventBtn" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>

                        <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Save Event
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    {{-- VIEW MODAL --}}
    <div id="eventViewModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4">

        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel relative z-10 flex max-h-[92vh] w-full translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10 max-w-2xl">

            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex min-w-0 items-center gap-3">
                    {{-- background is recoloured by the script to match the event status --}}
                    <span id="eventViewStatusDot" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm"><i class="fa-solid fa-calendar-check"></i></span>
                    <div class="min-w-0">
                        <h2 id="eventViewTitle" class="break-words text-base font-bold leading-tight text-gray-800 dark:text-white">Event Details</h2>
                        <p id="eventViewSubtitle" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <button type="button" id="closeEventViewModal" aria-label="Close"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
            </div>

            <div class="modal-scroll min-h-0 space-y-4 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-900/60">

                {{-- Tenant + status badges --}}
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p id="view_event_name" class="hidden">-</p>
                    <dl>
                            <div class="">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-building w-3 text-center text-[10px]"></i> Tenant / Event Company Name
                                </dt>
                                <dd id="view_event_company_name" class="mt-1 break-words text-base font-bold text-gray-900 dark:text-white">-</dd>
                            </div>
                    </dl>
                    <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <span id="view_event_status">-</span>
                        <span id="view_status">-</span>
                        <span id="view_event_type" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-200">-</span>
                    </div>
                </div>

                <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Schedule &amp; Location</h3>
                                <p class="text-xs text-gray-400">Where and when it takes place</p>
                            </div>
                        </div>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-calendar-day w-3 text-center text-[10px]"></i> Start Date
                                </dt>
                                <dd id="view_event_start_date" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                            <div class="">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-calendar-check w-3 text-center text-[10px]"></i> End Date
                                </dt>
                                <dd id="view_event_end_date" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                            <div class="">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-location-dot w-3 text-center text-[10px]"></i> Location
                                </dt>
                                <dd id="view_event_location" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                            <div class="">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-ruler-combined w-3 text-center text-[10px]"></i> Location Total Area (m²)
                                </dt>
                                <dd id="view_event_total_area" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                        </dl>
                    </section>

                {{-- Total Contract: shown to GM only (toggled by the script) --}}
                <dl id="view_event_total_contract_card" class="hidden rounded-xl border border-[#bbf7d0] bg-[#f0fdf4] p-4 dark:border-green-500/30 dark:bg-green-500/10">
                            <div class="">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-file-contract w-3 text-center text-[10px]"></i> Total Contract
                                </dt>
                                <dd id="view_event_total_contract" class="mt-1 break-words text-lg font-bold text-green-700 dark:text-green-300">-</dd>
                            </div>
                </dl>

                <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-user-group"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Person in Charge</h3>
                                <p class="text-xs text-gray-400">Who is responsible, inside and outside</p>
                            </div>
                        </div>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-user w-3 text-center text-[10px]"></i> PIC Event
                                </dt>
                                <dd id="view_pic_event" class="mt-1 break-words text-sm text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                            <div id="view_pic_event_external_card" class="hidden">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-user-tie w-3 text-center text-[10px]"></i> PIC External
                                </dt>
                                <dd id="view_pic_event_external" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                            <div id="view_pic_event_external_hp_card" class="hidden">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-phone w-3 text-center text-[10px]"></i> PIC External Phone Number
                                </dt>
                                <dd id="view_pic_event_external_hp" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                    <i class="fa-solid fa-user-pen w-3 text-center text-[10px]"></i> Created By
                                </dt>
                                <dd id="view_created_by" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                            </div>
                        </dl>
                    </section>

                <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-align-left"></i></span>
                            <div>
                                <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Description</h3>
                                <p class="text-xs text-gray-400">Notes on this event</p>
                            </div>
                        </div>
                        <p id="view_event_description" class="whitespace-pre-line break-words text-sm text-gray-700 dark:text-gray-200">-</p>
                    </section>

            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800 !justify-end">
                <button type="button" id="closeEventViewBtn" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Close</button>

                <button type="button" id="editEventFromViewBtn" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="fa-solid fa-pen text-xs"></i>
                    Edit
                </button>
            </div>

        </div>

    </div>

    <script>
        window.EventCalendarResources = {!! json_encode($locations->map(function ($l) use ($departmentNames) {
            $cpnyName = optional($l->company)->cpny_name;
            $deptLabel = collect($l->departmentIds())
                ->map(fn ($id) => $departmentNames->get($id, $id))
                ->implode(', ');

            return [
                'id' => (string) $l->id,
                'title' => $l->event_total_area ? "{$l->event_location_name} - {$l->event_total_area} m²" : $l->event_location_name,
                'cpny_name' => $cpnyName,
                'department_label' => $deptLabel,
                // Single composite key so Company + Department render as one
                // full-width group header with locations nested directly
                // beneath it, instead of separate staircased columns.
                'group_key' => "{$cpnyName}|||{$deptLabel}",
                // PROMOTION/CASUALLEASING group before LOYALTY within each company
                // (alphabetical group_key order would put LOYALTY first otherwise).
                'dept_rank' => strtoupper(trim($deptLabel)) === 'LOYALTY' ? 1 : 0,
                // Sort by insertion order (id) rather than name, so locations
                // land in whatever sequence they were set up in (e.g. floor
                // order: LG, GF, UG, 1st FL...) instead of alphabetically.
                // "Others" is still forced last as a catch-all safety net.
                'sort_key' => strtolower(trim($l->event_location_name)) === 'others'
                    ? 999999999
                    : $l->id,
            ];
        })->values()) !!};

        window.EventCalendarCurrentUser = {
            username: @json(auth()->user()->username),
            isAdmin: @json($isAdmin),
            isGM: @json(auth()->user() && auth()->user()->hasRole('GMACCESS')),
            canManageAll: @json($isAdmin || (auth()->user() && auth()->user()->hasRole('GMACCESS'))),
            canCreate: @json($canCreate),
        };

        window.EventCalendarRoutes = {
            json: '{{ route('event-calendar.json') }}',
            holidays: '{{ route('event-calendar.holidays') }}',
            store: '{{ route('event-calendar.store') }}',
            update: (id) => `{{ url('event-calendar/update') }}/${id}`,
            status: (id) => `{{ url('event-calendar/status') }}/${id}`,
            destroy: (id) => `{{ url('event-calendar/destroy') }}/${id}`,
        };
    </script>

</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script src="{{ asset('assets/js/event_calendar/calendar.js') }}?v={{ filemtime(public_path('assets/js/event_calendar/calendar.js')) }}"></script>
