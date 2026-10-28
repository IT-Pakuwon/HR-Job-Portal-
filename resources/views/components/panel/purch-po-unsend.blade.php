@props(['title' => 'Purchasing · PO Unsend'])
<x-panel.shell type="purch-po-unsend" url="/purchasing-dashboard/po-unsend-json" :title="$title" :statuses="['ALL' => 'All Status', 'UNSEND' => 'Unsend', 'UNSEND_EMAIL' => 'Purchase - Unsend Email', 'ON_PROGRESS' => 'On Progress']" />
