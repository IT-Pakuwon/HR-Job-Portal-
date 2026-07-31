@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'block pl-3 pr-4 py-2 border-l-4 border-violet-400 text-sm font-medium text-violet-700 bg-violet-50 focus:outline-hidden focus:text-violet-800 focus:bg-violet-100 focus:border-violet-700 transition dark:text-violet-300 dark:bg-violet-900/40 dark:focus:text-violet-200 dark:focus:bg-violet-900/60'
            : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-hidden focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:text-gray-200 dark:focus:bg-gray-700 dark:focus:border-gray-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
