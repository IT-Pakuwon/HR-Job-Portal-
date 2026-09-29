-- =====================================================================
-- PROADMINACCESS — module-wide "see everything" role for Project
-- Management. Holders bypass the usual per-Team membership scoping in
-- PmProjectController/PmTaskController/PmTaskDetailController and see
-- every active Team and Project, not just ones they're a member of.
-- Run against the PGSQL2 connection (sys_role lives there).
-- =====================================================================

INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT 'PROADMINACCESS', 'Project Admin Access (see all Teams/Projects)', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'PROADMINACCESS');
