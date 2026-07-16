-- IAM v2 access repository execution-plan validation.
-- Read-only.

EXPLAIN ANALYZE
SELECT EXISTS (
    SELECT 1
    FROM access_identity_companies
    WHERE auth_identity_id = 1
      AND company_id = 1
      AND status = 'active'
      AND (
          valid_from IS NULL
          OR valid_from <= NOW()
      )
      AND (
          valid_until IS NULL
          OR valid_until >= NOW()
      )
) AS company_access;

EXPLAIN ANALYZE
SELECT EXISTS (
    SELECT 1
    FROM access_identity_business_units AS aibu
    INNER JOIN access_business_units AS bu
        ON bu.id = aibu.business_unit_id
    WHERE aibu.auth_identity_id = 1
      AND aibu.business_unit_id = 1
      AND bu.company_id = 1
      AND aibu.status = 'active'
      AND bu.status = 'active'
      AND (
          aibu.valid_from IS NULL
          OR aibu.valid_from <= NOW()
      )
      AND (
          aibu.valid_until IS NULL
          OR aibu.valid_until >= NOW()
      )
) AS business_unit_access;

EXPLAIN ANALYZE
SELECT EXISTS (
    SELECT 1
    FROM access_identity_systems AS ais
    INNER JOIN access_systems AS systems
        ON systems.id = ais.system_id
    WHERE ais.auth_identity_id = 1
      AND ais.system_id = 1
      AND ais.status = 'active'
      AND systems.status = 'active'
      AND (
          ais.valid_from IS NULL
          OR ais.valid_from <= NOW()
      )
      AND (
          ais.valid_until IS NULL
          OR ais.valid_until >= NOW()
      )
) AS system_access;

EXPLAIN ANALYZE
SELECT role_id
FROM access_role_contexts
WHERE auth_identity_id = 1
  AND status = 'active'
  AND (
      company_id IS NULL
      OR company_id = 1
  )
  AND (
      business_unit_id IS NULL
      OR business_unit_id = 1
  )
  AND (
      system_id IS NULL
      OR system_id = 1
  )
  AND (
      valid_from IS NULL
      OR valid_from <= NOW()
  )
  AND (
      valid_until IS NULL
      OR valid_until >= NOW()
  );

EXPLAIN ANALYZE
SELECT p.name
FROM role_has_permissions AS rhp
INNER JOIN permissions AS p
    ON p.id = rhp.permission_id
WHERE rhp.role_id IN (
    SELECT role_id
    FROM access_role_contexts
    WHERE auth_identity_id = 1
      AND status = 'active'
      AND (
          company_id IS NULL
          OR company_id = 1
      )
      AND (
          business_unit_id IS NULL
          OR business_unit_id = 1
      )
      AND (
          system_id IS NULL
          OR system_id = 1
      )
)
AND p.guard_name = 'web';

EXPLAIN ANALYZE
SELECT
    p.name AS permission_name,
    overrides.effect
FROM access_component_overrides AS overrides
LEFT JOIN permissions AS p
    ON p.id = overrides.permission_id
WHERE overrides.auth_identity_id = 1
  AND overrides.company_id = 1
  AND overrides.business_unit_id = 1
  AND overrides.system_id = 1
  AND overrides.component_id = 1
  AND overrides.status = 'active'
  AND (
      overrides.valid_from IS NULL
      OR overrides.valid_from <= NOW()
  )
  AND (
      overrides.valid_until IS NULL
      OR overrides.valid_until >= NOW()
  );
