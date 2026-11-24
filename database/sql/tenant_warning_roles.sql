-- Role ACCESSFINTS (PGSQL2): sets tenant warnings; sees only the Warning tab of Tenancy Support > Master Data. Idempotent.
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT 'ACCESSFINTS', 'Access Finance Tenant Support', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'ACCESSFINTS');

INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'ACCESSFINTS', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.application_id = 'TENANTSUPPORTAPP' AND m.menu_id = 'TSMASTERDATA'
  AND NOT EXISTS (SELECT 1 FROM sys_role_menu x WHERE x.role_id = 'ACCESSFINTS' AND x.menu_id = m.menu_id);

INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, status, created_by, created_at)
SELECT 'ACCESSFINTS', 'TSMASTERDATA', 'TENANTSUPPORTAPP', a.access_name, true, 'A', 'system', now()
FROM (VALUES ('VIEW'),('CREATE'),('EDIT'),('DELETE')) AS a(access_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_access_right x WHERE x.role_id = 'ACCESSFINTS' AND x.screen_id = 'TSMASTERDATA' AND x.access_name = a.access_name);
