-- =====================================================================
-- Task lock: a locked Project Task (and its whole subtree) can only be
-- opened by the people assigned to it — everyone else still sees the
-- card on the board, but without its description/subtasks/chat/files.
-- Admins / PROADMINACCESS keep access as a recovery path.
-- See TrProjectTask::isAccessibleBy() and PmTaskController::toggleLock().
-- =====================================================================

ALTER TABLE tr_project_task ADD COLUMN IF NOT EXISTS is_locked boolean NOT NULL DEFAULT false;
ALTER TABLE tr_project_task ADD COLUMN IF NOT EXISTS locked_by varchar(50);
ALTER TABLE tr_project_task ADD COLUMN IF NOT EXISTS locked_at timestamp;
