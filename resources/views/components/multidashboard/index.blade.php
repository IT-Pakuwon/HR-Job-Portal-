<x-app-layout>
    @include('components.multidashboard._dsh-theme')
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $viewPath = 'components.multidashboard.' . $dashboardComponent;
        @endphp

        @if(View::exists($viewPath))
            @include($viewPath)
        @else
            <div class="col-span-12">
                <div class="rounded-xl border border-red-200 bg-white p-6 dark:bg-gray-800">
                    <h2 class="font-bold text-red-600">
                        Dashboard Component Not Found
                    </h2>

                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $viewPath }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Panels of other dashboards chosen on this dashboard's Menu tiles (see dashboard-menu). --}}
        @unless (app()->bound('dashPanelHostRendered'))
            <div id="dashForeignPanelHost" class="mt-4 empty:hidden"></div>
        @endunless

        @php
            // My Activity is a panel now (tile in Dashboard Menu). Dashboards without that tile keep the injected tab until one is added.
            $dashCode = array_search($dashboardComponent, \App\Http\Controllers\MultiDashboardController::DASHBOARD_COMPONENTS, true);
            $hasActivityPanel = $dashCode && \App\Models\DashboardMenu::forDashboard($dashCode, auth()->user())->contains(fn ($i) => ($i['panel'] ?? null) === 'my-activity');
        @endphp
        @if(View::exists($viewPath) && $dashboardComponent !== 'dashboard-om' && !$hasActivityPanel)
            {{-- One merged "My Activity" tab on every dashboard (except OM, which has none). --}}
            @include('components.multidashboard.my-activity', ['combined' => true])
        @endif

    </div>

    @include('partials.update-notification-panel')

    <script src="{{ asset('assets/js/multidashboard/update-notification.js') }}?v={{ file_exists(public_path('assets/js/multidashboard/update-notification.js')) ? filemtime(public_path('assets/js/multidashboard/update-notification.js')) : time() }}"></script>

</x-app-layout>
