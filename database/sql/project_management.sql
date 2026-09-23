-- =====================================================================
-- Project Management module — schema
-- Run this block against the PGSQL5 connection (transactional-module DB,
-- same connection as tr_ticket / tr_booking_car). Matches the existing
-- convention: bigserial id + text business key, status char flag instead
-- of Eloquent SoftDeletes, created_by/updated_by/deleted_by as username
-- strings with matching _at timestamps.
--
-- Cross-connection references (tr_group_detail.department_opr_id ->
-- pgsql2.ms_department_opr) have no literal FK constraint, same as
-- tr_ticket.department_id today, since pgsql2/pgsql5 are separate
-- database connections.
--
-- ms_department_opr, ms_department.department_opr_id, sys_role.PROJECTACCESS,
-- and the sys_menu rows for PROJECT/KANBAN/ALLPROJECT/GANTT already exist —
-- nothing to create for those.
--
-- tr_task already exists on pgsql5 as an unrelated personal-calendar /
-- Google-sync feature (taskid, sync_to_google, google_event_id) — this
-- module's task tables are named tr_project_task / tr_project_task_detail /
-- tr_project_task_assignee to avoid the collision.
-- =====================================================================

-- ── Group / Team ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ms_group (
    id                 BIGSERIAL PRIMARY KEY,
    group_id           VARCHAR(20)  NOT NULL UNIQUE,
    group_name         VARCHAR(255) NOT NULL,
    group_description  TEXT,
    status             VARCHAR(5)   DEFAULT 'A',
    created_by         VARCHAR(50),
    created_at         TIMESTAMP WITHOUT TIME ZONE,
    updated_by         VARCHAR(50),
    updated_at         TIMESTAMP WITHOUT TIME ZONE,
    deleted_by         VARCHAR(50),
    deleted_at         TIMESTAMP WITHOUT TIME ZONE
);

