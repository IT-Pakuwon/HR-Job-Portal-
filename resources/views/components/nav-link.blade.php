@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-violet-400 text-xs font-medium leading-5 text-gray-900 focus:outline-hidden focus:border-violet-700 transition dark:text-gray-100'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-hidden focus:text-gray-700 focus:border-gray-300 transition dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-700 dark:focus:text-gray-200 dark:focus:border-gray-700';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
