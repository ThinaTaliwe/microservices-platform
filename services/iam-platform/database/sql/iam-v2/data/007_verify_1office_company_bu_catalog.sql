SELECT
    COUNT(*) AS imported_company_count
FROM access_companies
WHERE external_key LIKE '1office-company:%';

SELECT
    COUNT(*) AS imported_business_unit_count
FROM access_business_units
WHERE external_key LIKE '1office-bu:%';

SELECT
    COUNT(*) AS orphan_business_unit_count
FROM access_business_units AS bu
LEFT JOIN access_companies AS company
    ON company.id = bu.company_id
WHERE bu.external_key LIKE '1office-bu:%'
  AND company.id IS NULL;

SELECT
    company.external_key AS company_external_key,
    company.name AS company_name,
    bu.external_key AS business_unit_external_key,
    bu.name AS business_unit_name,
    company.status AS company_status,
    bu.status AS business_unit_status
FROM access_business_units AS bu
INNER JOIN access_companies AS company
    ON company.id = bu.company_id
WHERE bu.external_key LIKE '1office-bu:%'
ORDER BY
    company.external_key,
    bu.external_key;
