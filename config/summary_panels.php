<?php

/*
 | Summary panels (components/panel/om-summary, gm-summary).
 |
 | 'catalog'  every card a Summary panel CAN show — the cards of the All Reports page (GmReportController),
 |            shared by OM and GM. Data comes from the same endpoints, so the numbers match All Reports and are
 |            limited to the user's own companies automatically.
 | 'panels'   per panel: title, and 'default' = the card ids it shows (in order) until an admin saves a layout
 |            (Dashboard Setup > OM Summary / GM Summary). Catalog cards not listed are offered as "available".
 |
 | Card fields:
 |   id      unique
 |   group   section of All Reports it comes from (label only)
 |   type    kpi | stat | split | table | donut | mix | column | list | insight   (drawn by public/assets/js/Dashboard-Panel/summary-panel.js)
 |   source  key resolved by App\Services\Dashboard\SummaryPanels\SummaryData ('budget.total' -> budgetTotal())
 |   span    width out of 12 on large screens (phones are always full width)
 |   color   violet|blue|green|orange|red|pink|cyan
 |   height  chart height in px (donut / column)
 | To add a card: one entry here + one method in SummaryData.
 */
return [

    'catalog' => [
        // ── Budget (All Reports > Budget Overview) ─────────────────────────────
        ['id' => 'budget_kpi',      'group' => 'Budget', 'type' => 'kpi',    'source' => 'budget.kpi',           'span' => 12, 'title' => 'Budget at a Glance',       'subtitle' => 'Budget',            'color' => 'blue'],
        ['id' => 'budget_total',    'group' => 'Budget', 'type' => 'split',  'source' => 'budget.total',         'span' => 4, 'title' => 'Total Budget',              'subtitle' => 'Budget',            'color' => 'green'],
        ['id' => 'budget_mix',      'group' => 'Budget', 'type' => 'mix',    'source' => 'budget.mix',           'span' => 4, 'title' => 'Used · Reserved · Remaining', 'subtitle' => 'Breakdown',        'color' => 'cyan'],
        ['id' => 'budget_absorb',   'group' => 'Budget', 'type' => 'column', 'source' => 'budget.monthly',       'span' => 4, 'title' => 'Cumulative Budget Used',    'subtitle' => 'Monthly Absorption', 'color' => 'violet', 'height' => 240],
        ['id' => 'budget_dept',     'group' => 'Budget', 'type' => 'table',  'source' => 'budget.by_department', 'span' => 6, 'title' => 'Budget Usage per Department', 'subtitle' => 'By Department', 'color' => 'orange'],
        ['id' => 'budget_company',  'group' => 'Budget', 'type' => 'table',  'source' => 'budget.by_company',    'span' => 6, 'title' => 'Budget Usage per Company',  'subtitle' => 'By Company',        'color' => 'blue'],
        ['id' => 'budget_activity', 'group' => 'Budget', 'type' => 'table',  'source' => 'budget.by_activity',   'span' => 6, 'title' => 'Budget Usage by Activity',  'subtitle' => 'By Activity',       'color' => 'pink'],

        // ── Voucher & Product (All Reports > Voucher & Product) ────────────────
        ['id' => 'vpl_overview',    'group' => 'Voucher & Product', 'type' => 'table', 'source' => 'vpl.company_overview', 'span' => 6, 'title' => 'Stock per Company',          'subtitle' => 'Voucher & Product', 'color' => 'violet'],
        ['id' => 'vpl_category',    'group' => 'Voucher & Product', 'type' => 'donut', 'source' => 'vpl.by_category',      'span' => 4, 'title' => 'Total Stock by Category',   'subtitle' => 'Voucher & Product', 'color' => 'blue',   'height' => 240],
        ['id' => 'vpl_usage',       'group' => 'Voucher & Product', 'type' => 'donut', 'source' => 'vpl.usage_by_reason',  'span' => 4, 'title' => 'Usage by Purpose',          'subtitle' => 'Voucher & Product', 'color' => 'orange', 'height' => 240],
        ['id' => 'vpl_top_out',     'group' => 'Voucher & Product', 'type' => 'table', 'source' => 'vpl.top_out',          'span' => 6, 'title' => 'Top 10 Most Out',           'subtitle' => 'Voucher & Product', 'color' => 'red'],
        ['id' => 'vpl_vouchers',    'group' => 'Voucher & Product', 'type' => 'table', 'source' => 'vpl.voucher_list',     'span' => 6, 'title' => 'Voucher List — Sorted by Stock', 'subtitle' => 'Voucher & Product', 'color' => 'green'],

        // ── PG Card (All Reports > PG Card) — BigQuery ─────────────────────────
        ['id' => 'pg_total',     'group' => 'PG Card', 'type' => 'split',  'source' => 'pgcard.total',      'span' => 4, 'title' => 'Transactions & Spending',  'subtitle' => 'PG Card', 'color' => 'pink'],
        ['id' => 'pg_members',   'group' => 'PG Card', 'type' => 'split',  'source' => 'pgcard.members',    'span' => 4, 'title' => 'Members & Average Ticket', 'subtitle' => 'PG Card', 'color' => 'violet'],
        ['id' => 'pg_trend',     'group' => 'PG Card', 'type' => 'column', 'source' => 'pgcard.trend',      'span' => 4, 'title' => 'Monthly Transaction Trend', 'subtitle' => 'PG Card', 'color' => 'cyan', 'height' => 240],
        ['id' => 'pg_customers', 'group' => 'PG Card', 'type' => 'table',  'source' => 'pgcard.customers',  'span' => 6, 'title' => 'Top Customers',            'subtitle' => 'PG Card', 'color' => 'pink'],
        ['id' => 'pg_tenants',   'group' => 'PG Card', 'type' => 'table',  'source' => 'pgcard.tenants',    'span' => 6, 'title' => 'Top Tenants',              'subtitle' => 'PG Card', 'color' => 'violet'],

        // ── Operation · Isort (All Reports > Operation - Isort) — BigQuery ─────
        ['id' => 'isort_summary',  'group' => 'Operation · Isort', 'type' => 'split',  'source' => 'isort.summary',   'span' => 4, 'title' => 'Kaizen Cases',            'subtitle' => 'Operation · Isort', 'color' => 'orange'],
        ['id' => 'isort_type',     'group' => 'Operation · Isort', 'type' => 'donut',  'source' => 'isort.by_type',   'span' => 4, 'title' => 'Total Kaizen by Type',    'subtitle' => 'Operation · Isort', 'color' => 'orange', 'height' => 240],
        ['id' => 'isort_trend',    'group' => 'Operation · Isort', 'type' => 'column', 'source' => 'isort.trend',     'span' => 4, 'title' => 'Monthly Issue Trend',     'subtitle' => 'Operation · Isort', 'color' => 'red',    'height' => 240],
        ['id' => 'isort_dept',     'group' => 'Operation · Isort', 'type' => 'table',  'source' => 'isort.by_department', 'span' => 6, 'title' => 'Kaizen by Department', 'subtitle' => 'Operation · Isort', 'color' => 'orange'],
        ['id' => 'isort_incident', 'group' => 'Operation · Isort', 'type' => 'table',  'source' => 'isort.by_incident',   'span' => 6, 'title' => 'Top Kaizen by Incident Type', 'subtitle' => 'Operation · Isort', 'color' => 'red'],

        // ── Parking · Valet (All Reports > Parking - Valet) — BigQuery ─────────
        ['id' => 'valet_kpi',     'group' => 'Parking · Valet', 'type' => 'split',  'source' => 'valet.kpi',         'span' => 4, 'title' => 'Valet Volume & Income', 'subtitle' => 'Parking · Valet', 'color' => 'blue'],
        ['id' => 'valet_peak',    'group' => 'Parking · Valet', 'type' => 'column', 'source' => 'valet.peak_hours',  'span' => 4, 'title' => 'Peak Check-in Hours',   'subtitle' => 'Parking · Valet', 'color' => 'blue',  'height' => 240],
        ['id' => 'valet_voucher', 'group' => 'Parking · Valet', 'type' => 'donut',  'source' => 'valet.redemption',  'span' => 4, 'title' => 'Voucher Redemption',    'subtitle' => 'Parking · Valet', 'color' => 'cyan',  'height' => 240],

        // ── Event (All Reports > Event) ────────────────────────────────────────
        ['id' => 'event_summary', 'group' => 'Event', 'type' => 'split', 'source' => 'event.summary',   'span' => 4, 'title' => 'Event Contracts',  'subtitle' => 'Event', 'color' => 'green'],
        ['id' => 'event_status',  'group' => 'Event', 'type' => 'donut', 'source' => 'event.by_status', 'span' => 4, 'title' => 'Events by Status', 'subtitle' => 'Event', 'color' => 'violet', 'height' => 240],
        ['id' => 'event_type',    'group' => 'Event', 'type' => 'table', 'source' => 'event.by_type',   'span' => 4, 'title' => 'Events by Type',   'subtitle' => 'Event', 'color' => 'green'],
    ],

    'panels' => [
        'om-summary' => [
            'title'    => 'OM · Summary',
            'subtitle' => 'Operations at a glance',
            'default'  => ['budget_kpi', 'budget_total', 'budget_mix', 'budget_absorb', 'budget_dept', 'budget_activity', 'vpl_top_out'],
        ],
        'gm-summary' => [
            'title'    => 'GM · Summary',
            'subtitle' => 'Group performance at a glance',
            'default'  => ['budget_kpi', 'budget_total', 'budget_mix', 'budget_absorb', 'budget_company', 'budget_dept', 'vpl_overview', 'vpl_top_out'],
        ],
    ],

];
