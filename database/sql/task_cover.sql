-- =====================================================================
-- Task cover image: a banner shown on a Task's detail header and on its
-- board card. Points at a tr_attachment row stored under doctype
-- TSKCOVER / TTKCOVER (kept out of the task's File tab).
-- See Traits\ManagesTaskCover.
-- =====================================================================

ALTER TABLE tr_project_task ADD COLUMN IF NOT EXISTS cover_attachment_id bigint;
ALTER TABLE tr_team_task ADD COLUMN IF NOT EXISTS cover_attachment_id bigint;
