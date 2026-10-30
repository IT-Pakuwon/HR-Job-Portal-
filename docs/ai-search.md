# AI Search

Open `/ai-search` after logging in. The source selector lists active PostgreSQL
sources with at least one table permitted for the current USER or ROLE.
No permission is created automatically.

## Catalog and authorization

- Sources must have `is_active = true`.
- Tables must have `is_active = true` and `is_queryable = true`.
- Selected/filter/group/order columns must have `is_queryable = true` and
  `is_sensitive = false`.
- Source READ covers its tables; table/module READ can narrow access.
- Applicable DENY takes precedence. Company/department-scoped permissions fail
  closed until row filtering is implemented.
- Excluded tables in `config/ai-search.php` do not enter the searchable catalog.

Schema and table names come from the authorized source catalog, not a fixed PRF
table. New catalog tables become searchable when active and permitted.

## Supported searches

Choose the intended source and mention the table when its business meaning is
ambiguous. Examples (replace the table/column with actual catalog names):

- Count rows in a table.
- Show the 10 most common values of a column.
- Show 10 records with 1 to 10 explicitly selected columns.
- Filter using case-insensitive trimmed equality or literal substring matching.
- Sort a record list by an authorized column.

Queries use one table at a time, at most five AND filters and at most 50 result
rows/groups. Joins, cross-source queries, sums and date ranges are not supported.
No arbitrary SQL from the model is executed.

## Provider and audit

Configure `OPENROUTER_API_KEY`, `OPENROUTER_MODEL`, and optional
`OPENROUTER_BASE_URL=https://openrouter.ai/api/v1`. Clear Laravel config cache
after configuration changes.

The provider receives the question and authorized schema/table/column names for
the selected source. It does not receive query results. Laravel executes the
validated plan in a read-only PostgreSQL transaction with a five-second query
timeout. Permissions are checked again before execution. Audit records are stored
in `pgsql9.ai_query_logs`; API keys and question/filter values are not recorded.
