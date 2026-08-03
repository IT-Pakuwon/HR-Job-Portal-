-- =====================================================================
-- Spin Wheel (Lucky Draw) — dedicated roles
-- Run this block against the PGSQL2 connection (sys_role / sys_access_right
-- / sys_user_role live there — same connection as sys_user_role, matching
-- the existing convention documented in database/sql/project_management.sql).
--
-- Replaces the previous reuse of ADEVENTACCESS / EVENTACCESS (pre-existing
-- roles "Access Admin Event" / "Access Event", already granted VIEW/CREATE/
-- EDIT/DELETE/FULLACCESS on screen_id SPINWHEELS, already assigned to
-- bedriamaail (both), febiawulandari, muhammadnaufal, williemhalim
-- (EVENTACCESS)) with two roles dedicated to this feature only:
--   - SPINWHEELADMIN  : operator console — event select, Go Live/End Live,
--     stats, import participants, wheel settings, candidate validation.
--     See app/Http/Controllers/SpinwheelController.php and
--     resources/views/pages/spinwheel/admin.blade.php.
--   - SPINWHEELACCESS : audience / live-display screen — spin button,
--     read-only candidate status, winner history.
--     See resources/views/pages/spinwheel/audience.blade.php.
--
-- ADEVENTACCESS / EVENTACCESS roles themselves are left intact (they may
-- grant access to other screens) — only their SPINWHEELS-scoped
-- access-right rows are retired (status='X'), since those are superseded
-- by the new dedicated roles.
-- =====================================================================

-- ── New roles ──────────────────────────────────────────────────────────
INSERT INTO sys_role (role_id, role_name, status, created_by)
SELECT 'SPINWHEELADMIN', 'Spin Wheel - Operator (manage draws, validate winners)', 'A', 'bedriamaail'
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'SPINWHEELADMIN');

INSERT INTO sys_role (role_id, role_name, status, created_by)
SELECT 'SPINWHEELACCESS', 'Spin Wheel - Audience (live draw display)', 'A', 'bedriamaail'
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'SPINWHEELACCESS');

-- ── Route-level permissions ──────────────────────────────────────────────
-- AccessRightMiddleware (app/Http/Middleware/AccessRightMiddleware.php)
-- matches screen_id + access_name literally against the
-- `access:SPINWHEELS,VIEW` / `access:SPINWHEELS,CREATE` route groups in
-- routes/web.php, so only VIEW/CREATE are needed (unlike the old roles'
-- rows, nothing in this app checks EDIT/DELETE/FULLACCESS for SPINWHEELS).
INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, access_type, status, created_by)
SELECT v.role_id, 'SPINWHEELS', 'EVENT', v.access_name, true, 'NORMAL', 'A', 'bedriamaail'
FROM (VALUES
    ('SPINWHEELADMIN',  'VIEW'),
    ('SPINWHEELADMIN',  'CREATE'),
    ('SPINWHEELACCESS', 'VIEW')
) AS v(role_id, access_name)
WHERE NOT EXISTS (
    SELECT 1 FROM sys_access_right r
    WHERE r.role_id = v.role_id AND r.screen_id = 'SPINWHEELS' AND r.access_name = v.access_name
);

-- ── Migrate existing spinwheel users to the new roles ────────────────────
INSERT INTO sys_user_role (username, role_id, status, created_by)
SELECT v.username, v.role_id, 'A', 'bedriamaail'
FROM (VALUES
    ('bedriamaail',     'SPINWHEELADMIN'),
    ('bedriamaail',     'SPINWHEELACCESS'),
    ('febiawulandari',  'SPINWHEELACCESS'),
    ('muhammadnaufal',  'SPINWHEELACCESS'),
    ('williemhalim',    'SPINWHEELACCESS')
) AS v(username, role_id)
WHERE NOT EXISTS (
    SELECT 1 FROM sys_user_role r
    WHERE r.username = v.username AND r.role_id = v.role_id AND r.status = 'A'
);

-- ── Retire the old SPINWHEELS-scoped grants (role itself stays intact) ──
UPDATE sys_access_right
SET status = 'X', updated_by = 'bedriamaail'
WHERE screen_id = 'SPINWHEELS'
  AND role_id IN ('ADEVENTACCESS', 'EVENTACCESS')
  AND status = 'A';

-- ── Sidebar menu visibility (confirmed menu_id='SPINWHEELS', parent 'EVENT') ──
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by)
SELECT v.role_id, 'SPINWHEELS', 'EVENT', 'A', 'bedriamaail'
FROM (VALUES ('SPINWHEELADMIN'), ('SPINWHEELACCESS')) AS v(role_id)
WHERE NOT EXISTS (
    SELECT 1 FROM sys_role_menu r WHERE r.role_id = v.role_id AND r.menu_id = 'SPINWHEELS'
);
