-- IAM v2 contextual role-assignment query indexes
-- MySQL 8+
-- Idempotent.

SET @index_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'access_role_contexts'
      AND INDEX_NAME = 'idx_role_context_admin_query'
);

SET @statement = IF(
    @index_exists = 0,
    'CREATE INDEX idx_role_context_admin_query
        ON access_role_contexts (
            auth_identity_id,
            company_scope_id,
            system_scope_id,
            status,
            id
        )',
    'SELECT ''idx_role_context_admin_query already exists'' AS result'
);

PREPARE iam_v2_statement FROM @statement;
EXECUTE iam_v2_statement;
DEALLOCATE PREPARE iam_v2_statement;


SET @bu_index_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'access_role_contexts'
      AND INDEX_NAME = 'idx_role_context_admin_bu_query'
);

SET @bu_statement = IF(
    @bu_index_exists = 0,
    'CREATE INDEX idx_role_context_admin_bu_query
        ON access_role_contexts (
            auth_identity_id,
            company_scope_id,
            business_unit_scope_id,
            system_scope_id,
            status,
            id
        )',
    'SELECT ''idx_role_context_admin_bu_query already exists'' AS result'
);

PREPARE iam_v2_bu_statement FROM @bu_statement;
EXECUTE iam_v2_bu_statement;
DEALLOCATE PREPARE iam_v2_bu_statement;
