<x-app-layout>

    <div class="max-w-6xl mx-auto w-full p-2">

        <div class="mb-4">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Tenancy Support — Master</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Master data for the Tenancy Support module.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                'Company', 'Department', 'Site', 'Floor', 'Tenant', 'Tenant Company',
                'Doc Type', 'Work Type', 'Work Hour', 'Entrance / Exit Gate',
                'Auto Number', 'Mail Setting', 'Mail Template',
            ] as $label)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-[#0f172a]">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</span>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">Coming soon</span>
                </div>
            @endforeach
        </div>

    </div>

</x-app-layout>
