-- IAM Verification Challenge extension
--
-- Adds support for a second, API-specific verification credential while
-- preserving the existing browser access token and numeric email OTP.
--
-- Safe to execute more than once.

DELIMITER $$

DROP PROCEDURE IF EXISTS iam_add_column_if_missing$$

CREATE PROCEDURE iam_add_column_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_column_name VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @iam_sql = CONCAT(
            'ALTER TABLE `',
            REPLACE(p_table_name, '`', '``'),
            '` ADD COLUMN `',
            REPLACE(p_column_name, '`', '``'),
            '` ',
            p_definition
        );

        PREPARE iam_statement FROM @iam_sql;
        EXECUTE iam_statement;
        DEALLOCATE PREPARE iam_statement;
    END IF;
END$$

DROP PROCEDURE IF EXISTS iam_add_index_if_missing$$

CREATE PROCEDURE iam_add_index_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_definition TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND INDEX_NAME = p_index_name
    ) THEN
        SET @iam_sql = CONCAT(
            'ALTER TABLE `',
            REPLACE(p_table_name, '`', '``'),
            '` ADD ',
            p_definition
        );

        PREPARE iam_statement FROM @iam_sql;
        EXECUTE iam_statement;
        DEALLOCATE PREPARE iam_statement;
    END IF;
END$$

DELIMITER ;

CALL iam_add_column_if_missing(
    'auth_otp_challenges',
    'challenge_uuid',
    'CHAR(36) NULL AFTER `id`'
);

CALL iam_add_column_if_missing(
    'auth_otp_challenges',
    'api_token_hash',
    'CHAR(64) NULL AFTER `access_token_hash`'
);

CALL iam_add_column_if_missing(
    'auth_otp_challenges',
    'verified_via',
    'VARCHAR(20) NULL AFTER `verified_at`'
);

CALL iam_add_column_if_missing(
    'auth_otp_challenges',
    'revoked_at',
    'TIMESTAMP NULL AFTER `consumed_at`'
);

-- Existing challenges need public identifiers before the column becomes
-- mandatory. UUID() is generated independently for every updated row.
UPDATE auth_otp_challenges
SET challenge_uuid = UUID()
WHERE challenge_uuid IS NULL
   OR challenge_uuid = '';

ALTER TABLE auth_otp_challenges
    MODIFY COLUMN challenge_uuid CHAR(36) NOT NULL;

CALL iam_add_index_if_missing(
    'auth_otp_challenges',
    'uq_auth_otp_challenge_uuid',
    'UNIQUE KEY `uq_auth_otp_challenge_uuid` (`challenge_uuid`)'
);

CALL iam_add_index_if_missing(
    'auth_otp_challenges',
    'uq_auth_otp_api_token',
    'UNIQUE KEY `uq_auth_otp_api_token` (`api_token_hash`)'
);

DROP PROCEDURE IF EXISTS iam_add_column_if_missing;
DROP PROCEDURE IF EXISTS iam_add_index_if_missing;
