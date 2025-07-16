<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-2 py-1 sm:px-6 lg:px-2">
                <div class="gap">
                    <div
                        class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-2 xl:row-span-2 xl:flex-row">
                        <div class="flex w-full flex-col">

                            <!-- Tabs Start -->
                            <div x-data="{ tab: 'tab1', content1Loaded: false, content2Loaded: false, content3Loaded: false }">
                                <!-- Tab Headers -->
                                <div class="mb-4 flex space-x-2 border-b border-gray-300 dark:border-gray-600">
                                    <button @click="tab = 'tab1'; content1Loaded = true"
                                        :class="tab === 'tab1' ? 'border-b-2 border-blue-500 text-blue-600' :
                                            'text-gray-600 dark:text-gray-300'"
                                        class="px-4 py-2 text-lg font-medium focus:outline-none">
                                        Summary
                                    </button>
                                    <button @click="tab = 'tab2'; content2Loaded = true"
                                        :class="tab === 'tab2' ? 'border-b-2 border-blue-500 text-blue-600' :
                                            'text-gray-600 dark:text-gray-300'"
                                        class="px-4 py-2 text-lg font-medium focus:outline-none">
                                        Applicant Profile
                                    </button>
                                    <button @click="tab = 'tab3'; content3Loaded = true"
                                        :class="tab === 'tab3' ? 'border-b-2 border-blue-500 text-blue-600' :
                                            'text-gray-600 dark:text-gray-300'"
                                        class="px-4 py-2 text-lg font-medium focus:outline-none">
                                        Job Information
                                    </button>
                                </div>
                                {{-- <!-- Debugging: Display current tab state -->
                                <p>Current Tab: <strong x-text="tab"></strong></p> --}}

                                <!-- Tab Content -->
                                <div>
                                    <!-- Tab 1 -->
                                    <div x-show="tab === 'tab1'" x-transition x-init="$nextTick(() => { initializeComponent() })"
                                        class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                                        @include('pages.careers.approval')
                                    </div>

                                    <!-- Tab 2 -->
                                    <div x-show="tab === 'tab2'" x-init="content2Loaded = true" x-cloak
                                        class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                                        {{-- <p class="text-gray-800 dark:text-gray-200">Ini konten dari Tab 2.</p> --}}
                                        <!-- Include page content for tab2 -->
                                        <template x-if="content2Loaded">
                                            @include('pages.applicants.applicantscareer')
                                        </template>
                                    </div>

                                    <!-- Tab 3 -->
                                    <div x-show="tab === 'tab3'" x-init="content3Loaded = true" x-cloak
                                        class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                                        {{-- <p class="text-gray-800 dark:text-gray-200">Ini konten dari Tab 3.</p> --}}
                                        <!-- Include page content for tab3 -->
                                        <template x-if="content3Loaded">
                                            @include('pages.jobpostings.jobinformationcareers')
                                        </template>
                                    </div>
                                </div>
                            </div>


                            <!-- Tabs End -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
