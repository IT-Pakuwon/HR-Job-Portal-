{{-- Card header for the SPP list pages: title, Create button and the All-mode filters (shown by the page script via #allFilters).
     <x-spp-list-header title="Request SPPK" :create-url="url('/createsppks')" /> --}}
@props(['title', 'createUrl'])

    <div class="flex flex-col gap-4 rounded-t-xl border-b border-gray-100 px-5 py-3.5 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            </span>
            <div>
                <h2 id="pageTitle" class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">
                    {{ $title }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Create, track and manage your requests</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- FILTER SECTION (ONLY FOR ALL MODE) --}}
            <div id="allFilters" class="hidden items-center gap-2 flex">

                {{-- Status Filter --}}
                <div class="sppb-f relative w-40">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                    <select id="filterStatus">
                        <option value="">All Status</option>
                        <option value="P">On Progress</option>
                        <option value="C">Completed</option>
                    </select>
                </div>

                {{-- Company Filter --}}
                <div class="sppb-f relative w-40">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                    <select id="filterCompany">
                        <option value="">All Company</option>
                    </select>
                </div>

                {{-- Department Filter --}}
                <div class="sppb-f relative w-48">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <select id="filterDepartment">
                        <option value="">All Department</option>
                    </select>
                </div>

            </div>

            <a id="createBtn" href="{{ $createUrl }}"
                class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                <i class="fa-solid fa-plus mr-2 text-xs"></i>Create
            </a>
        </div>
    </div>
