@props(['title' => 'Plaud', 'dashCd' => ''])
{{-- Plaud recordings of the account(s) the admin mapped to this dashboard for the user's company (Dashboard Menu > Plaud accounts).
     Rows: PlaudController@panelJson; the summary of a recording is fetched on click (PlaudController@summary). --}}
<x-panel.shell type="plaud" :url="'/plaud/panel-json?dash=' . urlencode(strtoupper($dashCd))" :title="$title"
    :statuses="['ALL' => 'All', 'READY' => 'With summary', 'PENDING' => 'Summary pending']" :page-sizes="[5, 10, 25, 50]" :page-size="5" />
