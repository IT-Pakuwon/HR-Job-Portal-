-- =====================================================================
-- Project Management — a Project can now be handled by more than one
-- Team. Converts ms_project.team_id (single, added by
-- project_team_scope.sql) into a proper many-to-many via tr_project_team.
-- Run against the PGSQL5 connection.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_team (
    id          BIGSERIAL PRIMARY KEY,
    project_id  VARCHAR(20) NOT NULL REFERENCES ms_project(project_id),
    team_id     VARCHAR(20) NOT NULL REFERENCES ms_team(team_id),
    added_by    VARCHAR(50),
    added_at    TIMESTAMP WITHOUT TIME ZONE,
    status      VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_project_team_project_team UNIQUE (project_id, team_id)
);
CREATE INDEX IF NOT EXISTS idx_tr_project_team_project ON tr_project_team(project_id);
CREATE INDEX IF NOT EXISTS idx_tr_project_team_team ON tr_project_team(team_id);

-- Carry over the single team_id each Project already had.
INSERT INTO tr_project_team (project_id, team_id, added_by, added_at, status)
SELECT project_id, team_id, 'system', now(), 'A'
FROM ms_project
WHERE team_id IS NOT NULL
ON CONFLICT (project_id, team_id) DO NOTHING;

ALTER TABLE ms_project DROP COLUMN IF EXISTS team_id;
