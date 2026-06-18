<x-app-layout>
    <div class="max-w-9xl mx-auto flex flex-col gap-3 p-2" x-data="tabsComponent()" x-init="initializeComponent()">

        {{-- ── Tab bar ───────────────────────────────────────────────── --}}
        <nav class="grid w-full grid-cols-3 gap-1 rounded-lg bg-gray-200/60 p-1 dark:bg-gray-700/50">
            <button @click="tab = 'tab1'"
                :class="tab === 'tab1'
                    ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-600 dark:text-white'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-150 focus:outline-none">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Summary
            </button>
            <button @click="tab = 'tab2'"
                :class="tab === 'tab2'
                    ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-600 dark:text-white'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-150 focus:outline-none">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Applicant Profile
            </button>
            <button @click="tab = 'tab3'"
                :class="tab === 'tab3'
                    ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-600 dark:text-white'
                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium transition-all duration-150 focus:outline-none">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Job Information
            </button>
        </nav>

        {{-- ── Tab content ──────────────────────────────────────────── --}}
        <div class="overflow-hidden">

            <div x-show="tab === 'tab1'" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{ isOpen: false }">
                @include('pages.careers.approval')
            </div>

            <div x-show="tab === 'tab2'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{ isOpen: false }">
                @include('pages.applicants.applicantscareer')
            </div>

            <div x-show="tab === 'tab3'" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{ isOpen: false }">
                @include('pages.jobpostings.jobinformationcareers')
            </div>

        </div>

    </div>
    <script>
        function tabsComponent() {
            return {
                // state tabs
                tab: 'tab1',
                content1Loaded: true,
                content2Loaded: false,
                content3Loaded: false,

                // fallback state umum yang sering dipakai partial
                isOpen: false,

                // methods
                initializeComponent() {
                    // jalankan inisialisasi awal di sini bila perlu
                    // contoh: this.content1Loaded = true
                }
            }
        }
    </script>

</x-app-layout>
