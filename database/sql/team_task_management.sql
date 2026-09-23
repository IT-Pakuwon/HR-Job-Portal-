-- =====================================================================
-- Team Task Management — a Team owns its own recursive Task tree,
-- independent of Project (a Project keeps its own separate tree, built
-- the identical way — see pm_task_recursive_merge.sql). Run against the
-- PGSQL5 connection (same connection as ms_team / ms_project).
--
-- Team Task statuses get their OWN twin table (ms_team_task_status)
-- rather than a nullable team_id bolted onto ms_task_status — that would
-- recreate the exact "nullable column doing double duty for two owners"
-- problem being removed elsewhere (see tr_project_task_assignee.task_detail_id
-- in pm_task_recursive_merge.sql). A twin table is a risk-free structural
-- copy of the already-working ms_task_status.
-- =====================================================================

CREATE TABLE IF NOT EXISTS ms_team_task_status (
    id           BIGSERIAL PRIMARY KEY,
    status_id    VARCHAR(20)  NOT NULL,
    team_id      VARCHAR(20)  NOT NULL REFERENCES ms_team(team_id),
    status_name  VARCHAR(100) NOT NULL,
    color        VARCHAR(20),
    sort_order   INTEGER      DEFAULT 0,
    status       VARCHAR(5)   DEFAULT 'A',
    created_by   VARCHAR(50),
    created_at   TIMESTAMP WITHOUT TIME ZONE,
    updated_by   VARCHAR(50),
    updated_at   TIMESTAMP WITHOUT TIME ZONE,
    deleted_by   VARCHAR(50),
    deleted_at   TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_ms_team_task_status_team_status UNIQUE (team_id, status_id)
);

-- task_id is a globally unique business key (like tr_project_task.task_id),
-- so routes/controllers never need to carry an ancestor chain in the URL —
-- a task at any depth is addressed by task_id alone.
CREATE TABLE IF NOT EXISTS tr_team_task (
    id                BIGSERIAL PRIMARY KEY,
    task_id           VARCHAR(20)  NOT NULL UNIQUE,
    team_id           VARCHAR(20)  NOT NULL REFERENCES ms_team(team_id),
    parent_task_id    VARCHAR(20)  REFERENCES tr_team_task(task_id),
    task_name         VARCHAR(255) NOT NULL,
    task_description  TEXT,
    start_date        DATE,
    end_date          DATE,
    status_id         VARCHAR(20),
    progress_percent  NUMERIC(5,2) DEFAULT 0,
    status            VARCHAR(5)   DEFAULT 'A',
    created_by        VARCHAR(50),
    created_at        TIMESTAMP WITHOUT TIME ZONE,
    updated_by        VARCHAR(50),
    updated_at        TIMESTAMP WITHOUT TIME ZONE,
    deleted_by        VARCHAR(50),
    deleted_at        TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT chk_tr_team_task_no_self_parent CHECK (task_id <> parent_task_id),
    CONSTRAINT fk_tr_team_task_status FOREIGN KEY (team_id, status_id) REFERENCES ms_team_task_status(team_id, status_id)
);
CREATE INDEX IF NOT EXISTS idx_tr_team_task_team ON tr_team_task(team_id);
CREATE INDEX IF NOT EXISTS idx_tr_team_task_parent ON tr_team_task(parent_task_id);

-- No task_detail_id double-duty here — the self-FK on tr_team_task already
-- handles unlimited depth, so one assignee row shape covers every level.
-- A Team Task's assignees must already be signed members of that Team
-- (enforced in TeamTaskController, mirroring how a Project's PIC must
-- already be a member of one of its linked Teams).
CREATE TABLE IF NOT EXISTS tr_team_task_assignee (
    id           BIGSERIAL PRIMARY KEY,
    task_id      VARCHAR(20) NOT NULL REFERENCES tr_team_task(task_id),
    username     VARCHAR(50) NOT NULL,
    assigned_by  VARCHAR(50),
    assigned_at  TIMESTAMP WITHOUT TIME ZONE,
    status       VARCHAR(5)  DEFAULT 'A'
);
CREATE INDEX IF NOT EXISTS idx_tr_team_task_assignee_task ON tr_team_task_assignee(task_id);

-- Reuses the SAME shared master tag list as Project Tasks (ms_task_tag) —
-- no second tag master.
CREATE TABLE IF NOT EXISTS tr_team_task_tag (
    id       BIGSERIAL PRIMARY KEY,
    task_id  VARCHAR(20) NOT NULL REFERENCES tr_team_task(task_id),
    tag_id   VARCHAR(30) NOT NULL REFERENCES ms_task_tag(tag_id),
    status   VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_team_task_tag UNIQUE (task_id, tag_id)
);
CREATE INDEX IF NOT EXISTS idx_tr_team_task_tag_task ON tr_team_task_tag(task_id);

-- Seed default columns for every existing Team, mirroring what
-- PmProjectController@store already seeds per-Project (TODO/In Progress/Done).
INSERT INTO ms_team_task_status (status_id, team_id, status_name, color, sort_order, status, created_by, created_at)
SELECT v.status_id, t.team_id, v.status_name, v.color, v.sort_order, 'A', 'system', now()
FROM ms_team t
CROSS JOIN (VALUES
    ('TODO', 'To Do', '#9CA3AF', 0),
    ('INPROGRESS', 'In Progress', '#3B82F6', 1),
    ('DONE', 'Done', '#10B981', 2)
) AS v(status_id, status_name, color, sort_order)
WHERE t.status = 'A'
  AND NOT EXISTS (SELECT 1 FROM ms_team_task_status x WHERE x.team_id = t.team_id AND x.status_id = v.status_id);
