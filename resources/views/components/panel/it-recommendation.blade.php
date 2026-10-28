@props(['title' => 'IT · IT Recommendation'])
<x-panel.shell type="it-recommendation" url="/it-dashboard/recommendation-json" :title="$title" :statuses="['ALL' => 'All Status', 'W' => 'Waiting for IT', 'I' => 'Waiting Revision']" />
