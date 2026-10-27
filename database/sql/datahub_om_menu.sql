-- =====================================================================
-- Data Hub > OM Dashboard — child menu + role menu.
-- Run against the PGSQL2 connection (sys_* tables live there). Idempotent.
--
-- Already exists (not touched here): root menu DATAHUB, screens DATAHUB and
-- OMDASHBOARD, role OMACCESS, OMACCESS VIEW on OMDASHBOARD.
-- ALLDASHBOARD ("All Dashboard") is a later step and is left as-is.
--
-- Routes gate on access:OMDASHBOARD,VIEW (see routes/web.php, datahub-dashboard.om.*).
-- Company restriction is applied in the controller (user cpny_id -> mall).
-- =====================================================================

INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, screen_id, application_id, status, created_by, created_at)
SELECT 'OMDASHBOARD', 'DATAHUB', 'OM Dashboard', 'datahub-dashboard.om.index',
       'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5',
       2, 'OMDASHBOARD', 'DASHBOARD', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_menu WHERE menu_id = 'OMDASHBOARD');

-- Role -> menu (leaf only; the sidebar provider resolves parents).
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'OMACCESS', 'OMDASHBOARD', 'DATAHUB', 'A', 'system', now()
WHERE NOT EXISTS (
    SELECT 1 FROM sys_role_menu WHERE role_id = 'OMACCESS' AND menu_id = 'OMDASHBOARD'
);

-- ── "All Data" (ALLDASHBOARD) -> the rebuilt Data Hub landing page ───
-- The menu row pointed at the legacy route `datahub.index` (/data-hub). Idempotent: only touches
-- the row while it still holds the legacy route name.
UPDATE sys_menu
SET menu_route = 'datahub-dashboard.index', updated_by = 'system', updated_at = now()
WHERE menu_id = 'ALLDASHBOARD' AND menu_route = 'datahub.index';
