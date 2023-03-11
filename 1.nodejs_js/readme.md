# Блок заданий "Задание, Java Script, Node JS:" (1)
1. Есть таблицы (PgSql, но диалект в данном случае не важен, пишите как на том который знаете):
```pgsql
CREATE TABLE public.departments (
    id integer NOT NULL DEFAULT nextval('departments_id_seq'::regclass),
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    file_id character varying(32) COLLATE pg_catalog."default",
    CONSTRAINT departments_pkey PRIMARY KEY (id)
)
CREATE TABLE public.dep_names (
    id integer NOT NULL DEFAULT nextval('dep_names_id_seq'::regclass),
    name character varying(1024) COLLATE pg_catalog."default" NOT NULL,
    department_id integer,
    name_tsvector tsvector,
    CONSTRAINT dep_names_pkey PRIMARY KEY (id)
)
# dep_names .department_id (многие) ссылается на departments.id (к одному)
```  
Все запросы будут написаны на языке запросов SQL.  
  
1.1. Запрос (SELECT) для построения списка departments.id, для которых нет связанных названий (строк в dep_names).  
```sql
SELECT id
FROM departments
WHERE NOT EXISTS (
	SELECT * FROM dep_names WHERE dep_names.department_id = departments.id
);
```
1.2. Запрос (SELECT) для построения списка departments.id, для которых есть 2 и более названий.
```sql
SELECT id
FROM departments
WHERE (
	SELECT count(name_tsvector) FROM dep_names WHERE dep_names.id = departments.id AND dep_names.name_tsvector != NULL
) >= 2;
```
1.3. Запрос (SELECT) для построения списка departments.\*, для каждого указать только 1 название (даже если их несколько) с минимальным dep_names.id.