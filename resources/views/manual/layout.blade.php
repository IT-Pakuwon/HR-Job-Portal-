<x-app-layout>

    <div class="flex h-[calc(100dvh-56px-16px)] overflow-hidden bg-white dark:bg-gray-950">

        <!-- ================= SIDEBAR ================= -->
        <aside
            class="w-72 shrink-0 overflow-y-auto border-r border-gray-200 bg-gray-50/80 px-6 py-8 backdrop-blur dark:border-gray-800 dark:bg-gray-900">

            <div class="mb-8">
                <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400">
                    Manual
                </h2>
            </div>

            @foreach ($rootMenus as $rootMenu)
                @php
                    $rootSlug = \Illuminate\Support\Str::slug($rootMenu->menu_name);
                    $isRootActive = request()->segment(2) === $rootSlug;
                @endphp

                <div class="mb-6" x-data="{ openRoot: {{ $isRootActive ? 'true' : 'false' }} }">

                    <!-- ROOT -->
                    <button @click="openRoot = !openRoot"
                        class="{{ $isRootActive ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }} group flex w-full items-center justify-between text-sm font-semibold tracking-tight">

                        <span class="transition group-hover:translate-x-0.5">
                            {{ $rootMenu->menu_name }}
                        </span>

                        <svg class="h-4 w-4 opacity-60 transition-transform duration-200 ease-out"
                            :class="openRoot ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- CHILD LEVEL -->
                    <div x-show="openRoot" x-collapse
                        class="mt-3 space-y-1 border-l border-gray-200 pl-3 dark:border-gray-800">

                        @foreach ($rootMenu->children as $parentMenu)
                            @php
                                $parentSlug = \Illuminate\Support\Str::slug($parentMenu->menu_name);
                                $isParentActive = request()->segment(3) === $parentSlug;
                                $hasChildren = $parentMenu->children->count() > 0;
                            @endphp

                            <div x-data="{ openParent: {{ $isParentActive ? 'true' : 'false' }} }">

                                @if ($hasChildren)
                                    <!-- LEVEL 2 DROPDOWN -->
                                    <button @click="openParent = !openParent"
                                        class="{{ $isParentActive
                                            ? 'bg-gray-200/70 dark:bg-gray-800 text-gray-900 dark:text-white'
                                            : 'text-gray-600 hover:bg-gray-200/60 dark:text-gray-400 dark:hover:bg-gray-800' }} group flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-all duration-150">

                                        <span class="truncate">
                                            {{ $parentMenu->menu_name }}
                                        </span>

                                        <svg class="h-4 w-4 transition-transform duration-200"
                                            :class="openParent ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>

                                    <!-- LEVEL 3 -->
                                    <div x-show="openParent" x-collapse class="mt-1 space-y-1 pl-4">

                                        @foreach ($parentMenu->children as $childMenu)
                                            @php
                                                $childSlug = \Illuminate\Support\Str::slug($childMenu->menu_name);
                                                $isActive = request()->segment(4) === $childSlug;
                                            @endphp

                                            <a href="{{ route('manual', [$rootSlug, $parentSlug, $childSlug]) }}"
                                                class="{{ $isActive
                                                    ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-medium'
                                                    : 'text-gray-600 hover:bg-gray-200/60 dark:text-gray-400 dark:hover:bg-gray-800' }} block rounded-md px-3 py-1.5 text-sm transition-all">

                                                {{ $childMenu->menu_name }}

                                            </a>
                                        @endforeach

                                    </div>
                                @else
                                    <!-- DIRECT LINK -->
                                    <a href="{{ route('manual', [$rootSlug, $parentSlug]) }}"
                                        class="{{ $isParentActive
                                            ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-medium'
                                            : 'text-gray-600 hover:bg-gray-200/60 dark:text-gray-400 dark:hover:bg-gray-800' }} block rounded-lg px-3 py-2 text-sm transition-all">

                                        {{ $parentMenu->menu_name }}

                                    </a>
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

                {{-- LEVEL 3 (purchasing/request-budget/budget) --}}
                @if ($rootSlug && $parentSlug && $childSlug && view()->exists("manual.$rootSlug.$parentSlug.$childSlug"))
                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        @include("manual.$rootSlug.$parentSlug.$childSlug")
                    </div>

                    {{-- LEVEL 2 (purchasing/request-budget) --}}
                @elseif ($rootSlug && $parentSlug && view()->exists("manual.$rootSlug.$parentSlug"))
                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        @include("manual.$rootSlug.$parentSlug")
                    </div>

                    {{-- DEFAULT --}}
                @else
                    <div class="mt-32 text-center text-gray-400">
                        <h2 class="text-xl font-semibold tracking-tight">
                            Welcome to Manual
                        </h2>
                        <p class="mt-2 text-sm">
                            Select a page from the sidebar.
                        </p>
                    </div>
                @endif

            </div>

        </main>

    </div>

</x-app-layout>
