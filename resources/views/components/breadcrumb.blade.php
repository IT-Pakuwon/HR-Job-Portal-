@props(['items' => []])

<div class="mb-3 flex flex-nowrap items-center justify-between gap-3">
    <nav class="flex min-w-0 overflow-x-auto" aria-label="Breadcrumb">
        <ol class="flex flex-nowrap items-center gap-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
            @foreach ($items as $index => $item)
                @php $isLast = $index === count($items) - 1; @endphp
                <li class="flex items-center gap-1.5">
                    @if ($index > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    @endif

                    @if (!empty($item['url']) && !$isLast)
                        <a href="{{ $item['url'] }}"
                            class="inline-flex items-center gap-1 font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                            @if ($index === 0)
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="h-4 w-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    @if (trim($slot) !== '')
        <div class="flex shrink-0 items-center">
            {{ $slot }}
        </div>
    @endif
</div>
