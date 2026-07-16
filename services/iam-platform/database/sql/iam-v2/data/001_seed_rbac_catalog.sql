-- IAM v2 role and permission catalogue
-- Idempotent reference-data load.

START TRANSACTION;

INSERT INTO permissions (
    name,
    guard_name,
    created_at,
    updated_at
)
VALUES
    ('iam.sessions.read', 'web', NOW(), NOW()),
    ('iam.sessions.revoke', 'web', NOW(), NOW()),
    ('iam.devices.read', 'web', NOW(), NOW()),
    ('iam.devices.remove-trust', 'web', NOW(), NOW()),
    ('iam.audit.read', 'web', NOW(), NOW()),
    ('iam.approvals.read', 'web', NOW(), NOW()),
    ('iam.approvals.approve', 'web', NOW(), NOW()),
    ('iam.approvals.block', 'web', NOW(), NOW()),
    ('iam.identities.read', 'web', NOW(), NOW()),
    ('iam.identities.manage', 'web', NOW(), NOW()),
    ('iam.roles.read', 'web', NOW(), NOW()),
    ('iam.roles.manage', 'web', NOW(), NOW()),
    ('iam.permissions.read', 'web', NOW(), NOW()),
    ('iam.permissions.manage', 'web', NOW(), NOW()),
    ('iam.context.read', 'web', NOW(), NOW()),
    ('iam.context.manage', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    updated_at = VALUES(updated_at);

INSERT INTO roles (
    name,
    guard_name,
    created_at,
    updated_at
)
VALUES
    ('platform-super-admin', 'web', NOW(), NOW()),
    ('system-owner', 'web', NOW(), NOW()),
    ('company-admin', 'web', NOW(), NOW()),
    ('bu-admin', 'web', NOW(), NOW()),
    ('system-admin', 'web', NOW(), NOW()),
    ('supervisor', 'web', NOW(), NOW()),
    ('operations-manager', 'web', NOW(), NOW()),
    ('operations-user', 'web', NOW(), NOW()),
    ('client-user', 'web', NOW(), NOW()),
    ('auditor', 'web', NOW(), NOW()),
    ('read-only', 'web', NOW(), NOW()),
    ('api-user', 'web', NOW(), NOW()),
    ('service-account', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    updated_at = VALUES(updated_at);

-- Full IAM permission set for platform super administrators.
INSERT IGNORE INTO role_has_permissions (
    permission_id,
    role_id
)
SELECT
    p.id,
    r.id
FROM permissions AS p
JOIN roles AS r
    ON r.name = 'platform-super-admin'
   AND r.guard_name = 'web'
WHERE p.guard_name = 'web';

-- Full IAM permission set for system owners.
INSERT IGNORE INTO role_has_permissions (
    permission_id,
    role_id
)
SELECT
    p.id,
    r.id
FROM permissions AS p
JOIN roles AS r
    ON r.name = 'system-owner'
   AND r.guard_name = 'web'
WHERE p.guard_name = 'web';

-- Supervisor permissions.
INSERT IGNORE INTO role_has_permissions (
    permission_id,
    role_id
)
SELECT
    p.id,
    r.id
FROM permissions AS p
JOIN roles AS r
    ON r.name = 'supervisor'
   AND r.guard_name = 'web'
WHERE p.guard_name = 'web'
  AND p.name IN (
      'iam.sessions.read',
      'iam.devices.read',
      'iam.audit.read',
      'iam.approvals.read',
      'iam.approvals.approve',
      'iam.approvals.block',
      'iam.identities.read'
  );

-- Auditor permissions.
INSERT IGNORE INTO role_has_permissions (
    permission_id,
    role_id
)
SELECT
    p.id,
    r.id
FROM permissions AS p
JOIN roles AS r
    ON r.name = 'auditor'
   AND r.guard_name = 'web'
WHERE p.guard_name = 'web'
  AND p.name IN (
      'iam.sessions.read',
      'iam.devices.read',
      'iam.audit.read',
      'iam.approvals.read',
      'iam.identities.read',
      'iam.roles.read',
      'iam.permissions.read',
      'iam.context.read'
  );

-- Read-only permissions.
INSERT IGNORE INTO role_has_permissions (
    permission_id,
    role_id
)
SELECT
    p.id,
    r.id
FROM permissions AS p
JOIN roles AS r
    ON r.name = 'read-only'
   AND r.guard_name = 'web'
WHERE p.guard_name = 'web'
  AND p.name IN (
      'iam.sessions.read',
      'iam.devices.read',
      'iam.identities.read',
      'iam.context.read'
  );

-- client-user intentionally receives no global IAM permissions.

COMMIT;
