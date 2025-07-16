<x-app-layout> 
    <div class="py-1 w-full max-w-9xl mx-auto">
        <div class="grid">
            <div class="px-2 sm:px-6 lg:px-2 py-1 w-full max-w-9xl mx-auto">
                <div class="gap">    
                    <div class="flex flex-col xl:flex-row sm:col-span-1 lg:row-span-2 xl:row-span-2 gap-2 w-full overflow-hidden">
                        <div class="flex flex-col w-full">

                            <!-- Tabs Start -->    
                            <div x-data="{ tab: 'workorder', content1Loaded: false, content2Loaded: false, content3Loaded: false }">
                                <!-- Tab Headers -->
                                <div class="flex space-x-2 border-b border-gray-300 dark:border-gray-600 mb-4">
                                    <button @click="tab = 'workorder'; content1Loaded = true" 
                                            :class="tab === 'workorder' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'" 
                                            class="px-4 py-2 text-lg font-medium focus:outline-none">
                                        Work Order
                                    </button>
                                    <button @click="tab = 'workinstruction'; content2Loaded = true" 
                                            :class="tab === 'workinstruction' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'" 
                                            class="px-4 py-2 text-lg font-medium focus:outline-none">
                                        Work Instruction
                                    </button>                                   
                                </div>
                            
                              
                                <!-- Tab Content -->
                                <div>
                                    <!-- Tab 1 -->
                                    <div x-show="tab === 'workorder'" x-transition x-init="" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                        @include('pages.workorder.workorder')
                                    </div>
                            
                                    <!-- Tab 2 -->
                                    <div x-show="tab === 'workinstruction'" x-init="content2Loaded = true" x-cloak class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">                                      
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
