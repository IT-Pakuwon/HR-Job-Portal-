-- =====================================================================
-- REPORTLEGALLEASE — "Legal Lease" report dashboard under the sidebar's
-- DASHBOARD group (next to All Report / Corporate Teknik / IT Support /
-- Training Report). Route: legal-lease-report.dashboard
-- Run against the PGSQL2 connection (sys_* tables). Idempotent.
--
-- Access: screen REPORTLEGALLEASE, access VIEW, granted to
--   LEGALAGREEMENT (Legal staff) and DASHACCESS (dashboard admins).
-- =====================================================================
BEGIN;

INSERT INTO sys_screen (screen_id, screen_name, application_id, status, created_by, created_at)
SELECT 'REPORTLEGALLEASE', 'Legal Lease Report', 'DASHBOARD', 'A', 'system', now()
WHERE NOT EXISTS (
    SELECT 1 FROM sys_screen WHERE screen_id = 'REPORTLEGALLEASE' AND application_id = 'DASHBOARD'
);

INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, screen_id, application_id, status, created_by, created_at)
SELECT 'REPORTLEGALLEASE', 'DASHBOARD', 'Legal Lease', 'legal-lease-report.dashboard',
       'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
       7, 'REPORTLEGALLEASE', 'DASHBOARD', 'A', 'system', now()
WHERE NOT EXISTS (
    SELECT 1 FROM sys_menu WHERE menu_id = 'REPORTLEGALLEASE' AND application_id = 'DASHBOARD'
);

INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT r.role_id, 'REPORTLEGALLEASE', 'DASHBOARD', 'A', 'system', now()
FROM (VALUES ('LEGALAGREEMENT'), ('DASHACCESS')) AS r(role_id)
WHERE EXISTS (SELECT 1 FROM sys_role x WHERE x.role_id = r.role_id)
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu m WHERE m.role_id = r.role_id AND m.menu_id = 'REPORTLEGALLEASE'
  );

INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, status, created_by, created_at)
SELECT r.role_id, 'REPORTLEGALLEASE', 'DASHBOARD', 'VIEW', true, 'A', 'system', now()
FROM (VALUES ('LEGALAGREEMENT'), ('DASHACCESS')) AS r(role_id)
WHERE EXISTS (SELECT 1 FROM sys_role x WHERE x.role_id = r.role_id)
  AND NOT EXISTS (
      SELECT 1 FROM sys_access_right a
      WHERE a.role_id = r.role_id AND a.screen_id = 'REPORTLEGALLEASE' AND a.access_name = 'VIEW'
  );

COMMIT;
