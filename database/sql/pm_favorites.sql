-- =====================================================================
-- Project Management — "My Favorite" (star) support for the sidebar's
-- Teams / Projects lists. Run against the PGSQL5 connection (same
-- connection as ms_team / ms_project).
--
-- One shared table for both kinds (fav_type + ref_id) instead of two
-- near-duplicate tables — same idiom as tr_project_task_assignee's
-- nullable task_detail_id doing double duty for Task vs Subtask.
-- Per-user: starring is personal, not shared across the Team/Project.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_favorite (
    id          BIGSERIAL PRIMARY KEY,
    username    VARCHAR(50) NOT NULL,
    fav_type    VARCHAR(10) NOT NULL, -- TEAM | PROJECT
    ref_id      VARCHAR(20) NOT NULL, -- ms_team.team_id or ms_project.project_id
    created_at  TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_tr_favorite UNIQUE (username, fav_type, ref_id)
);

CREATE INDEX IF NOT EXISTS idx_tr_favorite_username ON tr_favorite(username);
