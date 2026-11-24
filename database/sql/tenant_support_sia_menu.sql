-- Tenancy Support > "SIA Request" menu (PGSQL2), next to "SIK Request" and "SIKMB Request". Idempotent.
--   TENANTSUPPORT "Tenancy Support"
--     ├─ TSSIKREQUEST   "SIK Request"
--     ├─ TSSIKMBREQUEST "SIKMB Request"
--     └─ TSSIAREQUEST   "SIA Request"   <- this script
-- Same role as the other two document screens: ACCESSTS (Access Tenancy Support), VIEW/CREATE/EDIT/DELETE.

INSERT INTO sys_screen (screen_id, screen_name, application_id, status, created_by, created_at)
SELECT 'TSSIAREQUEST', 'Tenancy Support - SIA Request', 'TENANTSUPPORTAPP', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_screen s WHERE s.screen_id = 'TSSIAREQUEST' AND s.application_id = 'TENANTSUPPORTAPP');

-- Make room at position 4 (Checker TS and Complaint Jobs moved down one). Order matters: Complaint first. Re-running is a no-op.
UPDATE sys_menu SET menu_sort_order = 6 WHERE application_id = 'TENANTSUPPORTAPP' AND menu_id = 'TSCOMPLAINT' AND menu_sort_order = 5
  AND EXISTS (SELECT 1 FROM sys_menu c WHERE c.application_id = 'TENANTSUPPORTAPP' AND c.menu_id = 'TSCHECKER' AND c.menu_sort_order = 4);
UPDATE sys_menu SET menu_sort_order = 5 WHERE application_id = 'TENANTSUPPORTAPP' AND menu_id = 'TSCHECKER' AND menu_sort_order = 4;

-- Icon is copied from the SIKMB Request menu so the three entries look alike.
INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, screen_id, application_id, status, created_by, created_at)
SELECT 'TSSIAREQUEST', 'TENANTSUPPORT', 'SIA Request', 'tenantsupport.sia', m.menu_icon, 4, 'TSSIAREQUEST', 'TENANTSUPPORTAPP', 'A', 'system', now()
FROM sys_menu m
WHERE m.menu_id = 'TSSIKMBREQUEST' AND m.application_id = 'TENANTSUPPORTAPP'
  AND NOT EXISTS (SELECT 1 FROM sys_menu x WHERE x.menu_id = 'TSSIAREQUEST' AND x.application_id = 'TENANTSUPPORTAPP');

INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'ACCESSTS', 'TSSIAREQUEST', 'TENANTSUPPORT', 'A', 'system', now()
WHERE EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_id = 'TSSIAREQUEST' AND m.application_id = 'TENANTSUPPORTAPP')
  AND NOT EXISTS (SELECT 1 FROM sys_role_menu x WHERE x.role_id = 'ACCESSTS' AND x.menu_id = 'TSSIAREQUEST');

INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, status, created_by, created_at)
SELECT 'ACCESSTS', 'TSSIAREQUEST', 'TENANTSUPPORTAPP', a.access_name, true, 'A', 'system', now()
FROM (VALUES ('VIEW'),('CREATE'),('EDIT'),('DELETE')) AS a(access_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_access_right x WHERE x.role_id = 'ACCESSTS' AND x.screen_id = 'TSSIAREQUEST' AND x.access_name = a.access_name);
