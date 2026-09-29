-- =====================================================================
-- A Project Task's PIC can be whole Teams, not just individual people.
-- Rows here sit beside tr_project_task_assignee (people): a task's
-- effective assignees = its direct people + every current member of
-- its assigned Teams (live, so joining/leaving a Team follows through).
-- Only Teams already linked to the task's Project are offered.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_task_team (
    id          bigserial PRIMARY KEY,
    task_id     varchar(20) NOT NULL,
    team_id     varchar(20) NOT NULL,
    assigned_by varchar(50),
    assigned_at timestamp,
    status      varchar(5) NOT NULL DEFAULT 'A'
);

CREATE INDEX IF NOT EXISTS tr_project_task_team_task_idx ON tr_project_task_team (task_id, status);
