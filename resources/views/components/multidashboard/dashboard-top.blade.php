@props(['dashCd', 'blockData' => []])
@php
    // A dashboard is arranged in Dashboard Setup (ms_dashboard_layout): rows of columns of blocks, the same for everyone
    // assigned to the dashboard. Until an admin saves one, DashboardLayout::defaultLayout() applies.
    // A block that does not apply is skipped here (Main Menu / Panel area need menu tiles, Your Progress needs a tile with a
    // count, KPI cards / Work tabs exist only on dashboards that have them) and an emptied column or row folds away, so a
    // saved arrangement can never break the page.
    // $blockData = variables the dashboard's own KPI / tab blocks need (a component does not see its parent's variables).
    $topItems = \App\Models\DashboardMenu::forDashboard($dashCd, auth()->user());
    $has = \App\Services\Dashboard\DashboardLayout::flags($dashCd, $topItems);
    $layout = \App\Services\Dashboard\DashboardLayout::resolve($dashCd, $has);

    // literal class names so Tailwind can see them
    $spanClass = [
        3 => 'xl:col-span-3', 4 => 'xl:col-span-4', 5 => 'xl:col-span-5', 6 => 'xl:col-span-6',
        7 => 'xl:col-span-7', 8 => 'xl:col-span-8', 9 => 'xl:col-span-9', 12 => 'xl:col-span-12',
    ];
@endphp
{{-- Auto-refresh interval: the one chosen in Dashboard Setup, else DashboardLayout::DEFAULT_REFRESH (20 s). The dashboards' scripts and the
     panels read window.dashRefreshSeconds when they (re)start their countdown. It must come before any of them, so it is first. --}}
<script>window.dashRefreshSeconds = {{ \App\Services\Dashboard\DashboardLayout::refreshSeconds($dashCd) }};</script>

{{-- The training-feedback reminders live inside the My Favourites block; keep them working when that block is taken off --}}
@unless (in_array('favourites', $layout['shown'], true))
    <x-multidashboard.component.feedback-reminders />
@endunless

@if ($layout['rows'])
    <div class="flex flex-col gap-4">
        @foreach ($layout['rows'] as $columns)
            <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-12">
                @foreach ($columns as $col)
                    @php $narrow = $col['span'] < 8; @endphp
                    <div class="flex min-w-0 flex-col gap-4 {{ $spanClass[$col['span']] ?? 'xl:col-span-12' }}">
                        @foreach ($col['blocks'] as $block)
                            @switch($block)
                                @case('menu')
                                    <x-multidashboard.component.menu :dash-cd="$dashCd" :items="$topItems" />
                                    @break
                                @case('favourites')
                                    <x-multidashboard.component.favourite />
                                    @break
                                @case('panels')
                                    @php app()->instance('dashPanelHostRendered', true); @endphp
                                    {{-- display: contents: the host adds no box (and no extra gap) of its own; a panel is a flex child only while it is visible --}}
                                    <div id="dashForeignPanelHost" class="contents"></div>
                                    {{ $slot }}
                                    @break
                                @case('progress')
                                    <x-multidashboard.component.progress :compact="$narrow" />
                                    @break
                                @case('todo')
                                    <x-multidashboard.component.todo :compact="$narrow" />
                                    @break
                                @case('kpis')
                                @case('tabs')
                                    @include(\App\Services\Dashboard\DashboardLayout::blockView($dashCd, $block), $blockData)
                                    @break
                            @endswitch
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

{{-- KPI cards / Work tabs taken off the page stay in it, out of sight: the dashboard's script reads their elements --}}
@if ($layout['hidden'])
    <div hidden aria-hidden="true">
        @foreach ($layout['hidden'] as $block)
            @include(\App\Services\Dashboard\DashboardLayout::blockView($dashCd, $block), $blockData)
        @endforeach
    </div>
@endif
