SELECT
    systems.slug AS system_slug,
    systems.name AS system_name,
    modules.slug AS module_slug,
    modules.name AS module_name,
    modules.display_order AS module_order,
    components.slug AS component_slug,
    components.name AS component_name,
    components.display_order AS component_order
FROM access_systems AS systems
INNER JOIN access_modules AS modules
    ON modules.system_id = systems.id
INNER JOIN access_components AS components
    ON components.module_id = modules.id
WHERE systems.slug = 'iam'
ORDER BY
    modules.display_order,
    components.display_order;

SELECT
    components.slug AS component_slug,
    permissions.name AS permission_name
FROM access_component_permissions AS mapping
INNER JOIN access_components AS components
    ON components.id = mapping.component_id
INNER JOIN access_modules AS modules
    ON modules.id = components.module_id
INNER JOIN access_systems AS systems
    ON systems.id = modules.system_id
INNER JOIN permissions
    ON permissions.id = mapping.permission_id
WHERE systems.slug = 'iam'
ORDER BY
    components.slug,
    permissions.name;

SELECT
    COUNT(DISTINCT systems.id) AS system_count,
    COUNT(DISTINCT modules.id) AS module_count,
    COUNT(DISTINCT components.id) AS component_count,
    COUNT(mapping.permission_id) AS permission_mapping_count
FROM access_systems AS systems
LEFT JOIN access_modules AS modules
    ON modules.system_id = systems.id
LEFT JOIN access_components AS components
    ON components.module_id = modules.id
LEFT JOIN access_component_permissions AS mapping
    ON mapping.component_id = components.id
WHERE systems.slug = 'iam';
