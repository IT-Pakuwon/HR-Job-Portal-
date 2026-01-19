@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-2">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $title }}
        </div>

        <div class="mt-4">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end bg-gray-100 px-6 py-2 text-right dark:bg-gray-900/20">
        {{ $footer }}
    </div>
</x-modal>
