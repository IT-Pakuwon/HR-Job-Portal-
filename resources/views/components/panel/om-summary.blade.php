@props(['title' => 'OM · Summary'])
{{-- Widgets: config/summary_panels.php ('om-summary'). Data: App\Services\Dashboard\SummaryPanels\SummaryData (omXxx methods). --}}
<x-panel.summary-grid panel="om-summary" :title="$title" />
