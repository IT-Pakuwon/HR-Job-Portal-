<x-app-layout>
    <div class="mx-auto w-full max-w-3xl p-4">
        <div class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <svg class="mx-auto h-12 w-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>

            <h1 class="mt-4 text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $title }}</h1>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>

            <p class="mt-6 text-xs font-medium uppercase tracking-wide text-indigo-500">
                Coming soon
            </p>
        </div>
    </div>
</x-app-layout>
