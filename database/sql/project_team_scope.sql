-- =====================================================================
-- Project Management — switch Project scoping from ms_group to ms_team
-- Run against the PGSQL5 connection (same connection as ms_project /
-- ms_team already live on).
--
-- ms_project.group_id predates the Team module (team_management.sql) and
-- scoped a Project to the old department-scoped ms_group. Projects now
-- scope to ms_team (explicit membership, no department scoping) instead —
-- add team_id and drop the old NOT NULL on group_id so existing rows
-- aren't broken by the switch.
-- =====================================================================

ALTER TABLE ms_project ADD COLUMN IF NOT EXISTS team_id VARCHAR(20) REFERENCES ms_team(team_id);
ALTER TABLE ms_project ALTER COLUMN group_id DROP NOT NULL;

CREATE INDEX IF NOT EXISTS idx_ms_project_team ON ms_project(team_id);
