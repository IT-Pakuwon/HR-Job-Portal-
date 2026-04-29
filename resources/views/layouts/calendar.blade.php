<style>
    /* ======================================================
   GLOBAL CALENDAR — NOTION STYLE (CLEAN / AIRY)
   ====================================================== */
:root {
    --row-height: 56px;
}

.fc {
    font-family: ui-sans-serif, system-ui, -apple-system;
    font-size: 13px;
    color: #374151;
}

/* ================= HEADER ================= */

.fc-header-toolbar {
    margin-bottom: 14px;
    padding: 0 4px;
}

.fc-toolbar-title {
    color: #111827; /* light mode */
    font-size: 16px;
    font-weight: 600;
}

/* buttons */
.fc-button-group {
    background: #f3f4f6;
    padding: 3px;
    border-radius: 10px;
}

/* default button */
.fc-button {
    background: transparent !important;
    border: none !important;
    color: #6b7280 !important;
    box-shadow: none !important;
}

/* hover */
.fc-button:hover {
    background: rgba(0,0,0,0.05) !important;
}
/* ================= GRID ================= */

.fc-scrollgrid {
    border: none !important;
}

.fc-theme-standard td,
.fc-theme-standard th {
    border-color: #f1f5f9 !important;
}

/* vertical lines softer */
.fc-timegrid-col,
.fc-resource-timeline-divider {
    border-color: #f8fafc !important;
}

/* ================= TIME HEADER ================= */

.fc-timeline-slot-cushion {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
}

/* ================= RESOURCE COLUMN (ROOMS) ================= */

.fc-datagrid-cell {
    height: var(--row-height) !important;
    padding: 0 12px !important;
}

/* RIGHT (TIMELINE ROW) */
.fc-timeline-lane {
    height: var(--row-height) !important;
}
.fc-datagrid-cell-cushion {
    font-size: 12px;
    color: #374151;
}

/* room column background */

/* ================= ROW SPACING (IMPORTANT) ================= */

/* tambah tinggi row biar lega */

/* bikin "gap illusion" antar row */
/* .fc-timeline-lane-frame {
    padding: 6px 0;
} */

/* garis jadi lebih soft */
.fc-timeline-lane {
    border-bottom: 2px solid #f3f4f6 !important;
}

/* ================= EVENTS ================= */

.fc-event {
    border-radius: 8px !important;
    border: none !important;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 500;

    /* kasih napas dikit */
    margin-top: 4px;
}

/* hover subtle */
.fc-event:hover {
    filter: brightness(0.96);
}

/* ================= CUSTOM EVENT UI ================= */

.fc-custom-event {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.fc-event-title {
    font-size: 11px;
    font-weight: 600;
    line-height: 1.2;
    color: inherit;
}

.fc-event-meta {
    font-size: 10px;
    opacity: 0.75;
    font-weight: 500;
}

/* optional: spacing */
.fc-event {
    padding: 6px 8px !important;
}

/* ================= NOW LINE ================= */

.fc-timeline-now-indicator-line {
    border-color: #ef4444 !important;
    opacity: 0.6;
}

.fc-timeline-now-indicator-arrow {
    border-top-color: #ef4444 !important;
}

/* ================= SCROLL BEHAVIOR ================= */

/* biar halaman yang scroll, bukan grid */
.fc-scroller {
    overflow: visible !important;
}

#calendar {
    -webkit-overflow-scrolling: touch;
}

/* ================= RESPONSIVE ================= */

@media (max-width: 768px) {

    .fc-timeline-lane {
        height: 48px !important;
    }

    .fc-toolbar-title {
        font-size: 14px;
    }

    .fc-button {
        padding: 3px 8px;
    }
}
@media (max-width: 768px) {

    .fc-header-toolbar {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }

    .fc-toolbar-title {
        font-size: 14px;
    }

    .fc-button {
        font-size: 11px;
        padding: 3px 6px;
    }

    /* biar scroll horizontal kalau perlu */
    #calendar {
        overflow-x: auto;
    }

}
/* ================= CENTER EVENT VERTICALLY ================= */

/* bikin lane jadi flex */
/* pastikan event nggak full height */
.fc-event {
    margin-top: 0 !important;
}

