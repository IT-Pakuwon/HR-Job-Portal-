@props(['title' => 'IT · Access Request'])
<x-panel.shell type="it-access" url="/it-dashboard/access-request-json" :title="$title" :statuses="['ALL' => 'All Status', 'C' => 'Waiting for Process']" />
