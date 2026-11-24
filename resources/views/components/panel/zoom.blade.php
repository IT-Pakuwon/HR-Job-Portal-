@props(['title' => 'Zoom'])
{{-- Zoom meetings this user was invited to (or organizes): join link while upcoming, cloud recording once ended.
     Rows: TodoController@zoomJson; recording files are fetched live per meeting (TodoController@recording). --}}
<x-panel.shell type="zoom" url="/todo/zoom-json" :title="$title" :statuses="['ALL' => 'All', 'UPCOMING' => 'Upcoming', 'ENDED' => 'Ended']" :page-sizes="[5, 10, 25, 50]" :page-size="5" />
