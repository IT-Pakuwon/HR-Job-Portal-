<x-app-layout>

    <div class="flex h-[calc(100dvh-56px-16px)] overflow-hidden bg-white dark:bg-gray-950">

        <!-- ================= SIDEBAR ================= -->
        <aside
            class="w-72 shrink-0 overflow-y-auto border-r border-gray-200 bg-gray-50/80 px-6 py-8      dark:border-gray-800 dark:bg-gray-900">

            <div class="mb-8">
                <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400">
                    User Support Manual
                </h2>
            </div>

            @php
                $isFaqActive = request()->segment(2) === 'faq';
            @endphp

            {{-- FAQ MENU --}}
            <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-800">

                <a href="{{ route('manual', ['faq']) }}"
                    class="{{ $isFaqActive
                        ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-medium'
                        : 'text-gray-700 hover:bg-gray-200/60 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center rounded-lg px-3 py-2 text-sm transition-all duration-150">

                    FAQ

                </a>

            </div>

            @php
                $manualAllowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];
            @endphp

            @foreach ($rootMenus as $rootMenu)
                @php
                    $rootSlug = \Illuminate\Support\Str::slug($rootMenu->menu_slug ?? $rootMenu->menu_name);
                    $isRootActive = request()->segment(2) === $rootSlug;

                    // Mirror the same filter logic as the main sidebar
                    $visibleParents = $rootMenu->children->filter(function ($parentMenu) use ($manualAllowedIds) {
                        $visibleChildren = $parentMenu->children->whereIn('menu_id', $manualAllowedIds);
                        return $visibleChildren->isNotEmpty()
                            || (in_array($parentMenu->menu_id, $manualAllowedIds) && !empty($parentMenu->menu_route));
                    });
                @endphp

                @if ($visibleParents->isEmpty())
                    @continue
                @endif

                <div class="mb-4" x-data="{ openRoot: {{ $isRootActive ? 'true' : 'false' }} }">

                    <!-- ROOT -->
                    <button @click="openRoot = !openRoot"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700">

                        <span>{{ $rootMenu->menu_name }}</span>
                        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': openRoot }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- CHILD -->
                    <div x-show="openRoot" x-transition class="mt-2 space-y-1 pl-4">

                        @foreach ($visibleParents as $parentMenu)
                            @php
                                $parentSlug     = \Illuminate\Support\Str::slug($parentMenu->menu_slug ?? $parentMenu->menu_name);
                                $isParentActive = request()->segment(3) === $parentSlug;
                                $visibleChildren = $parentMenu->children->whereIn('menu_id', $manualAllowedIds);
                            @endphp

                            <div x-data="{ openParent: {{ $isParentActive ? 'true' : 'false' }} }">

                                <button @click="openParent = !openParent"
                                    class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <span>{{ $parentMenu->menu_name }}</span>
                                    @if ($visibleChildren->isNotEmpty())
                                        <svg class="h-3.5 w-3.5 transition-transform text-gray-400" :class="{ 'rotate-180': openParent }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </button>

                                <!-- SUB CHILD -->
                                @if ($visibleChildren->isNotEmpty())
                                    <div x-show="openParent" x-transition class="mt-1 space-y-1 pl-4">

                                        @foreach ($visibleChildren as $childMenu)
                                            @php
                                                $childSlug = \Illuminate\Support\Str::slug($childMenu->menu_slug ?? $childMenu->menu_name);
                                                $isActive  = request()->segment(4) === $childSlug;
                                            @endphp

                                            <a href="{{ route('manual', [$rootSlug, $parentSlug, $childSlug]) }}"
                                                class="{{ $isActive
                                                    ? 'bg-indigo-500 text-white'
                                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}
                                                block rounded-md px-3 py-1.5 text-sm">
                                                {{ $childMenu->menu_name }}
                                            </a>
                                        @endforeach

                                    </div>
                                @endif
                            </div>
                        @endforeach

                    </div>
                </div>
            @endforeach
        </aside>

        <!-- ================= CONTENT ================= -->
        <main class="flex-1 overflow-y-auto">

            <div class="max-w-9xl mx-auto p-8">

                @php
                    $rootSlug = request()->segment(2);
                    $parentSlug = request()->segment(3);
                    $childSlug = request()->segment(4);
                @endphp

                {{-- LEVEL 1 (Single Page seperti FAQ) --}}
                @if ($rootSlug && !$parentSlug && view()->exists("manual.$rootSlug"))
                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        @include("manual.$rootSlug")
                    </div>
                    {{-- LEVEL 3 (purchasing/request-budget/budget) --}}
                @elseif ($rootSlug && $parentSlug && $childSlug && view()->exists("manual.$rootSlug.$parentSlug.$childSlug"))
                    <div
                        class="prose prose-sm dark:prose-invert prose-headings:font-semibold prose-p:leading-relaxed prose-p:text-gray-700 dark:prose-p:text-gray-300 max-w-none">
                        @include("manual.$rootSlug.$parentSlug.$childSlug")
                    </div>

                    {{-- LEVEL 2 (purchasing/request-budget) --}}
                @elseif ($rootSlug && $parentSlug && view()->exists("manual.$rootSlug.$parentSlug"))
                    <div
                        class="prose prose-sm dark:prose-invert prose-headings:font-semibold prose-p:leading-relaxed prose-p:text-gray-700 dark:prose-p:text-gray-300 max-w-none">
                        @include("manual.$rootSlug.$parentSlug")
                    </div>

                    {{-- DEFAULT --}}
                @else
                    <div class="mt-32 text-center text-gray-400 dark:text-gray-500">
                        <h2 class="text-xl font-semibold tracking-tight text-gray-700 dark:text-gray-200">
                            Page Under Construction
                        </h2>
                        <p class="mt-2 text-sm">
                            This section is currently being developed and will be available soon.
                        </p>
                        <p class="mt-1 text-xs opacity-70">
                            Estimated release: March 2026
                        </p>
                    </div>
                @endif

            </div>

        </main>

    </div>

</x-app-layout>
