# TASK: Separate Recruitment Dashboard Inline JavaScript into Asset File

## Objective
Extract all inline `<script>` tag logic from `resources/views/pages/recruitment/dashboard.blade.php`, place it into a dedicated, modular JavaScript file under `public/assets/js/recruitment/`, and reference it in the Blade template using the `asset()` helper.

---

## Instructions

1. **Create Directory & JS File**:
   - Create a new directory if it does not exist: `public/assets/js/recruitment/`
   - Create a new file: `public/assets/js/recruitment/dashboard.js`

2. **Extract & Refactor Code**:
   - Cut all inline JavaScript logic (ApexCharts/Chart.js initialization, filter event handlers, dynamic dataset update logic) from `resources/views/pages/recruitment/dashboard.blade.php`.
   - Paste the extracted code into `public/assets/js/recruitment/dashboard.js`.
   - Ensure DOM-dependent initializations are wrapped inside a safety listener:
     ```javascript
     document.addEventListener('DOMContentLoaded', function () {
         // Dashboard initialization and chart logic here
     });
     ```

3. **Pass Dynamic Server Data to JS cleanly**:
   - If the chart datasets or filter configurations rely on PHP/Blade variables, pass them to JavaScript using data attributes on HTML elements or window global objects in a minimal script block, keeping the main initialization strictly inside `dashboard.js`.
   - *Example Blade data binding:*
     ```html
     <div id="dashboard-data" 
          data-chart-data="{{ json_encode($chartData) }}" 
          data-filter-options="{{ json_encode($filterOptions) }}"></div>
     ```

4. **Link File in Blade View**:
   - In `resources/views/pages/recruitment/dashboard.blade.php`, remove the extracted `<script>` block.
   - At the bottom of the Blade view (or inside your `@section('scripts')` stack), reference the external file:
     ```blade
     <script src="{{ asset('assets/js/recruitment/dashboard.js') }}"></script>
     ```

5. **Post-Extraction Verification**:
   - Run `php artisan view:clear` to ensure Blade cache is updated.
   - Verify that chart rendering and dynamic updates continue functioning without browser console errors.
