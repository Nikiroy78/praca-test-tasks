SELECT id
FROM departments
WHERE (
	SELECT count(name_tsvector) FROM dep_names WHERE dep_names.id = departments.id AND dep_names.name_tsvector != NULL
) >= 2;