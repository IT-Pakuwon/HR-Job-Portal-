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

-- Sidebar icon (Heroicons "squares-2x2" outline path). Only fills it while empty.
UPDATE sys_menu
SET menu_icon = 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
    updated_by = 'system', updated_at = now()
WHERE menu_id = 'ALLDASHBOARD' AND (menu_icon IS NULL OR menu_icon = '');

-- ── Group the Data Hub screens under the DATAHUB application ─────────
-- Access Rights (Step 6) lists screens by application; DATAHUB had none, so its group was empty.
-- AccessRightMiddleware matches on screen_id only, so moving the application is safe.
-- "All Data" (ALLDASHBOARD) is gated by access:DATAHUB,VIEW, so it takes screen DATAHUB.
UPDATE sys_screen SET application_id = 'DATAHUB', updated_by = 'system', updated_at = now()
WHERE screen_id IN ('DATAHUB', 'OMDASHBOARD') AND application_id = 'DASHBOARD';

UPDATE sys_access_right SET application_id = 'DATAHUB', updated_by = 'system', updated_at = now()
WHERE screen_id IN ('DATAHUB', 'OMDASHBOARD') AND application_id = 'DASHBOARD';

UPDATE sys_menu SET application_id = 'DATAHUB', updated_by = 'system', updated_at = now()
WHERE menu_id IN ('DATAHUB', 'OMDASHBOARD') AND application_id = 'DASHBOARD';

UPDATE sys_menu SET screen_id = 'DATAHUB', updated_by = 'system', updated_at = now()
WHERE menu_id = 'ALLDASHBOARD' AND screen_id IS NULL;
