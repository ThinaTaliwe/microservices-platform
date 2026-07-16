-- IAM v2 access catalogue relationships
-- MySQL 8+
-- Idempotent.
--
-- Systems, modules and components are created by schema 002.
-- This table associates functional components with RBAC permissions.

CREATE TABLE IF NOT EXISTS access_component_permissions (
    component_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        component_id,
        permission_id
    ),

    KEY idx_access_component_permissions_permission (
        permission_id,
        component_id
    ),

    CONSTRAINT fk_access_component_permissions_component
        FOREIGN KEY (component_id)
        REFERENCES access_components (id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_access_component_permissions_permission
        FOREIGN KEY (permission_id)
        REFERENCES permissions (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
