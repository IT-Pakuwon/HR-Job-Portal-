<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        <div class="mb-4 sm:flex sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-lg font-bold text-gray-800 md:text-lg dark:text-gray-100">
                    {{ $menu->menu_name ?? 'Dashboard' }}
                </h1>
            </div>
        </div>

        @php
            $viewPath = 'components.multidashboard.' . $dashboardComponent;
        @endphp

        <div class="grid grid-cols-12 gap-6">
            @if(View::exists($viewPath))
                @include($viewPath)
            @else
                <div class="col-span-12">
                    <div class="bg-white rounded-xl border border-red-200 p-6">
                        <h2 class="font-bold text-red-600">
                            Dashboard Component Not Found
                        </h2>

                        <div class="mt-2 text-sm text-gray-500">
                            {{ $viewPath }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
