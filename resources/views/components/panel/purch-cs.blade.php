@props(['title' => 'Purchasing · CS'])
<x-panel.shell type="purch-cs" url="/purchasing-dashboard/cs-json" :title="$title" :statuses="['ALL' => 'All Status', 'DRAFT' => 'Draft', 'PROGRESS' => 'On Progress']" />
