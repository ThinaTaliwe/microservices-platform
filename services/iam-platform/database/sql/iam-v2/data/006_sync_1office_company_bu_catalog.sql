-- IAM v2 synchronization from 1office_0_2
-- Idempotent.

START TRANSACTION;

INSERT INTO access_companies (
external_key,
name,
slug,
status,
created_at,
updated_at
)
VALUES
('1office-company:1', 'Nasaco Southern Africa', '1office-company-1', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:2', 'Betachem', '1office-company-2', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:3', 'Betachem Agencies', '1office-company-3', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:4', 'BFR Properties', '1office-company-4', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:5', 'Nasaco Cargo Services', '1office-company-5', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:6', 'Zero paper', '1office-company-6', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:7', 'GBA', '1office-company-7', 'active', '2023-08-01 00:00:01', '2023-08-01 00:00:01'),
('1office-company:8', 'SYSTEM - 1 Office', '1office-company-8', 'active', '2021-10-31 05:19:18', '2021-10-31 05:19:18')

ON DUPLICATE KEY UPDATE
name = VALUES(name),
slug = VALUES(slug),
status = VALUES(status),
updated_at = VALUES(updated_at);

CREATE TEMPORARY TABLE iam_v2_source_business_units (
company_external_key VARCHAR(100)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL,
external_key VARCHAR(100)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL,
name VARCHAR(191)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL,
slug VARCHAR(191)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL,
status VARCHAR(30)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL,
source_short_code VARCHAR(20)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NULL,
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL,
PRIMARY KEY (external_key)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

INSERT INTO iam_v2_source_business_units (
company_external_key,
external_key,
name,
slug,
status,
source_short_code,
created_at,
updated_at
)
VALUES

('1office-company:1', '1office-bu:1', 'Nasaco Southern Africa', '1office-bu-1', 'active', 'NSA', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:2', '1office-bu:2', 'Betachem', '1office-bu-2', 'active', 'BC', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:3', '1office-bu:3', 'Betachem Agencies', '1office-bu-3', 'active', 'BA', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:5', '1office-bu:4', 'Nasaco Cargo Services', '1office-bu-4', 'active', 'NCS', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:4', '1office-bu:5', 'BFR Properties', '1office-bu-5', 'active', 'BFR', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:6', '1office-bu:6', 'Zero Paper', '1office-bu-6', 'active', 'ZP', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:7', '1office-bu:7', 'Group Burial Association', '1office-bu-7', 'active', 'GBA', '2024-01-16 00:00:00', '2024-01-16 00:00:00'),
('1office-company:8', '1office-bu:8', 'SYSTEM - 1 Office', '1office-bu-8', 'active', 'SYSTE', '2021-10-31 05:19:18', '2021-10-31 05:19:18'),
('1office-company:1', '1office-bu:9', 'Test Business Unit', '1office-bu-9', 'active', 'TBU', NULL, CURRENT_TIMESTAMP)

;

INSERT INTO access_business_units (
company_id,
external_key,
name,
slug,
status,
created_at,
updated_at
)
SELECT
company.id,
source.external_key,
source.name,
source.slug,
source.status,
source.created_at,
source.updated_at
FROM iam_v2_source_business_units AS source
INNER JOIN access_companies AS company
ON company.external_key = source.company_external_key
ON DUPLICATE KEY UPDATE
company_id = VALUES(company_id),
name = VALUES(name),
slug = VALUES(slug),
status = VALUES(status),
updated_at = VALUES(updated_at);

DROP TEMPORARY TABLE iam_v2_source_business_units;

COMMIT;
