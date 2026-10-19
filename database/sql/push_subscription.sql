-- =====================================================================
-- Web Push subscriptions (My Page app notifications)
-- Run against the PGSQL2 connection (same as sys_menu_favourite).
-- One row per user per device/browser (endpoint is unique).
-- =====================================================================
CREATE TABLE IF NOT EXISTS sys_push_subscription (
    id          BIGSERIAL PRIMARY KEY,
    username    VARCHAR(100) NOT NULL,
    endpoint    TEXT         NOT NULL,
    p256dh      TEXT         NOT NULL,
    auth        TEXT         NOT NULL,
    origin      VARCHAR(255) NULL,          -- site origin the device subscribed from; push links are built from it
    user_agent  VARCHAR(255) NULL,
    status      CHAR(1)      NOT NULL DEFAULT 'A',
    created_at  TIMESTAMP    NULL,
    updated_at  TIMESTAMP    NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS ux_sys_push_subscription_endpoint ON sys_push_subscription (md5(endpoint));
CREATE INDEX IF NOT EXISTS ix_sys_push_subscription_username ON sys_push_subscription (username, status);
