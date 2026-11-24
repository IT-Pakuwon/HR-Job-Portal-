-- =====================================================================
-- DASHOM — "Dashboard OM" multidashboard homepage option.
-- Run against the PGSQL2 connection (sys_* tables). Idempotent.
-- Appears in Users > "Homepage Dashboard" (screens of application DASHBOARD
-- that MultiDashboardController::DASHBOARD_COMPONENTS knows about).
-- =====================================================================
INSERT INTO sys_screen (screen_id, screen_name, application_id, status, created_by, created_at)
SELECT 'DASHOM', 'Dashboard OM', 'DASHBOARD', 'A', 'system', now()
WHERE NOT EXISTS (
    SELECT 1 FROM sys_screen WHERE screen_id = 'DASHOM' AND application_id = 'DASHBOARD'
);
