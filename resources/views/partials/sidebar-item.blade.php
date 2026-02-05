<li class="bg-linear-to-r {{ Request::segment(1) === $seg
    ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]'
    : '' }} rounded-lg py-2 transition"
    :class="{
        'pl-4 pr-3': sidebarExpanded,
        'px-2': !sidebarExpanded
    }">

    <a href="{{ route($seg) }}" class="group block truncate transition">
        <div class="flex items-center gap-3 transition-all duration-200">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                <svg class="{{ Request::segment(1) === $seg ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} group-hover:text-gray-600 dark:group-hover:text-gray-300"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <path d="{{ $path }}" />
                </svg>
            </div>

            <span x-show="sidebarExpanded"
                class="{{ Request::segment(1) === $seg ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} whitespace-nowrap text-sm font-medium leading-tight">
                {{ $label }}
            </span>

        </div>
    </a>
</li>
