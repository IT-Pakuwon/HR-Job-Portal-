# PgTrek Dashboard Migration, Data Audit & PDF Export Protocols

## 1. PgTrek Dashboard Migration & Data Audit Protocol
1. **Script Refactoring**: Extract all inline `<script>` tags from `resources/views/pages/pgtrek/dashboard.blade.php`. Create a new directory at `public/assets/js/pgtrek/` and save the code into an organized `.js` file. Link this new file back into the Blade view using the `asset()` helper.
2. **Data Pipeline Tracing**: Inspect `app/Http/Controllers/PgTrekDashboardController.php` to trace every database query, Eloquent model call, and calculation logic feeding data into that Blade view.
3. **Database Audit**: Use `php artisan tinker` or raw SQL execution via terminal to run the exact queries found in the controller against the live database.
4. **Validation Check**: Verify that the calculated mathematical outputs from the controller match the database aggregates (e.g., sums, counts, averages) and that no faulty JavaScript modifications are altering these values on the frontend.

## 2. PgTrek Dashboard PDF Export Redesign Protocol
* When requested to redesign the dashboard PDF export, locate the relevant controller layout method (e.g., inside `PgTrekDashboardController.php`) or the specific Blade export template view.
* Ensure the styling relies on clean, accessible CSS frameworks compatible with your Laravel PDF engine (such as `dompdf` or `snappy`). Avoid complex flexbox or grid spaces if using old Dompdf engines; use structured HTML `<table>` layouts for cross-engine stability instead.
* Enforce a professional executive structure.
