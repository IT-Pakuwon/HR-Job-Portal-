-- =====================================================================
-- Retire ORGPROJECTACCESS. The Project module no longer checks it:
-- Projects pages are browsed via PROJECTACCESS / PROADMINACCESS, and the
-- legacy Groups page (PmGroupController) is now managed by admin only.
-- Deactivated (status 'X') rather than deleted, same as everywhere else.
-- Run against the PGSQL2 connection (sys_role / sys_role_menu /
-- sys_user_role live there).
-- =====================================================================

UPDATE sys_user_role SET status = 'X' WHERE role_id = 'ORGPROJECTACCESS' AND status = 'A';
UPDATE sys_role_menu SET status = 'X' WHERE role_id = 'ORGPROJECTACCESS' AND status = 'A';
UPDATE sys_role      SET status = 'X' WHERE role_id = 'ORGPROJECTACCESS' AND status = 'A';
