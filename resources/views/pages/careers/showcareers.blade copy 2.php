<x-app-layout> 
    <div class="py-1 w-full max-w-9xl mx-auto">
        <div class="grid">
            <div class="px-2 sm:px-6 lg:px-2 py-1 w-full max-w-9xl mx-auto">
                <div class="gap">    
                    <div class="flex flex-col xl:flex-row sm:col-span-1 lg:row-span-2 xl:row-span-2 gap-2 w-full overflow-hidden">
                        <div class="flex flex-col w-full">

                            <!-- Tabs Start -->    
                            <div x-data="{ tab: 'tab1' }" class="w-full">
                                <!-- Tab Header -->
                                <div class="flex space-x-2 border-b border-gray-300 dark:border-gray-600 mb-4">
                                    <button @click="tab = 'tab1'" 
                                            :class="tab === 'tab1' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'" 
                                            class="px-4 py-2 text-sm font-medium focus:outline-none">
                                        Covering Letter
                                    </button>
                                    <button @click="tab = 'tab2'" 
                                            :class="tab === 'tab2' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'" 
                                            class="px-4 py-2 text-sm font-medium focus:outline-none">
                                        Applicant Profile
                                    </button>
                                    <button @click="tab = 'tab3'" 
                                            :class="tab === 'tab3' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'" 
                                            class="px-4 py-2 text-sm font-medium focus:outline-none">
                                        Job Information
                                    </button>
                                </div>
                            
                                <!-- Tab Content -->
                                <div>
                                    <div x-show="tab === 'tab1'" x-transition class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow space-y-6">
                                        @include('pages.careers.approval')                                            
                                    </div>
                                    <div x-show="tab === 'tab2'" x-transition class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                        <p class="text-gray-800 dark:text-gray-200">Ini konten dari Tab 2.</p>
                                    </div>
                                    <div x-show="tab === 'tab3'" x-transition class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                        <p class="text-gray-800 dark:text-gray-200">Ini konten dari Tab 3.</p>
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
