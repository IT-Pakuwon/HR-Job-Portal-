@props(['title' => 'IT · Ticket'])
<x-panel.shell type="it-ticket" url="/it-dashboard/ticket-json" :title="$title" :statuses="[
    'ALL' => 'All Status', 'CREATED' => 'Created', 'RESPONSE' => 'Response', 'PROCESS' => 'Process', 'PENDING' => 'Pending',
    'ENVISION' => 'Envision', 'ENVISION CHECKED / SOLVED' => 'Envision Checked / Solved', 'TRANSFER' => 'Transfer', 'REOPEN' => 'Reopen',
]" />
