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
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Manage casual leasing exhibition, promotion, and mall sales promotion events
                    </p>
                </div>

            </div>

            <div class="flex items-center gap-2">

                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-1">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#E0A800]"></span> Booked
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#2E9E5B]"></span> Confirmed
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#2F6FED]"></span> Paid
                    </span>
                </div>

                @if (auth()->check() && in_array('admin', auth()->user()->roles(), true))
                    <a href="{{ route('event-calendar.setup.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                        <i class="fa-solid fa-gear text-xs"></i>
                        Setup
                    </a>
                @endif

                @if ($canCreate)
                    <button type="button" id="openCreateEventModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                        <i class="fa-solid fa-plus text-xs"></i>
                        New Event
                    </button>
                @endif

            </div>

        </div>

    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
        <div class="p-4">
            @if ($locations->isEmpty())
                <div class="flex flex-col items-center justify-center gap-2 py-16 text-center">
                    <span class="text-3xl">📍</span>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No event locations available to you yet</p>
                    <p class="text-xs text-slate-400">Ask an admin to set up a location for your company and department.</p>
                </div>
            @else
                <div id="calendar"></div>
            @endif
        </div>
    </div>

    <style>
        /* Event bar content: ID badge + title + creator avatar */
        #calendar .fc-event {
            border-radius: 6px !important;
            padding: 6px 10px !important;
            min-height: 30px;
            font-weight: 600;
            font-size: 12.5px;
        }

        #calendar .fc-event-main-frame {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
        }

        #calendar .fc-event-id-badge {
            flex-shrink: 0;
            font-size: 11px;
            font-weight: 700;
            opacity: 0.8;
        }

        #calendar .fc-event-main-frame .fc-event-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #calendar .fc-event-creator-avatar {
            flex-shrink: 0;
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.14);
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        /* Weekend (Sat/Sun) date columns: header label + body lane, both tinted red */
        #calendar .fc-weekend-slot {
            background: rgba(220, 38, 38, 0.08) !important;
        }

        #calendar .fc-timeline-header .fc-weekend-slot .fc-timeline-slot-cushion {
            color: #dc2626;
            font-weight: 700;
        }

        .dark #calendar .fc-weekend-slot {
            background: rgba(248, 113, 113, 0.12) !important;
        }

        .dark #calendar .fc-timeline-header .fc-weekend-slot .fc-timeline-slot-cushion {
            color: #f87171;
        }

        /* ── Resource grid (Company / Department / Location) — elevated look ── */
        #calendar .fc-datagrid-header .fc-datagrid-cell-cushion {
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .dark #calendar .fc-datagrid-header .fc-datagrid-cell-cushion {
            color: #94a3b8;
        }

        #calendar .fc-datagrid-cell-cushion {
            padding: 9px 12px;
        }

        /* Company + Department now render together as one full-width group
           header (see resourceGroupLabelContent in calendar.js), so lay the
           two spans out on a single line with a divider between them. */
        #calendar .fc-datagrid-cell-cushion:has(.fc-group-company) {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #calendar .fc-group-company {
            font-weight: 700;
            font-size: 12.5px;
            letter-spacing: 0.02em;
            color: #0f172a;
        }

        .dark #calendar .fc-group-company {
            color: #f1f5f9;
        }

        #calendar .fc-group-dept {
            padding-left: 10px;
            border-left: 1px solid #e2e8f0;
        }

        .dark #calendar .fc-group-dept {
            border-left-color: rgba(255, 255, 255, 0.1);
        }

        /* Department group header: small color dot + muted text, no filled tag */
        #calendar .fc-dept-loyalty,
        #calendar .fc-dept-promo {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: #64748b;
        }

        #calendar .fc-dept-loyalty::before,
        #calendar .fc-dept-promo::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        #calendar .fc-dept-loyalty::before {
            background: #a855f7;
        }

        #calendar .fc-dept-promo::before {
            background: #3b82f6;
        }

        .dark #calendar .fc-dept-loyalty,
        .dark #calendar .fc-dept-promo {
            color: #94a3b8;
        }

        /* FullCalendar's own default shading for group-header lanes reads as
           dull/gray next to our colored tags above — keep it plain instead. */
        #calendar .fc-cell-shaded {
            background: #ffffff !important;
        }

        .dark #calendar .fc-cell-shaded {
            background: transparent !important;
        }

        /* Event hover tooltip (tippy.js) */
        .ecap-tip {
            min-width: 180px;
            padding: 2px;
            text-align: left;
        }

        .ecap-tip-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .ecap-tip-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #475569;
            margin-bottom: 4px;
        }

        .ecap-tip-row i {
            width: 12px;
            flex-shrink: 0;
            opacity: 0.6;
        }

        .ecap-tip-status {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 9px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        /* View modal: status badges + PIC chips */
        .ecap-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }

        .ecap-chip {
            display: inline-flex;
            align-items: center;
            margin: 0 6px 6px 0;
            padding: 3px 10px;
            border-radius: 9999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 600;
        }

        .dark .ecap-chip {
            background: rgba(99, 102, 241, 0.18);
            color: #c7d2fe;
        }
    </style>

    {{-- CREATE / EDIT MODAL --}}
    <div id="eventModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

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
                                Location *
                            </label>
                            <select id="location_row_id" name="location_row_id" required
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                <option value=""></option>
                                @foreach ($locations as $l)
                                    <option value="{{ $l->id }}">{{ $l->event_location_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                PIC Event
                            </label>
                            <select id="pic_event" name="pic_event[]" multiple
                                class="w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                                @foreach ($users as $u)
                                    <option value="{{ $u->name }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                PIC External
                            </label>
                            <input type="text" id="pic_event_external" name="pic_event_external"
                                placeholder="e.g. John Doe"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                PIC External Phone Number
                            </label>
                            <input type="text" id="pic_event_external_hp" name="pic_event_external_hp"
                                placeholder="e.g. 0812xxxxxxx"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
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

                        <div class="hidden">
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Product Check Expiry
                            </label>
                            <input type="date" id="product_check_exp" name="product_check_exp"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Total Contract
                            </label>
                            <input type="text" inputmode="numeric" id="event_total_contract" name="event_total_contract"
                                placeholder="e.g. 1.000.000"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        </div>

                        <div id="statusActiveField" class="hidden items-end">
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

    {{-- VIEW MODAL --}}
    <div id="eventViewModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-2xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                <div class="flex items-center gap-3">
                    <span id="eventViewStatusDot" class="h-2.5 w-2.5 shrink-0 rounded-full bg-slate-300"></span>
                    <div>
                        <h2 id="eventViewTitle" class="text-sm font-bold text-slate-900 dark:text-white">
                            Event Details
                        </h2>
                        <p id="eventViewSubtitle" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        </p>
                    </div>
                </div>

                <button type="button" id="closeEventViewModal"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

            </div>

            <div class="space-y-5 bg-slate-50 p-5 dark:bg-[#0b1220]">

                {{-- Hero: name + tenant --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
                    <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                        <i class="fa-solid fa-tag text-[10px]"></i> Event Name
                    </p>
                    <p id="view_event_name" class="mt-1 text-base font-bold text-slate-900 dark:text-white">-</p>

                    <div class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:border-white/10">
                        <i class="fa-solid fa-building text-[10px]"></i> Tenant / Event Company Name
                    </div>
                    <p id="view_event_company_name" class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">-</p>
                </div>

                {{-- Status badges --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span id="view_event_status">-</span>
                    <span id="view_status">-</span>
                    <span id="view_event_type" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-200">-</span>
                </div>

                {{-- Detail cards --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-location-dot text-[10px]"></i> Location
                        </p>
                        <p id="view_event_location" class="mt-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-ruler-combined text-[10px]"></i> Location Total Area (m²)
                        </p>
                        <p id="view_event_total_area" class="mt-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-calendar-day text-[10px]"></i> Start Date
                        </p>
                        <p id="view_event_start_date" class="mt-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-calendar-check text-[10px]"></i> End Date
                        </p>
                        <p id="view_event_end_date" class="mt-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div id="view_event_total_contract_card" class="hidden rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-file-contract text-[10px]"></i> Total Contract
                        </p>
                        <p id="view_event_total_contract" class="mt-1.5 text-sm font-semibold text-emerald-700 dark:text-emerald-400">-</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-user text-[10px]"></i> PIC Event
                        </p>
                        <p id="view_pic_event" class="mt-1.5 text-sm text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div id="view_pic_event_external_card" class="hidden rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-user-tie text-[10px]"></i> PIC External
                        </p>
                        <p id="view_pic_event_external" class="mt-1.5 text-sm text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div id="view_pic_event_external_hp_card" class="hidden rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-phone text-[10px]"></i> PIC External Phone Number
                        </p>
                        <p id="view_pic_event_external_hp" class="mt-1.5 text-sm text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03] sm:col-span-2">
                        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="fa-solid fa-user-pen text-[10px]"></i> Created By
                        </p>
                        <p id="view_created_by" class="mt-1.5 text-sm text-slate-800 dark:text-slate-100">-</p>
                    </div>

                </div>

                {{-- Description --}}
                <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                    <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                        <i class="fa-solid fa-align-left text-[10px]"></i> Description
                    </p>
                    <p id="view_event_description" class="mt-1.5 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">-</p>
                </div>

            </div>

            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                <div class="flex items-center justify-end gap-3">
                    <button type="button" id="closeEventViewBtn"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                        Close
                    </button>

                    <button type="button" id="editEventFromViewBtn"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                        <i class="fa-solid fa-pen text-xs"></i>
                        Edit
                    </button>
                </div>

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
