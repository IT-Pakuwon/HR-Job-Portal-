<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $viewPath = 'components.multidashboard.' . $dashboardComponent;
        @endphp

        @if(View::exists($viewPath))
            @include($viewPath)
        @else
            <div class="col-span-12">
                <div class="rounded-xl border border-red-200 bg-white p-6">
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
</x-app-layout>
