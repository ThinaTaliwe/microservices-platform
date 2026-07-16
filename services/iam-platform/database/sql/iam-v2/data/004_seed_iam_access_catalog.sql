-- IAM Platform system/module/component catalogue
-- Idempotent reference-data deployment.

START TRANSACTION;

INSERT INTO access_systems (
    external_key,
    name,
    slug,
    status,
    created_at,
    updated_at
)
VALUES (
    'iam-platform',
    'IAM Platform',
    'iam',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO access_modules (
    system_id,
    external_key,
    name,
    slug,
    status,
    display_order,
    created_at,
    updated_at
)
SELECT
    systems.id,
    catalogue.external_key,
    catalogue.name,
    catalogue.slug,
    'active',
    catalogue.display_order,
    NOW(),
    NOW()
FROM access_systems AS systems
CROSS JOIN (
    SELECT
        'iam-security' AS external_key,
        'Security' AS name,
        'security' AS slug,
        10 AS display_order

    UNION ALL

    SELECT
        'iam-access-management',
        'Access Management',
        'access-management',
        20
) AS catalogue
WHERE systems.slug = 'iam'
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    status = VALUES(status),
    display_order = VALUES(display_order),
    updated_at = VALUES(updated_at);

INSERT INTO access_components (
    module_id,
    external_key,
    name,
    slug,
    status,
    display_order,
    created_at,
    updated_at
)
SELECT
    modules.id,
    catalogue.external_key,
    catalogue.name,
    catalogue.slug,
    'active',
    catalogue.display_order,
    NOW(),
    NOW()
FROM access_modules AS modules
INNER JOIN access_systems AS systems
    ON systems.id = modules.system_id
INNER JOIN (
    SELECT
        'security' AS module_slug,
        'iam-sessions' AS external_key,
        'Sessions' AS name,
        'sessions' AS slug,
        10 AS display_order

    UNION ALL
    SELECT
        'security',
        'iam-devices',
        'Trusted Devices',
        'devices',
        20

    UNION ALL
    SELECT
        'security',
        'iam-audit',
        'Security Audit',
        'audit',
        30

    UNION ALL
    SELECT
        'access-management',
        'iam-approvals',
        'Supervisor Approvals',
        'approvals',
        10

    UNION ALL
    SELECT
        'access-management',
        'iam-identities',
        'Identities',
        'identities',
        20

    UNION ALL
    SELECT
        'access-management',
        'iam-roles',
        'Roles',
        'roles',
        30

    UNION ALL
    SELECT
        'access-management',
        'iam-permissions',
        'Permissions',
        'permissions',
        40

    UNION ALL
    SELECT
        'access-management',
        'iam-contexts',
        'Access Contexts',
        'contexts',
        50
) AS catalogue
    ON catalogue.module_slug = modules.slug
WHERE systems.slug = 'iam'
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    status = VALUES(status),
    display_order = VALUES(display_order),
    updated_at = VALUES(updated_at);

INSERT IGNORE INTO access_component_permissions (
    component_id,
    permission_id
)
SELECT
    components.id,
    permissions.id
FROM access_components AS components
INNER JOIN access_modules AS modules
    ON modules.id = components.module_id
INNER JOIN access_systems AS systems
    ON systems.id = modules.system_id
INNER JOIN permissions
    ON permissions.guard_name = 'web'
INNER JOIN (
    SELECT
        'sessions' AS component_slug,
        'iam.sessions.read' AS permission_name

    UNION ALL
    SELECT 'sessions', 'iam.sessions.revoke'

    UNION ALL
    SELECT 'devices', 'iam.devices.read'

    UNION ALL
    SELECT 'devices', 'iam.devices.remove-trust'

    UNION ALL
    SELECT 'audit', 'iam.audit.read'

    UNION ALL
    SELECT 'approvals', 'iam.approvals.read'

    UNION ALL
    SELECT 'approvals', 'iam.approvals.approve'

    UNION ALL
    SELECT 'approvals', 'iam.approvals.block'

    UNION ALL
    SELECT 'identities', 'iam.identities.read'

    UNION ALL
    SELECT 'identities', 'iam.identities.manage'

    UNION ALL
    SELECT 'roles', 'iam.roles.read'

    UNION ALL
    SELECT 'roles', 'iam.roles.manage'

    UNION ALL
    SELECT 'permissions', 'iam.permissions.read'

    UNION ALL
    SELECT 'permissions', 'iam.permissions.manage'

    UNION ALL
    SELECT 'contexts', 'iam.context.read'

    UNION ALL
    SELECT 'contexts', 'iam.context.manage'
) AS mapping
    ON mapping.component_slug = components.slug
   AND mapping.permission_name = permissions.name
WHERE systems.slug = 'iam';

COMMIT;
