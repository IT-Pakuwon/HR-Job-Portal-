-- =====================================================================
-- Team module (v2 concept) — schema
-- Run the first block against the PGSQL5 connection (transactional-module
-- DB, same connection as ms_group / ms_project). Run the second block
-- against PGSQL2 (sys_role / sys_menu / sys_role_menu live there).
--
-- Concept: a Team is NOT scoped to a single company/department (unlike the
-- old ms_group, which is scoped via tr_group_detail -> department_opr_id).
-- A Team can freely mix users from different companies and departments.
-- Only users holding the CAPTACCESS role ("Captain Access") can create a
-- Team; the creator becomes that Team's Captain (tracked as a
-- tr_team_member row with member_role = 'CAPTAIN', so "my teams" queries
-- are a single join instead of an OR against two places).
-- =====================================================================

-- ── Team ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ms_team (
    id                BIGSERIAL PRIMARY KEY,
    team_id           VARCHAR(20)  NOT NULL UNIQUE,
    team_name         VARCHAR(255) NOT NULL,
    team_description  TEXT,
    status            VARCHAR(5)   DEFAULT 'A',
    created_by        VARCHAR(50),
    created_at        TIMESTAMP WITHOUT TIME ZONE,
    updated_by        VARCHAR(50),
    updated_at        TIMESTAMP WITHOUT TIME ZONE,
    deleted_by        VARCHAR(50),
    deleted_at        TIMESTAMP WITHOUT TIME ZONE
);

-- Explicit membership — no department/company scoping. member_role marks
-- the single Captain (creator) vs regular Members; both are ordinary rows
-- so listing "teams I belong to" is one join against this table.
CREATE TABLE IF NOT EXISTS tr_team_member (
    id           BIGSERIAL PRIMARY KEY,
    team_id      VARCHAR(20) NOT NULL REFERENCES ms_team(team_id),
    username     VARCHAR(50) NOT NULL,
    member_role  VARCHAR(10) NOT NULL DEFAULT 'MEMBER', -- CAPTAIN | MEMBER
    added_by     VARCHAR(50),
    added_at     TIMESTAMP WITHOUT TIME ZONE,
    status       VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_team_member_team_username UNIQUE (team_id, username)
);

CREATE INDEX IF NOT EXISTS idx_tr_team_member_team ON tr_team_member(team_id);
CREATE INDEX IF NOT EXISTS idx_tr_team_member_username ON tr_team_member(username);

-- Exactly one active Captain per Team.
CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_team_member_one_captain
    ON tr_team_member(team_id)
    WHERE member_role = 'CAPTAIN' AND status = 'A';


-- =====================================================================
-- The block below runs against the PGSQL2 connection (sys_role / sys_menu
-- / sys_role_menu).
-- =====================================================================

-- ── CAPTACCESS: only holders of this role can create a Team (become a
--    Captain). Anyone who can already reach the module can still browse
--    "All Team" — see the sys_role_menu grant below.
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT 'CAPTACCESS', 'Captain Access (create/manage Teams)', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'CAPTACCESS');

-- ── ALLTEAM menu — nested under the existing PROJECT menu (same parent as
--    ALLPROJECT/KANBAN/GANTT), so it shows up inside the "Project" sidebar
--    group next to "All Project" instead of as its own top-level section.
INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, application_id, status, created_by)
SELECT 'ALLTEAM', 'PROJECT', 'All Team',
       'all-team.index',
       'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
       0, 'PROJECTAPP', 'A', 'system'
WHERE NOT EXISTS (SELECT 1 FROM sys_menu WHERE menu_id = 'ALLTEAM' AND application_id = 'PROJECTAPP');

-- Grant CAPTACCESS holders the menu (they create/manage Teams).
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'CAPTACCESS', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.menu_id = 'ALLTEAM' AND m.application_id = 'PROJECTAPP'
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'CAPTACCESS' AND r.menu_id = m.menu_id
  );

-- Existing PROJECTACCESS holders can also browse "All Team" (read-only —
-- the Create/Edit/Delete actions are still gated to CAPTACCESS in-app).
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'PROJECTACCESS', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.menu_id = 'ALLTEAM' AND m.application_id = 'PROJECTAPP'
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'PROJECTACCESS' AND r.menu_id = m.menu_id
  );
