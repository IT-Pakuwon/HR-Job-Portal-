<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-2 py-1 sm:px-6 lg:px-2">
                <div class="gap">
                    <div
                        class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-2 xl:row-span-2 xl:flex-row">
                        <div class="flex w-full flex-col">

                            <!-- Tabs Start -->
                            <div x-data="{ tab: 'workorder', content1Loaded: false, content2Loaded: false, content3Loaded: false }">
                                <!-- Tab Headers -->
                                <div class="mb-4 flex space-x-2 border-b border-gray-300 dark:border-gray-600">
                                    <button @click="tab = 'workorder'; content1Loaded = true"
                                        :class="tab === 'workorder' ? 'border-b-2 border-blue-500 text-blue-600' :
                                            'text-gray-600 dark:text-gray-300'"
                                        class="px-4 py-2 text-sm font-medium focus:outline-none">
                                        Work Order
                                    </button>
                                    <button @click="tab = 'workinstruction'; content2Loaded = true"
                                        :class="tab === 'workinstruction' ? 'border-b-2 border-blue-500 text-blue-600' :
                                            'text-gray-600 dark:text-gray-300'"
                                        class="px-4 py-2 text-sm font-medium focus:outline-none">
                                        Work Instruction
                                    </button>
                                </div>


                                <!-- Tab Content -->
                                <div>
                                    <!-- Tab 1 -->
                                    <div x-show="tab === 'workorder'" x-transition x-init=""
                                        class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                                        @include('pages.workorder.workorder')
                                    </div>

                                    <!-- Tab 2 -->
                                    <div x-show="tab === 'workinstruction'" x-init="content2Loaded = true" x-cloak
                                        class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                                        <!-- Include page content for tab2 -->
                                        {{-- <template x-if="content2Loaded"> --}}
                                        @include('pages.workorder.workinstruction')
                                        {{-- </template> --}}
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
