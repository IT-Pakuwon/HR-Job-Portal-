-- =====================================================================
-- Project Management — PIC (person in charge) on a Project, and quick-add
-- support for the portfolio Kanban ("+" inside a status column).
-- Run against the PGSQL5 connection (same connection as ms_project).
-- =====================================================================

ALTER TABLE ms_project ADD COLUMN IF NOT EXISTS pic_username VARCHAR(50);