/* spacing antar tombol kanan */
.fc-toolbar-chunk:last-child .fc-button-group {
    display: flex;
    gap: 6px; /* ubah jadi 8px kalau mau lebih lega */
}

/* hilangkan border merge default */
.dark .fc-button-group {
    background: rgba(255,255,255,0.08);
}

.dark .fc-button {
    color: #94a3b8 !important;
}

.dark .fc-button:hover {
    background: rgba(255,255,255,0.08) !important;
}


/* ======================================================
   DARK MODE — FIXED (WORKING VERSION)
   ====================================================== */

/* base */
.dark .fc {
    color: #e5e7eb;
}

/* background */
.dark .fc-theme-standard {
    background: transparent;
}

/* grid */
.dark .fc-scrollgrid,
.dark .fc-theme-standard td,
.dark .fc-theme-standard th {
    background: transparent !important;
    border-color: rgba(255,255,255,0.06) !important; /* jangan full transparent */
}

/* vertical line */
.dark .fc-timegrid-col,
.dark .fc-resource-timeline-divider {
    border-color: rgba(255,255,255,0.06) !important;
}

/* header */
.dark .fc-toolbar-title {
    color: #f1f5f9;
}

/* ================= BUTTON ================= */

.dark .fc-button {
    background: transparent !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    color: #cbd5f5 !important; /* FIX: tadi kamu pakai opacity hex invalid */
}

.dark .fc-button:hover {
    background: rgba(255,255,255,0.08) !important;
}

.dark .fc-button-active {
    background: #6366f1 !important;
    border-color: #6366f1 !important;
    color: #fff !important;
}

/* ================= TIME ================= */

.dark .fc-timeline-slot-cushion {
    color: #64748b;
}

/* ================= ROOM ================= */

.dark .fc-datagrid {
    background: transparent;
}

.dark .fc-datagrid-cell-frame {
    background: rgba(255,255,255,0.02);
    border-right: 1px solid rgba(255,255,255,0.05);
}

.dark .fc-datagrid-cell-cushion {
    color: #cbd5f5;
}

/* ================= ROW ================= */

.dark .fc-timeline-lane {
    background: transparent;
    border-bottom: 1px solid rgba(255,255,255,0.05) !important;
}

.dark .fc-timeline-lane:hover {
    background: rgba(255,255,255,0.03);
}

/* ================= EVENT ================= */

.dark .fc-event {
    box-shadow: none !important;
}

/* ================= NOW LINE ================= */

.dark .fc-timeline-now-indicator-line {
    border-color: #f43f5e !important;
}

.dark .fc-timeline-now-indicator-arrow {
    border-top-color: #f43f5e !important;
}
.dark .fc-event {
    filter: brightness(1.1) saturate(0.9);
}

.fc .fc-button.fc-button-active {
    background: #ffffff !important;
    color: #111827 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
}

/* dark mode */
.dark .fc .fc-button.fc-button-active {
    background: #6366f1 !important;
    color: #ffffff !important;
}

/* WEEK VIEW CLEAN */
.fc-timeGridWeek-view .fc-event {
    border-radius: 8px !important;
    padding: 2px 4px !important;
    font-size: 11px !important;
    box-shadow: none !important;
}

/* REMOVE INNER PADDING */
.fc-timeGridWeek-view .fc-event-main {
    padding: 0 !important;
}

/* LESS HEIGHT */
.fc-timeGridWeek-view .fc-timegrid-event {
    min-height: 18px !important;
}
.fc-event {
    opacity: 1 !important;
}

/* ================= MONTH VIEW SPACING FIX ================= */

/* make each day taller */
.fc-daygrid-day-frame {
    min-height: 120px; /* 🔥 main control (try 130–150 if you want more) */
    padding: 10px 10px 8px;
}

/* spacing between rows */
.fc-daygrid-body tr {
    height: 120px;
}

/* date number spacing */
.fc-daygrid-day-number {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 6px;
}

/* event spacing */
.fc-daygrid-event {
    margin-top: 6px !important;
}

/* make cells feel like cards */
.fc-daygrid-day {
    border-radius: 12px;
    transition: all 0.15s ease;
}

/* subtle hover (Notion feel) */
.fc-daygrid-day:hover {
    background: #fafafa;
}

#approvalFlow > div {
    animation: fadeSlide 0.3s ease;
}

@keyframes fadeSlide {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


</style>


