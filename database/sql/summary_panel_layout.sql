-- =====================================================================
-- Summary panels (OM Summary / GM Summary) — admin layout override.
-- Run against the PGSQL5 connection (db_das_test). Idempotent.
--
-- config/summary_panels.php is the catalog + default layout. A row here overrides ONE widget of ONE panel
-- (order, visibility, width, title, colour, chart height). A panel with no rows follows the config default;
-- a widget added to the config later and not in this table is shown after the saved ones.
-- Read/written by App\Services\Dashboard\SummaryPanels\SummaryLayout and SummarySetupController.
-- =====================================================================
CREATE TABLE IF NOT EXISTS ms_summary_layout (
    panel_key   varchar(30)  NOT NULL,            -- 'om-summary' | 'gm-summary'
    widget_id   varchar(40)  NOT NULL,            -- 'id' of the widget in config/summary_panels.php
    sort_no     integer      NOT NULL DEFAULT 0,
    is_visible  boolean      NOT NULL DEFAULT true,
    span        smallint     NULL,                -- width out of 12; null = config default
    title       varchar(100) NULL,
    color       varchar(20)  NULL,
    height      smallint     NULL,                -- chart height in px
    created_by  varchar(50)  NULL,
    created_at  timestamp    NULL,
    updated_by  varchar(50)  NULL,
    updated_at  timestamp    NULL,
    PRIMARY KEY (panel_key, widget_id)
);
