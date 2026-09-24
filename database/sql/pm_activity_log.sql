-- =====================================================================
-- Activity history for Teams, Projects and their Tasks (TSK/TTK).
-- Every mutation in PmProjectController / PmTaskController /
-- TeamController / TeamTaskController writes one row here (see
-- App\Services\PmActivityLogger). Chat messages and file uploads are NOT
-- copied in — the History/Activity views read them live from pgsql2's
-- tr_message / tr_attachment, so older ones show up as well.
--
-- scope_type/scope_id = the board the event belongs to ('PROJECT' +
-- project_id, or 'TEAM' + team_id). task_id is set for task-level
-- events; NULL means the event is about the Project/Team itself.
-- changes = [{"label": "Status", "from": "To Do", "to": "Done"}, …]
-- Run against the PGSQL5 connection.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_pm_activity (
    id          BIGSERIAL PRIMARY KEY,
    scope_type  VARCHAR(10)  NOT NULL,
    scope_id    VARCHAR(20)  NOT NULL,
    task_id     VARCHAR(20),
    action      VARCHAR(30)  NOT NULL,
    description TEXT         NOT NULL,
    changes     JSONB,
    created_by  VARCHAR(50),
    created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_tr_pm_activity_scope ON tr_pm_activity (scope_type, scope_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tr_pm_activity_task ON tr_pm_activity (task_id);

-- One-time backfill: a "created" entry for everything that existed before
-- logging started, stamped with its own created_at. Safe to re-run.
INSERT INTO tr_pm_activity (scope_type, scope_id, task_id, action, description, created_by, created_at)
SELECT 'PROJECT', p.project_id, NULL, 'created', 'created the project', p.created_by, p.created_at
FROM ms_project p
WHERE p.created_at IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM tr_pm_activity a WHERE a.scope_type = 'PROJECT' AND a.scope_id = p.project_id AND a.task_id IS NULL AND a.action = 'created');

INSERT INTO tr_pm_activity (scope_type, scope_id, task_id, action, description, created_by, created_at)
SELECT 'TEAM', t.team_id, NULL, 'created', 'created the team', t.created_by, t.created_at
FROM ms_team t
WHERE t.created_at IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM tr_pm_activity a WHERE a.scope_type = 'TEAM' AND a.scope_id = t.team_id AND a.task_id IS NULL AND a.action = 'created');

INSERT INTO tr_pm_activity (scope_type, scope_id, task_id, action, description, created_by, created_at)
SELECT 'PROJECT', t.project_id, t.task_id, 'created',
       CASE WHEN t.parent_task_id IS NULL THEN 'created the task' ELSE 'added the subtask' END,
       t.created_by, t.created_at
FROM tr_project_task t
WHERE t.created_at IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM tr_pm_activity a WHERE a.task_id = t.task_id AND a.action = 'created');

INSERT INTO tr_pm_activity (scope_type, scope_id, task_id, action, description, created_by, created_at)
SELECT 'TEAM', t.team_id, t.task_id, 'created',
       CASE WHEN t.parent_task_id IS NULL THEN 'created the task' ELSE 'added the subtask' END,
       t.created_by, t.created_at
FROM tr_team_task t
WHERE t.created_at IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM tr_pm_activity a WHERE a.task_id = t.task_id AND a.action = 'created');
