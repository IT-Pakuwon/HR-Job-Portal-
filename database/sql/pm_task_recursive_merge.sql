-- =====================================================================
-- Project Task Management — merge the one-level tr_project_task_detail
-- ("Subtask") into tr_project_task via a self-referencing parent_task_id,
-- so a Project's Task tree nests to unlimited depth (task -> subtask ->
-- subtask -> ...), the same recursive shape as tr_team_task
-- (team_task_management.sql). Run against the PGSQL5 connection.
-- =====================================================================

ALTER TABLE tr_project_task ADD COLUMN IF NOT EXISTS parent_task_id VARCHAR(20) REFERENCES tr_project_task(task_id);
CREATE INDEX IF NOT EXISTS idx_tr_project_task_parent ON tr_project_task(parent_task_id);
ALTER TABLE tr_project_task ADD CONSTRAINT chk_tr_project_task_no_self_parent CHECK (task_id <> parent_task_id);

-- Carry over any existing Subtask rows as ordinary Task rows one level
-- down, reusing task_detail_id AS the new task_id (SUB##### ids already
-- live in a distinct namespace from TSK##### ids, so no collision).
INSERT INTO tr_project_task (
    task_id, project_id, parent_task_id, task_name, task_description,
    start_date, end_date, status_id, progress_percent, status,
    created_by, created_at, updated_by, updated_at, deleted_by, deleted_at
)
SELECT d.task_detail_id, t.project_id, d.task_id, d.subtask_name, d.subtask_description,
       d.start_date, d.end_date, d.status_id, d.progress_percent, d.status,
       d.created_by, d.created_at, d.updated_by, d.updated_at, d.deleted_by, d.deleted_at
FROM tr_project_task_detail d
JOIN tr_project_task t ON t.task_id = d.task_id
WHERE NOT EXISTS (SELECT 1 FROM tr_project_task x WHERE x.task_id = d.task_detail_id);

-- task_detail_id values already match tr_project_task_assignee.task_detail_id
-- 1:1, so this is a straight column collapse, not a lookup/remap. Once
-- collapsed, task_id unambiguously means "assigned to this task/former-
-- subtask row" at any depth — closes the old "no cascade on subtask
-- archive" gap since there's no longer a separate row shape to forget.
UPDATE tr_project_task_assignee SET task_id = task_detail_id WHERE task_detail_id IS NOT NULL;
ALTER TABLE tr_project_task_assignee DROP COLUMN IF EXISTS task_detail_id;

-- tr_project_task_tag already only keyed on task_id — former Subtasks can
-- now be tagged for free, no schema change needed there.

DROP TABLE IF EXISTS tr_project_task_detail;
