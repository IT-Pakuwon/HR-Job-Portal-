@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <nav class="mb-4 flex justify-center sm:order-1 sm:mb-0" role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
            {{-- Previous Page Link --}}
            <div class="mr-2">
                @if ($paginator->onFirstPage())
                    <span
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-2.5 py-2 leading-5 text-gray-300 dark:border-gray-700/60 dark:bg-gray-800 dark:text-gray-600">
                        <span class="sr-only">{!! __('pagination.previous') !!}</span><wbr />
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M9.4 13.4l1.4-1.4-4-4 4-4-1.4-1.4L4 8z" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                        class="shadow-xs inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-2.5 py-2 leading-5 text-violet-500 hover:bg-gray-50 dark:border-gray-700/60 dark:bg-gray-800 dark:hover:bg-gray-900">
                        <span class="sr-only">{!! __('pagination.previous') !!}</span><wbr />
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M9.4 13.4l1.4-1.4-4-4 4-4-1.4-1.4L4 8z" />
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Pagination Elements --}}
            <ul class="shadow-xs inline-flex -space-x-px rounded-lg text-sm font-medium">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li aria-disabled="true">
                            <span
                                class="inline-flex items-center justify-center border border-gray-200 bg-white px-3.5 py-2 leading-5 text-gray-400 dark:border-gray-700/60 dark:bg-gray-800 dark:text-gray-500">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li aria-current="page">
                                    <span
                                        class="@if ($page === 1) {{ 'rounded-l-lg' }}@elseif($page === $paginator->lastPage()){{ 'rounded-r-lg' }} @endif inline-flex items-center justify-center border border-gray-200 bg-white px-3.5 py-2 leading-5 text-violet-500 dark:border-gray-700/60 dark:bg-gray-800">{{ $page }}</span>
                                </li>
                            @else
                                <li>
                                    <a href="{{ $url }}"
                                        class="@if ($page === 1) {{ 'rounded-l-lg' }}@elseif($page === $paginator->lastPage()){{ 'rounded-r-lg' }} @endif inline-flex items-center justify-center border border-gray-200 bg-white px-3.5 py-2 leading-5 text-gray-600 hover:bg-gray-50 dark:border-gray-700/60 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </ul>

            {{-- Next Page Link --}}
            <div class="ml-2">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                        class="shadow-xs inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-2.5 py-2 leading-5 text-violet-500 hover:bg-gray-50 dark:border-gray-700/60 dark:bg-gray-800 dark:hover:bg-gray-900">
                        <span class="sr-only">{!! __('pagination.next') !!}</span><wbr />
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" />
                        </svg>
                    </a>
                @else
                    <span
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-2.5 py-2 leading-5 text-gray-300 dark:border-gray-700/60 dark:bg-gray-800 dark:text-gray-600">
                        <span class="sr-only">{!! __('pagination.next') !!}</span><wbr />
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" />
                        </svg>
                    </span>
                @endif
            </div>
        </nav>

        <div class="text-center text-sm text-gray-500 sm:text-left">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium text-gray-600 dark:text-gray-300">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </div>
    </div>
@endif
