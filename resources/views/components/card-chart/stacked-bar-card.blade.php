@props([
    'title'       => '',
    'titleId'     => null,
    'subtitle'    => '',
    'description' => '',
    'color'       => 'violet',
    'gradient'    => null,
    'chartId'     => 'stacked-bar-' . uniqid(),
    'summaryId'   => null,   // optional headline-tiles row above the chart, filled by the page's JS
    'tabsId'      => null,   // optional filter-tabs row, filled by the page's JS
    'minHeight'   => 280,
    'fill'        => false,  // true: chart stretches to the card's full height (card must be stretched by its parent, e.g. a grid row)
])

{{--
  Card frame + an empty chart container for a JS-rendered (ApexCharts) stacked
  bar. The page's JS fetches the data and paints into #{{ '{chartId}' }}; this
  component only provides the consistent frame so every stacked-bar card on a
  page looks and behaves the same.

  Usage:
    <x-card-chart.stacked-bar-card title="Status by Type" subtitle="Breakdown"
        chartId="myChart" tabsId="myTabs" :minHeight="240" />

  - tabsId : renders an empty tabs row above the chart (type switcher etc.).
  - extra classes (e.g. lg:col-span-2) are merged onto the card.
--}}

<x-card-chart.card-shell
    :title="$title" :titleId="$titleId" :subtitle="$subtitle" :color="$color" :gradient="$gradient"
    {{ $attributes->merge(['class' => 'flex flex-col']) }}>

    @if($description)
        <p class="px-5 pt-0.5 text-[11px] text-slate-400 dark:text-slate-500">{{ $description }}</p>
    @endif

    @if($summaryId)
        <div id="{{ $summaryId }}" class="grid grid-cols-2 gap-3 px-5 pb-1 pt-3 sm:grid-cols-4"></div>
    @endif

    @if($tabsId)
        <div id="{{ $tabsId }}" class="flex flex-wrap gap-1.5 px-5 pb-1 pt-2"></div>
    @endif

    <div class="flex-1 px-2 pb-3 pt-1 {{ $fill ? 'relative' : '' }}" @if($fill) style="min-height:{{ (int) $minHeight }}px" @endif>
        <div id="{{ $chartId }}" @if($fill) class="absolute inset-x-2 bottom-3 top-1" @else style="min-height:{{ (int) $minHeight }}px" @endif></div>
    </div>

</x-card-chart.card-shell>
