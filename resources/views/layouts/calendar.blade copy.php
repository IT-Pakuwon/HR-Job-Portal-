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
   FULLCALENDAR — DARK MODE FIX (COMPLETE)
   ====================================================== */

    /* Base */
    .dark .fc {
        background-color: #1f2933;
        /* slate-950 */
        color: #e5e7eb;
        /* gray-200 */
    }

    /* ---------------- TOOLBAR ---------------- */

    .dark .fc-header-toolbar {
        background-color: #1f2933;
    }

    .dark .fc .fc-toolbar-title {
        color: #f9fafb;
    }

    .dark .fc .fc-button {
        background: transparent !important;
        border-color: #374151 !important;
        color: #d1d5db !important;
    }

    .dark .fc .fc-button:hover {
        background-color: #1f2937 !important;
    }

    .dark .fc .fc-button.fc-button-active {
        background-color: #f9fafb !important;
        color: #1f2933 !important;
        border-color: #f9fafb !important;
    }

    /* ---------------- GRID / HEADERS ---------------- */

    .dark .fc-theme-standard td,
    .dark .fc-theme-standard th {
        border-color: #1f2933;
    }

    .dark .fc-col-header-cell {
        background-color: #1f2933;
        color: #9ca3af;
        font-weight: 500;
    }

    .dark .fc-daygrid-day-number {
        color: #6b7280;
    }

    /* Today */
    .dark .fc-day-today {
        background-color: rgba(99, 102, 241, 0.08) !important;
    }

    /* Weekend */
    .dark .fc-day-sun,
    .dark .fc-day-sat {
        background-color: #1f2933;
    }

    /* ---------------- TIME GRID ---------------- */

    .dark .fc-timegrid-slot {
        border-color: #1f2933;
    }

    .dark .fc-timegrid-slot-label {
        color: #9ca3af;
    }

    .dark .fc-timegrid-axis {
        border-color: #1f2933;
    }

    /* ---------------- EVENTS ---------------- */

    .dark .fc-event {
        background-color: #1f2933 !important;
        color: #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
    }

    .dark .fc-event-title {
        font-weight: 600;
    }

    .dark .fc-event-time {
        opacity: 0.7;
    }

    /* Google events */
    .dark .fc-event[style*="#6366F1"] {
        background-color: rgba(99, 102, 241, 0.15) !important;
        color: #e0e7ff !important;
    }

    /* Local events */
    .dark .fc-event[style*="#10B981"] {
        background-color: rgba(16, 185, 129, 0.15) !important;
        color: #d1fae5 !important;
    }

    /* ---------------- LIST VIEW ---------------- */

    .dark .fc-list {
        background-color: #1f2933;
        border: none;
    }

    .dark .fc-list-table {
        background-color: transparent;
    }

    .dark .fc-list-day-cushion {
        background-color: #1f2933;
        color: #e5e7eb;
        font-weight: 600;
    }

    .dark .fc-timegrid-col.fc-day-today {
        background-color: rgba(99, 102, 241, 0.08);
    }

    .dark .fc-list-event td {
        background-color: transparent;
        border-color: #1f2933;
        color: #e5e7eb;
        transition: background-color 0.15s ease;
    }

    .dark .fc-list-event:hover td {
        background-color: rgba(99, 102, 241, 0.08);
    }

    .dark .fc-list-event-title {
        font-weight: 500;
    }

    /* Remove list borders */
    .fc-theme-standard .fc-list {
        border: none;
    }

    /* ---------------- SCROLL GRID ---------------- */

    .dark .fc-scrollgrid {
        background-color: #1f2933;
    }

    /* ---------------- MISC ---------------- */

    .dark .fc-popover {
        background-color: #1f2933;
        border-color: #1f2937;
    }

    .dark .fc-popover-header {
        background-color: #1f2933;
        color: #f9fafb;
    }

    .dark .fc-popover-body {
        background-color: #1f2933;
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

    .fc-list-event:hover td {
        background: rgba(99, 102, 241, 0.05);
    }

    .fc-list-day-cushion {
        background: transparent;
        font-weight: 600;
    }

    .fc-list-event-title {
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
