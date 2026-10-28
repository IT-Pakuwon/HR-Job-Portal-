@props(['title' => 'GM · Summary'])
{{-- Widgets: config/summary_panels.php ('gm-summary'). Data: App\Services\Dashboard\SummaryPanels\SummaryData (gmXxx methods). --}}
<x-panel.summary-grid panel="gm-summary" :title="$title" />
