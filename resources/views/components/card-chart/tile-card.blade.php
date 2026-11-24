@props([
    'title'       => '',
    'subtitle'    => '',
    'color'       => 'violet',
    'gradient'    => null,
    'contentId'   => 'tile-card-' . uniqid(),
    'loadingText' => 'Loading…',
])

{{--
  Card frame + an empty content area for JS-rendered metric tiles (a headline
  number and a grid of small tiles). The page's JS fills #{{ '{contentId}' }}
  with the markup, so the frame — header, padding, gradient bar — is the same
  for every card of this kind.

  Usage:
    <x-card-chart.tile-card title="New LOI & Memo" subtitle="PSM / OLA" contentId="myTiles" />
--}}

<x-card-chart.card-shell
    :title="$title" :subtitle="$subtitle" :color="$color" :gradient="$gradient"
    {{ $attributes->merge(['class' => 'flex flex-col']) }}>

    <div id="{{ $contentId }}" class="flex-1 px-5 pb-5 pt-2">
        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $loadingText }}</p>
    </div>

</x-card-chart.card-shell>