-- Which operational departments (pgsql2.ms_department_opr) a Group is
-- scoped to. One row per department selected in the Group's multi-select2.
CREATE TABLE IF NOT EXISTS tr_group_detail (
    id                 BIGSERIAL PRIMARY KEY,
    group_id           VARCHAR(20) NOT NULL REFERENCES ms_group(group_id),
    department_opr_id  VARCHAR(20) NOT NULL,
    cpny_id            VARCHAR(10),
    status             VARCHAR(5)  DEFAULT 'A',
    created_by         VARCHAR(50),
    created_at         TIMESTAMP WITHOUT TIME ZONE,
    updated_by         VARCHAR(50),
    updated_at         TIMESTAMP WITHOUT TIME ZONE,
    deleted_by         VARCHAR(50),
    deleted_at         TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX IF NOT EXISTS idx_tr_group_detail_group ON tr_group_detail(group_id);

-- Explicit Group membership. Departments (tr_group_detail) narrow the
-- candidate pool an org admin picks from; this table is the actual stored
-- membership list, and is what backs Task/Project assignee pickers,
-- @mention audiences, and mentionable-users lookups.
CREATE TABLE IF NOT EXISTS tr_group_member (
    id          BIGSERIAL PRIMARY KEY,
    group_id    VARCHAR(20) NOT NULL REFERENCES ms_group(group_id),
    username    VARCHAR(50) NOT NULL,
    added_by    VARCHAR(50),
    added_at    TIMESTAMP WITHOUT TIME ZONE,
    status      VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_group_member_group_username UNIQUE (group_id, username)
);

CREATE INDEX IF NOT EXISTS idx_tr_group_member_group ON tr_group_member(group_id);

-- ── Project status (global, shared across all Groups/Projects — this is
--    what the portfolio Kanban groups by, so it must stay comparable) ──
CREATE TABLE IF NOT EXISTS ms_project_status (
    id           BIGSERIAL PRIMARY KEY,
    status_id    VARCHAR(20)  NOT NULL UNIQUE,
    status_name  VARCHAR(100) NOT NULL,
    color        VARCHAR(20),
    sort_order   INTEGER      DEFAULT 0,
    status       VARCHAR(5)   DEFAULT 'A',
    created_by   VARCHAR(50),
    created_at   TIMESTAMP WITHOUT TIME ZONE,
    updated_by   VARCHAR(50),
    updated_at   TIMESTAMP WITHOUT TIME ZONE,
    deleted_by   VARCHAR(50),
    deleted_at   TIMESTAMP WITHOUT TIME ZONE
);

INSERT INTO ms_project_status (status_id, status_name, color, sort_order, status, created_by, created_at)
SELECT v.status_id, v.status_name, v.color, v.sort_order, 'A', 'system', now()
FROM (VALUES
    ('NOTSTARTED', 'Not started',  '#9CA3AF', 0),
    ('INPROGRESS', 'In progress',  '#3B82F6', 1),
    ('DONE',       'Done',         '#10B981', 2)
) AS v(status_id, status_name, color, sort_order)
WHERE NOT EXISTS (SELECT 1 FROM ms_project_status WHERE status_id = v.status_id);

-- ── Project ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ms_project (
    id                   BIGSERIAL PRIMARY KEY,
    project_id           VARCHAR(20)  NOT NULL UNIQUE,
    group_id             VARCHAR(20)  NOT NULL REFERENCES ms_group(group_id),
    project_name         VARCHAR(255) NOT NULL,
    project_description  TEXT,
    start_date           DATE,
    end_date             DATE,
    status_id            VARCHAR(20)  REFERENCES ms_project_status(status_id),
    progress_percent     NUMERIC(5,2) DEFAULT 0,
    status               VARCHAR(5)   DEFAULT 'A',
    created_by           VARCHAR(50),
    created_at           TIMESTAMP WITHOUT TIME ZONE,
    updated_by           VARCHAR(50),
    updated_at           TIMESTAMP WITHOUT TIME ZONE,
    deleted_by           VARCHAR(50),
    deleted_at           TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX IF NOT EXISTS idx_ms_project_group ON ms_project(group_id);

-- Project-to-project links (symmetric — query both directions in the app,
-- guard against duplicate/reciprocal pairs on insert).
CREATE TABLE IF NOT EXISTS tr_project (
    id                 BIGSERIAL PRIMARY KEY,
    project_id         VARCHAR(20) NOT NULL REFERENCES ms_project(project_id),
    linked_project_id  VARCHAR(20) NOT NULL REFERENCES ms_project(project_id),
    status             VARCHAR(5)  DEFAULT 'A',
    created_by         VARCHAR(50),
    created_at         TIMESTAMP WITHOUT TIME ZONE,
    updated_by         VARCHAR(50),
    updated_at         TIMESTAMP WITHOUT TIME ZONE,
    deleted_by         VARCHAR(50),
    deleted_at         TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT chk_tr_project_no_self_link CHECK (project_id <> linked_project_id)
);

-- ── Task status (scoped PER PROJECT — each Project defines/extends its
--    own status columns for its own Task board) ──────────────────────
CREATE TABLE IF NOT EXISTS ms_task_status (
    id           BIGSERIAL PRIMARY KEY,
    status_id    VARCHAR(20)  NOT NULL,
    project_id   VARCHAR(20)  NOT NULL REFERENCES ms_project(project_id),
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
    CONSTRAINT uq_ms_task_status_project_status UNIQUE (project_id, status_id)
);

-- ── Task / Subtask (renamed from tr_task/tr_task_detail — collision
--    with the existing personal-calendar tr_task table) ──────────────
CREATE TABLE IF NOT EXISTS tr_project_task (
    id                BIGSERIAL PRIMARY KEY,
    task_id           VARCHAR(20)  NOT NULL UNIQUE,
    project_id        VARCHAR(20)  NOT NULL REFERENCES ms_project(project_id),
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
    CONSTRAINT fk_tr_project_task_status FOREIGN KEY (project_id, status_id)
        REFERENCES ms_task_status(project_id, status_id)
);

CREATE INDEX IF NOT EXISTS idx_tr_project_task_project ON tr_project_task(project_id);

CREATE TABLE IF NOT EXISTS tr_project_task_detail (
    id                   BIGSERIAL PRIMARY KEY,
    task_detail_id       VARCHAR(20)  NOT NULL UNIQUE,
    task_id              VARCHAR(20)  NOT NULL REFERENCES tr_project_task(task_id),
    subtask_name         VARCHAR(255) NOT NULL,
    subtask_description  TEXT,
    start_date           DATE,
    end_date             DATE,
    status_id            VARCHAR(20),
    progress_percent     NUMERIC(5,2) DEFAULT 0,
    status               VARCHAR(5)   DEFAULT 'A',
    created_by           VARCHAR(50),
    created_at           TIMESTAMP WITHOUT TIME ZONE,
    updated_by           VARCHAR(50),
    updated_at           TIMESTAMP WITHOUT TIME ZONE,
    deleted_by           VARCHAR(50),
    deleted_at           TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX IF NOT EXISTS idx_tr_project_task_detail_task ON tr_project_task_detail(task_id);

-- Shared assignee table for both Task and Subtask level. task_detail_id
-- is nullable: null = assignment is on the Task itself, set = assignment
-- is on that specific Subtask. Avoids a near-duplicate second table —
-- same nullable-FK idiom already used elsewhere (e.g. tr_ticket's
-- multiple nullable pic_* columns) rather than a polymorphic pattern.
CREATE TABLE IF NOT EXISTS tr_project_task_assignee (
    id              BIGSERIAL PRIMARY KEY,
    task_id         VARCHAR(20) NOT NULL REFERENCES tr_project_task(task_id),
    task_detail_id  VARCHAR(20) REFERENCES tr_project_task_detail(task_detail_id),
    username        VARCHAR(50) NOT NULL,
    assigned_by     VARCHAR(50),
    assigned_at     TIMESTAMP WITHOUT TIME ZONE,
    status          VARCHAR(5)  DEFAULT 'A'
);

CREATE INDEX IF NOT EXISTS idx_tr_project_task_assignee_task ON tr_project_task_assignee(task_id, task_detail_id);


-- =====================================================================
-- The block below runs against the PGSQL2 connection instead (sys_menu /
-- sys_role_menu live there). Run separately once the Laravel routes
-- below exist, so the menu_route values are valid.
-- =====================================================================

-- Point the pre-created placeholder menu rows at the new routes.
UPDATE sys_menu SET menu_route = 'projects.kanban' WHERE menu_id = 'KANBAN'     AND application_id = 'PROJECTAPP';
UPDATE sys_menu SET menu_route = 'projects.index'  WHERE menu_id = 'ALLPROJECT' AND application_id = 'PROJECTAPP';
UPDATE sys_menu SET menu_route = 'projects.gantt'  WHERE menu_id = 'GANTT'      AND application_id = 'PROJECTAPP';

-- Grant menu visibility to PROJECTACCESS holders (parent PROJECT menu_id
-- is auto-included by the sidebar's parent-walk once a child is granted,
-- but included explicitly here too for clarity).
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'PROJECTACCESS', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.menu_id IN ('PROJECT', 'KANBAN', 'ALLPROJECT', 'GANTT')
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'PROJECTACCESS' AND r.menu_id = m.menu_id
  );

-- ── ORGPROJECTACCESS: dedicated org-admin role for Group/Team management ──
-- (creating/editing/deactivating Groups & Teams), separate from
-- PROJECTACCESS (Project/Task/Subtask creation + day-to-day module use).
-- Both roles can browse the Projects pages; only ORGPROJECTACCESS can
-- create/manage Groups; only PROJECTACCESS can create Projects/Tasks/Subtasks.
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT 'ORGPROJECTACCESS', 'Access Project Organization (Group/Team management)', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'ORGPROJECTACCESS');

INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'ORGPROJECTACCESS', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.menu_id IN ('PROJECT', 'KANBAN', 'ALLPROJECT', 'GANTT')
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'ORGPROJECTACCESS' AND r.menu_id = m.menu_id
  );
