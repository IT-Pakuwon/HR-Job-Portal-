-- Role ACCESSCHECKERSEC (PGSQL2): Checker TS at the Security post. ACCESSCHECKERTS stays the Posko FCC post. Same TSCHECKER screen/menu. Idempotent.
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT 'ACCESSCHECKERSEC', 'Access Checker Security', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'ACCESSCHECKERSEC');

INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'ACCESSCHECKERSEC', 'TSCHECKER', 'TENANTSUPPORT', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role_menu WHERE role_id = 'ACCESSCHECKERSEC' AND menu_id = 'TSCHECKER');

INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, status, created_by, created_at)
SELECT 'ACCESSCHECKERSEC', 'TSCHECKER', 'TENANTSUPPORTAPP', a.access_name, true, 'A', 'system', now()
FROM (VALUES ('VIEW'),('CREATE'),('EDIT'),('DELETE')) AS a(access_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_access_right x WHERE x.role_id='ACCESSCHECKERSEC' AND x.screen_id='TSCHECKER' AND x.access_name=a.access_name);
