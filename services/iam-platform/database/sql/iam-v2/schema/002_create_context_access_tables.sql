-- IAM v2 contextual authorization foundation
-- MySQL 8+
-- No modification of existing authentication tables.

CREATE TABLE IF NOT EXISTS access_companies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    external_key VARCHAR(100) NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_access_companies_slug (slug),
    UNIQUE KEY uq_access_companies_external_key (external_key),
    KEY idx_access_companies_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_business_units (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    external_key VARCHAR(100) NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_access_bu_company_slug (
        company_id,
        slug
    ),
    UNIQUE KEY uq_access_bu_external_key (external_key),
    KEY idx_access_bu_company_status (
        company_id,
        status
    ),

    CONSTRAINT fk_access_bu_company
        FOREIGN KEY (company_id)
        REFERENCES access_companies (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_systems (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    external_key VARCHAR(100) NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_access_systems_slug (slug),
    UNIQUE KEY uq_access_systems_external_key (external_key),
    KEY idx_access_systems_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_modules (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    system_id BIGINT UNSIGNED NOT NULL,
    external_key VARCHAR(100) NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_access_modules_system_slug (
        system_id,
        slug
    ),
    UNIQUE KEY uq_access_modules_external_key (external_key),
    KEY idx_access_modules_system_status_order (
        system_id,
        status,
        display_order
    ),

    CONSTRAINT fk_access_modules_system
        FOREIGN KEY (system_id)
        REFERENCES access_systems (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_components (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    external_key VARCHAR(100) NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_access_components_module_slug (
        module_id,
        slug
    ),
    UNIQUE KEY uq_access_components_external_key (external_key),
    KEY idx_access_components_module_status_order (
        module_id,
        status,
        display_order
    ),

    CONSTRAINT fk_access_components_module
        FOREIGN KEY (module_id)
        REFERENCES access_modules (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_identity_companies (
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    valid_from TIMESTAMP NULL DEFAULT NULL,
    valid_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (
        auth_identity_id,
        company_id
    ),
    KEY idx_identity_companies_company_status (
        company_id,
        status
    ),
    KEY idx_identity_companies_validity (
        auth_identity_id,
        status,
        valid_from,
        valid_until
    ),

    CONSTRAINT fk_identity_companies_identity
        FOREIGN KEY (auth_identity_id)
        REFERENCES auth_identities (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_identity_companies_company
        FOREIGN KEY (company_id)
        REFERENCES access_companies (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_identity_business_units (
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    business_unit_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    valid_from TIMESTAMP NULL DEFAULT NULL,
    valid_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (
        auth_identity_id,
        business_unit_id
    ),
    KEY idx_identity_bu_bu_status (
        business_unit_id,
        status
    ),
    KEY idx_identity_bu_validity (
        auth_identity_id,
        status,
        valid_from,
        valid_until
    ),

    CONSTRAINT fk_identity_bu_identity
        FOREIGN KEY (auth_identity_id)
        REFERENCES auth_identities (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_identity_bu_business_unit
        FOREIGN KEY (business_unit_id)
        REFERENCES access_business_units (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_identity_systems (
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    system_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    valid_from TIMESTAMP NULL DEFAULT NULL,
    valid_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (
        auth_identity_id,
        system_id
    ),
    KEY idx_identity_systems_system_status (
        system_id,
        status
    ),
    KEY idx_identity_systems_validity (
        auth_identity_id,
        status,
        valid_from,
        valid_until
    ),

    CONSTRAINT fk_identity_systems_identity
        FOREIGN KEY (auth_identity_id)
        REFERENCES auth_identities (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_identity_systems_system
        FOREIGN KEY (system_id)
        REFERENCES access_systems (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_role_contexts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NULL,
    business_unit_id BIGINT UNSIGNED NULL,
    system_id BIGINT UNSIGNED NULL,

    company_scope_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (COALESCE(company_id, 0)) STORED,
    business_unit_scope_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (COALESCE(business_unit_id, 0)) STORED,
    system_scope_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (COALESCE(system_id, 0)) STORED,

    status VARCHAR(30) NOT NULL DEFAULT 'active',
    valid_from TIMESTAMP NULL DEFAULT NULL,
    valid_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_access_role_context (
        auth_identity_id,
        role_id,
        company_scope_id,
        business_unit_scope_id,
        system_scope_id
    ),

    KEY idx_access_role_context_lookup (
        auth_identity_id,
        company_id,
        business_unit_id,
        system_id,
        status
    ),

    KEY idx_access_role_context_role (
        role_id,
        status
    ),

    KEY idx_access_role_context_company (
        company_id
    ),

    KEY idx_access_role_context_bu (
        business_unit_id
    ),

    KEY idx_access_role_context_system (
        system_id
    ),

    CONSTRAINT fk_access_role_context_identity
        FOREIGN KEY (auth_identity_id)
        REFERENCES auth_identities (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_access_role_context_role
        FOREIGN KEY (role_id)
        REFERENCES roles (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_access_role_context_company
        FOREIGN KEY (company_id)
        REFERENCES access_companies (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_access_role_context_bu
        FOREIGN KEY (business_unit_id)
        REFERENCES access_business_units (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_access_role_context_system
        FOREIGN KEY (system_id)
        REFERENCES access_systems (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_component_overrides (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    business_unit_id BIGINT UNSIGNED NOT NULL,
    system_id BIGINT UNSIGNED NOT NULL,
    component_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NULL,

    permission_scope_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (COALESCE(permission_id, 0)) STORED,

    effect ENUM('allow', 'deny') NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    reason VARCHAR(255) NULL,
    valid_from TIMESTAMP NULL DEFAULT NULL,
    valid_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_component_override (
        auth_identity_id,
        company_id,
        business_unit_id,
        system_id,
        component_id,
        permission_scope_id
    ),

    KEY idx_component_override_decision (
        auth_identity_id,
        company_id,
        business_unit_id,
        system_id,
        component_id,
        status,
        effect
    ),

    CONSTRAINT fk_component_override_identity
        FOREIGN KEY (auth_identity_id)
        REFERENCES auth_identities (id)
        ON DELETE CASCADE,

    CONSTRAINT fk_component_override_company
        FOREIGN KEY (company_id)
        REFERENCES access_companies (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_component_override_bu
        FOREIGN KEY (business_unit_id)
        REFERENCES access_business_units (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_component_override_system
        FOREIGN KEY (system_id)
        REFERENCES access_systems (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_component_override_component
        FOREIGN KEY (component_id)
        REFERENCES access_components (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_component_override_permission
        FOREIGN KEY (permission_id)
        REFERENCES permissions (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
