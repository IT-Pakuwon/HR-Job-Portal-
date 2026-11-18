-- Tenancy Support: application, roles, screens, menus, role access (PGSQL2). Idempotent.
--   TENANTSUPPORT "Tenancy Support"
--     ├─ TSMASTERDATA "Master Data"
--     ├─ TSSIKREQUEST "SIK Request"
--     └─ TSSIKMBREQUEST "SIKMB Request"
-- Roles: ACCESSTS (Access Tenancy Support), ACCESSTR (Access Tenant Relation)
-- ACCESSTS -> SIK Request, SIKMB Request.  ACCESSTR -> Master Data only.  Each gets VIEW/CREATE/EDIT/DELETE on its screens.
-- Child menus need a non-empty menu_route or the sidebar hides them (route names are placeholders until pages exist). User bedriamaail is assigned both roles (sys_user_role).

INSERT INTO sys_application (application_id, application_name, status, created_by, created_at)
SELECT 'TENANTSUPPORTAPP', 'Tenancy Support APP', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_application WHERE application_id = 'TENANTSUPPORTAPP');

INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT v.role_id, v.role_name, 'A', 'system', now()
FROM (VALUES ('ACCESSTS','Access Tenancy Support'),('ACCESSTR','Access Tenant Relation')) AS v(role_id, role_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_role r WHERE r.role_id = v.role_id);

INSERT INTO sys_screen (screen_id, screen_name, application_id, status, created_by, created_at)
SELECT v.screen_id, v.screen_name, 'TENANTSUPPORTAPP', 'A', 'system', now()
FROM (VALUES
  ('TSMASTERDATA','Tenancy Support - Master Data'),
  ('TSSIKREQUEST','Tenancy Support - SIK Request'),
  ('TSSIKMBREQUEST','Tenancy Support - SIKMB Request')
) AS v(screen_id, screen_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_screen s WHERE s.screen_id = v.screen_id AND s.application_id = 'TENANTSUPPORTAPP');

INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, screen_id, application_id, status, created_by, created_at)
SELECT v.menu_id, v.parent_menu_id, v.menu_name, v.menu_route,
       'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
       v.sort_order, v.screen_id, 'TENANTSUPPORTAPP', 'A', 'system', now()
FROM (VALUES
  ('TENANTSUPPORT', NULL::varchar,  'Tenancy Support', 20, NULL::varchar, NULL::varchar),
  ('TSMASTERDATA','TENANTSUPPORT','Master Data',    1,'TSMASTERDATA','tenantsupport.master'),
  ('TSSIKREQUEST','TENANTSUPPORT','SIK Request',    2,'TSSIKREQUEST','tenantsupport.sik'),
  ('TSSIKMBREQUEST','TENANTSUPPORT','SIKMB Request',3,'TSSIKMBREQUEST','tenantsupport.sikmb')
) AS v(menu_id, parent_menu_id, menu_name, sort_order, screen_id, menu_route)
WHERE NOT EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_id = v.menu_id AND m.application_id = 'TENANTSUPPORTAPP');

INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT r.role_id, m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
JOIN (VALUES ('ACCESSTR','TSMASTERDATA'),('ACCESSTS','TSSIKREQUEST'),('ACCESSTS','TSSIKMBREQUEST')) AS r(role_id, menu_id) ON r.menu_id = m.menu_id
WHERE m.application_id = 'TENANTSUPPORTAPP' AND m.parent_menu_id = 'TENANTSUPPORT'
  AND NOT EXISTS (SELECT 1 FROM sys_role_menu x WHERE x.role_id = r.role_id AND x.menu_id = m.menu_id);

INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, status, created_by, created_at)
SELECT r.role_id, s.screen_id, 'TENANTSUPPORTAPP', a.access_name, true, 'A', 'system', now()
FROM sys_screen s
JOIN (VALUES ('ACCESSTR','TSMASTERDATA'),('ACCESSTS','TSSIKREQUEST'),('ACCESSTS','TSSIKMBREQUEST')) AS r(role_id, screen_id) ON r.screen_id = s.screen_id
CROSS JOIN (VALUES ('VIEW'),('CREATE'),('EDIT'),('DELETE')) AS a(access_name)
WHERE s.application_id = 'TENANTSUPPORTAPP'
  AND NOT EXISTS (SELECT 1 FROM sys_access_right x WHERE x.role_id = r.role_id AND x.screen_id = s.screen_id AND x.access_name = a.access_name);

INSERT INTO sys_user_role (username, role_id, status, created_by, created_at)
SELECT 'bedriamaail', v.r, 'A', 'system', now() FROM (VALUES ('ACCESSTS'),('ACCESSTR')) v(r)
WHERE NOT EXISTS (SELECT 1 FROM sys_user_role x WHERE x.username='bedriamaail' AND x.role_id=v.r);

-- Existing rows: make children visible in the sidebar
UPDATE sys_menu SET menu_route = CASE menu_id WHEN 'TSMASTERDATA' THEN 'tenantsupport.master' WHEN 'TSSIKREQUEST' THEN 'tenantsupport.sik' WHEN 'TSSIKMBREQUEST' THEN 'tenantsupport.sikmb' END
WHERE application_id='TENANTSUPPORTAPP' AND menu_id IN ('TSMASTERDATA','TSSIKREQUEST','TSSIKMBREQUEST') AND menu_route IS NULL;
