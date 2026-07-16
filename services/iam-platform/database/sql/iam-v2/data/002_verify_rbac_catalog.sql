SELECT
    COUNT(*) AS permission_count
FROM permissions
WHERE guard_name = 'web';

SELECT
    COUNT(*) AS role_count
FROM roles
WHERE guard_name = 'web';

SELECT
    r.name AS role_name,
    COUNT(rhp.permission_id) AS permission_count
FROM roles AS r
LEFT JOIN role_has_permissions AS rhp
    ON rhp.role_id = r.id
WHERE r.guard_name = 'web'
GROUP BY
    r.id,
    r.name
ORDER BY
    r.name;

SELECT
    r.name AS role_name,
    p.name AS permission_name
FROM role_has_permissions AS rhp
JOIN roles AS r
    ON r.id = rhp.role_id
JOIN permissions AS p
    ON p.id = rhp.permission_id
WHERE r.guard_name = 'web'
  AND p.guard_name = 'web'
ORDER BY
    r.name,
    p.name;
