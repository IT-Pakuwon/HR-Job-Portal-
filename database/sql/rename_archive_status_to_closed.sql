-- =====================================================================
-- The default "Archive" Task-board column is renamed "Closed" so it isn't
-- confused with a card's Archive button (which hides a Task entirely via
-- status 'X' and never moves it into any column).
--
-- Only the display name changes. status_id stays 'ARCHIVE' on existing
-- rows because tasks reference it (tr_project_task has a composite FK
-- on project_id + status_id). New Teams get status_id 'CLOSED' (see
-- TeamController::store()).
--
-- Only rows still named exactly "Archive" are touched, so a column a
-- Team/Project has already renamed itself is left alone.
-- =====================================================================

UPDATE ms_team_task_status
SET status_name = 'Closed', updated_by = 'system', updated_at = now()
WHERE status_id = 'ARCHIVE' AND status_name = 'Archive';

UPDATE ms_project_task_status
SET status_name = 'Closed', updated_by = 'system', updated_at = now()
WHERE status_id = 'ARCHIVE' AND status_name = 'Archive';
