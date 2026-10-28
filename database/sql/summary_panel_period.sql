-- =====================================================================
-- Summary panels (OM Summary / GM Summary) — admin-set default period.
-- Run against the PGSQL2 connection (db_iamsystem_test2). Idempotent.
--
-- ALTERs ms_summary_layout (summary_panel_layout.sql): adds default_period as the LAST column.
-- It is a panel-level setting, so every row of a panel carries the same value (written together on save).
-- NULL / no rows = 'today'. Set in Dashboard Setup > OM / GM Summary; the panel always opens on it.
-- =====================================================================
ALTER TABLE ms_summary_layout ADD COLUMN IF NOT EXISTS default_period varchar(10) NULL;  -- today | week | month | year
