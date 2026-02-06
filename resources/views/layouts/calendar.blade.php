<style>
    /* ======================================================
   FULLCALENDAR — MINIMAL / MODERN THEME
   ====================================================== */

    .fc {
        font-family: ui-sans-serif, system-ui, -apple-system;
        font-size: 14px;
        color: #374151;
        /* gray-700 */
    }

    /* ---------------- HEADER / TOOLBAR ---------------- */

    .fc-header-toolbar {
        margin-top: 5px;
    }

    .fc .fc-toolbar {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: nowrap !important;
        /* 🔥 THIS IS THE KEY */
    }

    .fc .fc-toolbar-chunk {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .fc .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        /* gray-900 */
        white-space: nowrap;
        margin: 0 !important;
        line-height: 1.2;
    }

    /* Buttons */
    .fc .fc-button {
        background: transparent !important;
        border: 1px solid #e5e7eb !important;
        color: #374151 !important;
        border-radius: 0.75rem !important;
        padding: 0.35rem 0.75rem !important;
        font-size: 0.75rem;
        transition: all 0.15s ease;
    }

    .fc .fc-button:hover {
        background: #f3f4f6 !important;
    }

    .fc .fc-button.fc-button-active {
        background: #111827 !important;
        color: #ffffff !important;
        border-color: #111827 !important;
    }

    /* Space between grouped buttons */
    .fc .fc-button-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }

    /* ---------------- GRID / STRUCTURE ---------------- */

    .fc-theme-standard .fc-scrollgrid {
        border: none;
    }

    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: #f1f5f9;
        /* ultra light */
    }

    .fc-col-header-cell {
        font-weight: 500;
        color: #374151;
        padding: 0.5rem 0;
    }

    /* Day number */
    .fc-daygrid-day-number {
        font-size: 0.75rem;
        color: #6b7280;
        /* gray-500 */
    }

    /* Today highlight */
    .fc-day-today {
        background: #f8fafc !important;
    }

    /* Softer weekends */
    .fc-day-sun,
    .fc-day-sat {
        background: #fafafa;
    }

    /* ---------------- TIME GRID ---------------- */

    .fc-timegrid-slot-label {
        font-size: 0.7rem;
        color: #9ca3af;
    }

    .fc-timegrid-all-day {
        display: none;
        /* cleaner */
    }

    /* ---------------- EVENTS ---------------- */

    .fc-event {
        border-radius: 0.5rem !important;
        padding: 6px 8px !important;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1.2;
        /* border-left: 4px solid currentColor; */
    }

    .fc-event-time {
        font-size: 0.7rem;
        opacity: 0.75;
    }


    .fc-event-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;

    }



    /* Drag cursor */
    .cursor-grabbing {
        cursor: grabbing !important;
    }

    /* ======================================================
   DARK MODE
   ====================================================== */

    .dark .fc {
        color: #d1d5db;
        /* gray-300 */
    }

    .dark .fc .fc-toolbar-title {
        color: #f9fafb;
    }

    .dark .fc .fc-button {
        border-color: #374151 !important;
        color: #d1d5db !important;
    }

    .dark .fc .fc-button:hover {
        background: #1f2933 !important;
    }

    .dark .fc .fc-button.fc-button-active {
        background: #f9fafb !important;
        color: #111827 !important;
        border-color: #f9fafb !important;
    }

    .dark .fc-theme-standard td,
    .dark .fc-theme-standard th {
        border-color: #1f2937;
    }

    .dark .fc-day-today {
        background: #1f2937 !important;
    }

    .dark .fc-day-sun,
    .dark .fc-day-sat {
        background: #0f172a;
    }

    .dark .fc-timegrid-slot-label {
        color: #6b7280;
    }

    .dark .fc-event {
        box-shadow: none;
    }

    [x-cloak] {
        display: none !important;
    }

    /* Month view event spacing */
    .fc-daygrid-event {
        padding: 2px 4px;
    }

    .fc-daygrid-event .fc-event-title {
        font-weight: 500;
    }
</style>


<style>
    .fc-timegrid-slot {
        height: 2.25rem;
    }

    .fc-event {
        border-radius: 0.375rem;
        padding: 4px 6px;
        font-size: 0.7rem;
        font-weight: 500;
    }

    [x-cloak] {
        display: none !important;
    }
</style>
