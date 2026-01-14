<x-app-layout>
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="gap">
            <div
                class="rounded-lgsm:col-span-1 flex w-full flex-col gap-2 overflow-hidden lg:row-span-2 xl:row-span-2 xl:flex-row dark:bg-gray-800">
                <div class="flex w-full flex-col">
                    <div x-data="tabsComponent()" x-init="initializeComponent()">
                        <div
                            class="flex items-center rounded-t-lg border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <button @click="tab = 'tab1'; content1Loaded = true"
                                :class="tab === 'tab1' ?
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                                Summary
                            </button>
                            <button @click="tab = 'tab2'; content2Loaded = true"
                                :class="tab === 'tab2' ?
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                                Applicant Profile
                            </button>
                            <button @click="tab = 'tab3'; content3Loaded = true"
                                :class="tab === 'tab3' ?
                                    'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                    'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="whitespace-nowrap px-3 py-3 text-sm font-medium transition-colors duration-200 focus:outline-none sm:px-4 sm:text-sm">
                                Job Information
                            </button>
                        </div>

                        <div>
                            <div x-show="tab === 'tab1'" x-transition:enter="transition ease-out duration-300 transform"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200 transform"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="rounded-b-lg bg-white p-4 dark:bg-gray-800" x-data="{ isOpen: false }">
                                <!-- supaya partial yang pakai isOpen tidak error -->
                                @include('pages.careers.approval')
                            </div>

                            <div x-show="tab === 'tab2'" x-cloak
                                x-transition:enter="transition ease-out duration-300 transform"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200 transform"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="rounded-b-lg bg-white p-4 dark:bg-gray-800" x-data="{ isOpen: false }">
                                @include('pages.applicants.applicantscareer')
                            </div>

                            <div x-show="tab === 'tab3'" x-cloak
                                x-transition:enter="transition ease-out duration-300 transform"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200 transform"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="rounded-b-lg bg-white p-4 dark:bg-gray-800" x-data="{ isOpen: false }">
                                @include('pages.jobpostings.jobinformationcareers')
                            </div>
                        </div>
                    </div>

                </div>
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
