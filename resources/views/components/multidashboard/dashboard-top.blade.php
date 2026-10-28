@props(['dashCd'])
@php
    // Menu tiles (ms_dashboard_menu_map) + My Favourites. With tiles: Menu left, Favourites right;
    // without: Favourites full width. Panel tiles that this dashboard can't host are already dropped by the model.
    $topItems = \App\Models\DashboardMenu::forDashboard($dashCd, auth()->user());
@endphp
@if ($topItems->isNotEmpty())
    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-2">
        <x-multidashboard.component.menu :dash-cd="$dashCd" :items="$topItems" />
        <div>
            <x-multidashboard.component.favourite />
        </div>
    </div>
@else
    <div class="grid grid-cols-12 gap-6">
        <x-multidashboard.component.favourite />
    </div>
@endif

<div class="mt-4 grid grid-cols-1 gap-4">
    <x-multidashboard.component.progress />
    <x-multidashboard.component.todo />
</div>
