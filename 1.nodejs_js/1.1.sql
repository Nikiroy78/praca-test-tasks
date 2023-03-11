SELECT id
FROM departments
WHERE NOT EXISTS (
	SELECT * FROM dep_names WHERE dep_names.id = departments.id
);