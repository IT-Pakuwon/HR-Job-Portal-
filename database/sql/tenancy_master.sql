-- =====================================================================
-- Tenancy Support — Master data: Location, Floor, Tenant, User Tenant.
-- Runs against the `mysql5` connection (dbtenancytest, DB_*_5 env vars),
-- NOT the app's primary pgsql database.
-- Hierarchy: Location -> Floor -> Tenant -> User Tenant.
-- Soft delete convention: status ('A' active / 'X' inactive), no hard delete.
-- See TsLocationController / TsFloorController / TsTenantController / TsUserTenantController.
-- =====================================================================

CREATE TABLE IF NOT EXISTS ms_location (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_code VARCHAR(50) NOT NULL,
    location_name VARCHAR(200) NOT NULL,
    address VARCHAR(255) NULL,
    status CHAR(1) NOT NULL DEFAULT 'A',
    created_by VARCHAR(100) NULL,
    created_at DATETIME NULL,
    updated_by VARCHAR(100) NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_ms_location_code (location_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ms_floor (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    floor_code VARCHAR(50) NOT NULL,
    floor_name VARCHAR(150) NOT NULL,
    status CHAR(1) NOT NULL DEFAULT 'A',
    created_by VARCHAR(100) NULL,
    created_at DATETIME NULL,
    updated_by VARCHAR(100) NULL,
    updated_at DATETIME NULL,
    KEY idx_ms_floor_location (location_id),
    CONSTRAINT fk_ms_floor_location FOREIGN KEY (location_id) REFERENCES ms_location (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ms_tenant (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    floor_id INT UNSIGNED NOT NULL,
    tenant_code VARCHAR(50) NOT NULL,
    tenant_name VARCHAR(200) NOT NULL,
    unit_no VARCHAR(50) NULL,
    status CHAR(1) NOT NULL DEFAULT 'A',
    created_by VARCHAR(100) NULL,
    created_at DATETIME NULL,
    updated_by VARCHAR(100) NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uq_ms_tenant_code (tenant_code),
    KEY idx_ms_tenant_floor (floor_id),
    CONSTRAINT fk_ms_tenant_floor FOREIGN KEY (floor_id) REFERENCES ms_floor (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ms_user_tenant (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    user_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    position VARCHAR(100) NULL,
    status CHAR(1) NOT NULL DEFAULT 'A',
    created_by VARCHAR(100) NULL,
    created_at DATETIME NULL,
    updated_by VARCHAR(100) NULL,
    updated_at DATETIME NULL,
    KEY idx_ms_user_tenant_tenant (tenant_id),
    CONSTRAINT fk_ms_user_tenant_tenant FOREIGN KEY (tenant_id) REFERENCES ms_tenant (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
