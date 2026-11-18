-- Assign ACCESSTR + ACCESSTS to every active user in department TENANTRELATION (PGSQL2). Idempotent.
-- A user counts when ms_user.department_id (primary) OR an active ms_user_dept row is TENANTRELATION.
INSERT INTO sys_user_role (username, role_id, status, created_by, created_at)
SELECT u.username, r.role_id, 'A', 'system', now()
FROM ms_user u
CROSS JOIN (VALUES ('ACCESSTR'), ('ACCESSTS')) AS r(role_id)
WHERE u.status = 'A'
  AND (u.department_id = 'TENANTRELATION'
       OR EXISTS (SELECT 1 FROM ms_user_dept d WHERE d.username = u.username AND d.department_id = 'TENANTRELATION' AND d.status = 'A' AND d.deleted_at IS NULL))
  AND NOT EXISTS (SELECT 1 FROM sys_user_role x WHERE x.username = u.username AND x.role_id = r.role_id);
