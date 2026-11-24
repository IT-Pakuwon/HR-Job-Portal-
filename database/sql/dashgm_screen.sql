-- =====================================================================
-- DASHGM — "Dashboard GM" multidashboard homepage option (a copy of DASHOM).
-- Run against the PGSQL2 connection. Idempotent.
-- Needs dashboard_menu.sql to have been applied first (ms_dashboard_menu_map).
-- =====================================================================
INSERT INTO sys_screen (screen_id, screen_name, application_id, status, created_by, created_at)
SELECT 'DASHGM', 'Dashboard GM', 'DASHBOARD', 'A', 'system', now()
WHERE NOT EXISTS (
    SELECT 1 FROM sys_screen WHERE screen_id = 'DASHGM' AND application_id = 'DASHBOARD'
);

-- Same Main Menu tiles as DASHOM (copied from whatever DASHOM has now).
INSERT INTO ms_dashboard_menu_map (dash_cd, menu_key, sys_menu_id, menu_desc, sort_no, created_by)
SELECT 'DASHGM', m.menu_key, m.sys_menu_id, m.menu_desc, m.sort_no, 'system'
FROM ms_dashboard_menu_map m
WHERE m.dash_cd = 'DASHOM'
  AND NOT EXISTS (
      SELECT 1 FROM ms_dashboard_menu_map x
      WHERE x.dash_cd = 'DASHGM'
        AND x.menu_key IS NOT DISTINCT FROM m.menu_key
        AND x.sys_menu_id IS NOT DISTINCT FROM m.sys_menu_id
  );
