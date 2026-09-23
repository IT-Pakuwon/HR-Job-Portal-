# TASK: Dark Mode Audit & Auto-Fix Protocol (`resources\views` & `public\assets`)

## Objective
Perform a targeted audit and refactoring of all Blade view templates inside `resources\views` and custom CSS/JS assets inside `public\assets`. Ensure every component, modal, table, card, form input, and script renders cleanly with proper contrast when dark mode is enabled.

---

## 1. Primary Target Locations
Recursively inspect and modify files in the following folders:
- `resources\views\**\*.blade.php`
- `public\assets\css\**\*.css`
- `public\assets\js\**\*.js`

---

## 2. Detection Patterns & Anti-Patterns to Fix

Search for and resolve the following visual bug sources across both directories:

### A. Hardcoded Light Backgrounds & Dark Text (`resources\views`)
- **Missing Dark Variants on Elements**:
  - `bg-white` $\rightarrow$ Add `dark:bg-gray-800` (or `dark:bg-slate-800`)
  - `bg-gray-50` / `bg-gray-100` $\rightarrow$ Add `dark:bg-gray-900`
  - `text-gray-900` / `text-black` / `text-slate-900` $\rightarrow$ Add `dark:text-white` or `dark:text-gray-100`
  - `text-gray-500` / `text-gray-600` $\rightarrow$ Add `dark:text-gray-400`
  - `border-gray-200` / `border-slate-200` $\rightarrow$ Add `dark:border-gray-700`

### B. Form Controls, Modals & Dropdowns (`resources\views`)
- **Inputs & Selects**: Fix `<input>`, `<select>`, and `<textarea>` elements where text becomes unreadable or white-on-white in dark mode.
  - Standardize with: `dark:bg-gray-900 dark:text-white dark:border-gray-700 dark:focus:ring-blue-500`
- **Modals & Dropdowns**: Ensure popup containers have explicit dark backgrounds (`dark:bg-gray-800`) and borders (`dark:border-gray-700`).

### C. Custom CSS & Hardcoded Inline Styles (`public\assets\css` & Views)
- **Inline Styles**: Replace hardcoded light hex values in `style="..."` attributes (e.g., `style="background: #ffffff; color: #333;"`) with adaptive classes or CSS variables.
- **Custom CSS Files**: Inspect CSS files in `public\assets\css\` for hardcoded light `#fff` or `#ffffff` declarations on background attributes and wrap/override them with proper dark mode media/class selectors (`.dark` or `@media (prefers-color-scheme: dark)`).

### D. Dynamic JS Chart Colors (`public\assets\js`)
- **ApexCharts / Chart.js**: Inspect chart initialization scripts in `public\assets\js\` to ensure axis labels, grid lines, and tooltips dynamically adjust text/border colors when switching between light and dark mode.

---

## 3. Standardized Dark Palette Guidelines

Use these consistent design tokens across your fixes:
- **Page Canvas / Body**: `dark:bg-gray-900`
- **Card / Modal / Header Containers**: `dark:bg-gray-800`
- **Dividers & Borders**: `dark:border-gray-700`
- **Primary Text**: `dark:text-gray-100`
- **Secondary / Muted Text**: `dark:text-gray-400`
- **Hover States**: `dark:hover:bg-gray-700`

---

## 4. Execution Steps

1. **Scan & Identify**: Audit all files in `resources\views` and `public\assets` for unhandled light mode classes and styles.
2. **Apply Auto-Fixes**: Directly update Blade templates and CSS files to inject dark mode utility classes.
3. **Clear View Cache**: Run `php artisan view:clear` to flush Blade cache and verify no template syntax errors were introduced.
