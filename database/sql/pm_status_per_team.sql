-- =====================================================================
-- Project Management — per-Team Kanban status columns.
-- Run against the PGSQL5 connection (same connection as ms_project_status
-- / ms_team already live on).
--
-- Previously every Team's portfolio Kanban rendered ALL active
-- ms_project_status rows as columns — there was no way to have a status
-- exist in the shared "master" list without it also being a column on
-- every single Team's board. This link table lets each Team explicitly
-- enable which master statuses it uses, so "+ Add status" can offer
-- master statuses not yet enabled for the current Team as one-click
-- adds, distinct from typing a brand new one.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_status_team (
    id          BIGSERIAL PRIMARY KEY,
    status_id   VARCHAR(20) NOT NULL REFERENCES ms_project_status(status_id),
    team_id     VARCHAR(20) NOT NULL REFERENCES ms_team(team_id),
    status      VARCHAR(5)  DEFAULT 'A',
    created_by  VARCHAR(50),
    created_at  TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_tr_project_status_team UNIQUE (status_id, team_id)
);

CREATE INDEX IF NOT EXISTS idx_tr_project_status_team_team ON tr_project_status_team(team_id);
CREATE INDEX IF NOT EXISTS idx_tr_project_status_team_status ON tr_project_status_team(status_id);

-- Backfill: every currently-active master status was, until now, shown on
-- every Team's board — so enable all of them for all existing Teams to
-- keep today's boards looking exactly the same after the switch.
INSERT INTO tr_project_status_team (status_id, team_id, status, created_by, created_at)
SELECT s.status_id, t.team_id, 'A', 'system', now()
FROM ms_project_status s
CROSS JOIN ms_team t
WHERE s.status = 'A' AND t.status = 'A'
  AND NOT EXISTS (
      SELECT 1 FROM tr_project_status_team x
      WHERE x.status_id = s.status_id AND x.team_id = t.team_id
  );
