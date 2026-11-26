<x-app-layout>
    <x-spp-form-styles />
    <style>
        .parking-detail-table {
            min-width: 1600px;
            table-layout: fixed;
        }

        .parking-file-input {
            width: 100%;
            min-width: 220px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #ffffff;
            padding: 0 0.5rem 0 0;
            font-size: 0.875rem;
            color: #4b5563;
            overflow: hidden;
            cursor: pointer;
        }

        .parking-file-input::file-selector-button {
            height: 38px;
            margin-right: 0.75rem;
            border: 0;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 0 0.9rem;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
        }

        .parking-file-input:hover::file-selector-button {
            background: #dbeafe;
        }

        .dark .parking-file-input {
            border-color: #4b5563;
            background: #374151;
            color: #d1d5db;
        }

        .dark .parking-file-input::file-selector-button {
            background: #4b5563;
            color: #f3f4f6;
        }

        .dark .parking-file-input:hover::file-selector-button {
            background: #6b7280;
        }

        /* locked vehicle fields (renewal / change card) look read only */
        #parkingDetailTable input[readonly], #parkingDetailTable .cursor-not-allowed { background-color: #f3f4f6; color: #6b7280; }
        .dark #parkingDetailTable input[readonly], .dark #parkingDetailTable .cursor-not-allowed { background-color: #374151; color: #9ca3af; }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">
        <form id="parkingRegistrationForm" class="sf-field flex flex-col gap-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_draft" id="isDraftField" value="0">

            {{-- HEADER --}}
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-square-parking"></i></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Create Parking Registration</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Choose the parking request, list the vehicles and attach the supporting files.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-5 p-6">

                {{-- BARIS 1 --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-6">

                    {{-- Company --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Company
                        </label>
                        <select name="cpny_id" id="cpny_id"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpny_id }}"
                                    {{ optional($usercpny2)->cpny_id == $p->cpny_id ? 'selected' : '' }}>
                                    {{ $p->cpny_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Department
                        </label>
                        <select name="department_id" id="department_id"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select Department</option>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->department_id }}"
                                    {{ optional($userdept2)->department_id == $p->department_id ? 'selected' : '' }}>
                                    {{ $p->department_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Periode
                        </label>

                        <select name="perpost" id="perpost"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select Year</option>
                            <option value="{{ now()->format('Y') }}" selected>
                                {{ now()->format('Y') }}
                            </option>
                            <option value="{{ now()->addYear()->format('Y') }}">
                                {{ now()->addYear()->format('Y') }}
                            </option>
                        </select>
                    </div>

                    {{-- Site ID Parking --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Site ID Parking
                        </label>
                        <select name="site_id_parking" id="site_id_parking"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select Site Parking</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->siteid }}">
                                    {{ $site->siteid }} - {{ $site->site_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Parking Type --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Parking Type
                        </label>
                        <select name="parking_type" id="parking_type"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select Parking Type</option>
                            @foreach ($parkingTypes as $row)
                                <option value="{{ $row->categoryid }}">
                                    {{ $row->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Worker Type --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Worker Type
                        </label>
                        <select name="worker_type" id="worker_type"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select Worker Type</option>
                            @foreach ($workerTypes as $row)
                                <option value="{{ $row->categoryid }}">
                                    {{ $row->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
             
                {{-- Info --}}
                {{-- Date Range + Info --}}
                <div id="nonEmployeeExtraSection" class="hidden grid grid-cols-1 gap-5 lg:grid-cols-2">

                    {{-- Date Range --}}
                    <div id="extraDateRangeBox" class="flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Date Range
                        </label>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 dark:text-gray-400">Start Date</label>
                                <input type="date" name="startdate" id="startdate"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 dark:text-gray-400">End Date</label>
                                <input type="date" name="enddate" id="enddate"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div id="infoBox" class="flex flex-col gap-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Info
                        </label>

                        <textarea name="info" id="info" rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Input info..."></textarea>
                    </div>

                </div>
                </div>
            </div>
            
            {{-- DETAIL --}}
            <div id="detailSection" class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <details class="group" open>
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-car"></i></span>
                            <span>
                                <span class="block text-base font-bold text-gray-800 dark:text-white">Detail</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">The vehicles to register</span>
                            </span>
                        </span>
                        <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                        <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                    </summary>

                    <div class="flex h-auto flex-col justify-start px-6 pb-6 pt-5">
                        <div class="overflow-x-auto">
                            <table class="parking-detail-table mb-4 border-separate border-spacing-0 text-sm">
                                <colgroup>
                                    <col style="width: 60px;">
                                    <col style="width: 420px;">
                                    <col style="width: 180px;">
                                    <col style="width: 150px;">
                                    <col style="width: 300px;">
                                    <col style="width: 300px;">
                                    <col style="width: 300px;">
                                    <col style="width: 70px;">
                                </colgroup>

                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">No</th>
                                        <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-left">Name</th>
                                        <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-left">No Polisi</th>
                                        <th class="req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-left">Jenis Kendaraan</th>
                                        <th class="stnkHeader req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-left">Attach STNK</th>
                                        <th class="idcardHeader req border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-left">Attach ID Card</th>
                                        <th class="buktiBayarHeader border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-left">Attach Bukti Bayar</th>
                                        <th class="border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center"></th>
                                    </tr>
                                </thead>

                                <tbody id="parkingDetailTable">
                                    <tr class="parking-detail-row">
                                        <td class="row-number border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">1</td>

                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                            {{-- Untuk Worker Type = EMPLOYEE / Karyawan --}}
                                            <select name="detail_username[]"
                                                class="employeeSelect hidden h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $emp)
                                                    <option value="{{ $emp->username }}" data-name="{{ $emp->name }}">
                                                        {{ $emp->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            {{-- Untuk Worker Type selain EMPLOYEE / Karyawan --}}
                                            <input type="text" name="detail_name_manual[]"
                                                class="manualNameInput h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                placeholder="Input name...">

                                            {{-- Name final yang dikirim ke backend --}}
                                            <input type="hidden" name="detail_name[]" class="detailNameHidden">
                                        </td>                                      
                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                            <input type="hidden" name="detail_nopol_lama[]" class="oldNopolHidden">

                                            <div class="oldNopolWrapper mb-2 hidden">
                                                <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                    No Polisi Lama
                                                </label>
                                                <input type="text"
                                                    class="oldNopolDisplay uppercase h-10 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    readonly>
                                            </div>

                                            <label class="nopolFinalLabel mb-1 hidden text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                No Polisi Baru
                                            </label>

                                            <input type="text" name="detail_no_polisi[]"
                                                class="nopolFinalInput uppercase h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                placeholder="B 1234 ABC" required>
                                        </td>

                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                            <input type="hidden" name="detail_jenis_lama[]" class="oldJenisHidden">

                                            <div class="oldJenisWrapper mb-2 hidden">
                                                <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                    Jenis Kendaraan Lama
                                                </label>
                                                <input type="text"
                                                    class="oldJenisDisplay h-10 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    readonly>
                                            </div>

                                            <label class="jenisFinalLabel mb-1 hidden text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                Jenis Kendaraan Baru
                                            </label>

                                            <select name="detail_jenis_kendaraan[]"
                                                class="jenisFinalInput sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                required>
                                                <option value="">Select</option>
                                                <option value="Motor">Motor</option>
                                                <option value="Mobil">Mobil</option>
                                            </select>
                                        </td>

                                        <td class="stnkCell border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                            <input type="file" name="detail_attach_stnk[]"
                                                class="attachStnkInput parking-file-input">
                                        </td>

                                        <td class="idcardCell border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                            <input type="file" name="detail_attach_idcard[]"
                                                class="attachIdcardInput parking-file-input">                                            
                                        </td>

                                        <td class="buktiBayarCell border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                            <input type="file" name="detail_attach_bukti_bayar[]"
                                                class="attachBuktiBayarInput parking-file-input">                                            
                                        </td>

                                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">
                                            <button type="button"
                                                class="removeParkingDetail h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="addParkingDetail"
                            class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Add Row
                        </button>
                    </div>
                </details>
            </div>

            {{-- ATTACHMENTS --}}
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <details class="group" open>
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-paperclip"></i></span>
                            <span>
                                <span class="block text-base font-bold text-gray-800 dark:text-white">Attachments <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(Optional)</span></span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents</span>
                            </span>
                        </span>
                        <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                        <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                    </summary>

                    <div class="flex flex-col px-6 pb-6 pt-5">
                        <div id="attachmentsContainer">
                            <div class="attachment-row flex items-center gap-2">
                                <input type="file" name="attachments[]"
                                    class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                <button type="button"
                                    class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </div>
                        </div>

                        <button type="button" id="addAttachment"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Add Attachment
                        </button>
                    </div>
                </details>

                <div class="flex flex-row flex-wrap justify-between gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-700 items-end md:items-center">
                    <button type="button" id="backBtn"
                        class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Back</span>
                    </button>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <button type="button" id="saveDraftBtn"
                            class="flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            <span id="draftBtnText">Save as Draft</span>
                            <svg id="draftLoadingSpinner" class="hidden h-5 w-5 animate-spin text-slate-600 dark:text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </button>

                        <button type="submit" id="submitBtn"
                            class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Submit Approval</span>
                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Overlay --}}  

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $.fn.select2.defaults.set('dropdownCssClass', 'sf-s2-dropdown');
    </script>

    <script>
        const parkingAttachSettings = @json($parkingAttachSettings ?? []);

        function isRenewalType() {
            return currentParkingType() === 'RENEWAL';
        }

        function isChangeNopolType() {
            return currentParkingType() === 'CHANGENOPOL';
        }

        function isChangeCardType() {
            return currentParkingType() === 'CHANGECARD';
        }

        function isNewOrTempType() {
            return ['NEWREQUEST', 'TEMPREQUEST'].includes(currentParkingType());
        }

        function setFileCell($cell, $input, show, required = false) {
            if (show) {
                $cell.removeClass('hidden').show();
                $input.prop('required', required);
            } else {
                $input.val('');
                $input.prop('required', false);
                $cell.addClass('hidden').hide();
            }
        }

        function setFileHeader(showStnk, showIdCard, showBuktiBayar) {
            if (showStnk) {
                $('.stnkHeader').removeClass('hidden').show();
            } else {
                $('.stnkHeader').addClass('hidden').hide();
            }

            if (showIdCard) {
                $('.idcardHeader').removeClass('hidden').show();
            } else {
                $('.idcardHeader').addClass('hidden').hide();
            }

            if (showBuktiBayar) {
                $('.buktiBayarHeader').removeClass('hidden').show();
            } else {
                $('.buktiBayarHeader').addClass('hidden').hide();
            }

            $('.stnkHeader').toggleClass('req', showStnk);
            $('.idcardHeader').toggleClass('req', showIdCard);
            $('.buktiBayarHeader').toggleClass('req', showBuktiBayar);
        }

        function currentAttachmentSetting() {
            const key = `${currentParkingType()}|${currentWorkerType()}`;
            const setting = parkingAttachSettings[key];

            if (!setting) {
                return {
                    att_stnk: true,
                    att_idcard: true,
                    att_buktibayar: true
                };
            }

            return {
                att_stnk: Boolean(setting.att_stnk),
                att_idcard: Boolean(setting.att_idcard),
                att_buktibayar: Boolean(setting.att_buktibayar)
            };
        }

        function resetParkingDetailRows() {
            /*
            |--------------------------------------------------------------------------
            | Destroy select2 yang sudah aktif
            |--------------------------------------------------------------------------
            */
            $('#parkingDetailTable .employeeSelect').each(function () {
                destroyEmployeeSelect2($(this));
            });

            /*
            |--------------------------------------------------------------------------
            | Reset table detail menjadi 1 row baru
            |--------------------------------------------------------------------------
            */
            $('#parkingDetailTable').html(parkingDetailRowTemplate());

            refreshDetailRowNumber();
            applyNameMode();
            applyParkingTypeDetailMode();
        }

        function setReadonlyVehicle($row, readonly) {
            const $nopol = $row.find('.nopolFinalInput');
            const $jenis = $row.find('.jenisFinalInput');

            if (readonly) {
                $nopol.prop('readonly', true).addClass('bg-gray-100 cursor-not-allowed');

                $jenis
                    .addClass('bg-gray-100 cursor-not-allowed pointer-events-none')
                    .attr('tabindex', '-1');
            } else {
                $nopol.prop('readonly', false).removeClass('bg-gray-100 cursor-not-allowed');

                $jenis
                    .removeClass('bg-gray-100 cursor-not-allowed pointer-events-none')
                    .removeAttr('tabindex');
            }
        }

        function applyParkingTypeDetailMode() {
            const parkingType = currentParkingType();
            const attachSetting = currentAttachmentSetting();

            setFileHeader(
                attachSetting.att_stnk,
                attachSetting.att_idcard,
                attachSetting.att_buktibayar
            );

            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);

                const $oldNopolWrapper = $row.find('.oldNopolWrapper');
                const $oldJenisWrapper = $row.find('.oldJenisWrapper');

                const $nopolFinalLabel = $row.find('.nopolFinalLabel');
                const $jenisFinalLabel = $row.find('.jenisFinalLabel');

                const $stnkCell = $row.find('.stnkCell');
                const $idcardCell = $row.find('.idcardCell');
                const $buktiCell = $row.find('.buktiBayarCell');

                const $stnkInput = $row.find('.attachStnkInput');
                const $idcardInput = $row.find('.attachIdcardInput');
                const $buktiInput = $row.find('.attachBuktiBayarInput');

                /*
                |--------------------------------------------------------------------------
                | Reset default NEWREQUEST / TEMPREQUEST
                |--------------------------------------------------------------------------
                */
                $oldNopolWrapper.addClass('hidden');
                $oldJenisWrapper.addClass('hidden');
                $nopolFinalLabel.addClass('hidden');
                $jenisFinalLabel.addClass('hidden');

                setReadonlyVehicle($row, false);

                setFileCell($stnkCell, $stnkInput, attachSetting.att_stnk, attachSetting.att_stnk);
                setFileCell($idcardCell, $idcardInput, attachSetting.att_idcard, attachSetting.att_idcard);
                setFileCell($buktiCell, $buktiInput, attachSetting.att_buktibayar, attachSetting.att_buktibayar);

                /*
                |--------------------------------------------------------------------------
                | RENEWAL
                |--------------------------------------------------------------------------
                */
                if (parkingType === 'RENEWAL') {
                    setReadonlyVehicle($row, true);
                }

                /*
                |--------------------------------------------------------------------------
                | CHANGENOPOL
                |--------------------------------------------------------------------------
                */
                if (parkingType === 'CHANGENOPOL') {
                    $oldNopolWrapper.removeClass('hidden');
                    $oldJenisWrapper.removeClass('hidden');

                    $nopolFinalLabel.removeClass('hidden');
                    $jenisFinalLabel.removeClass('hidden');

                    setReadonlyVehicle($row, false);
                }

                /*
                |--------------------------------------------------------------------------
                | CHANGECARD
                |--------------------------------------------------------------------------
                */
                if (parkingType === 'CHANGECARD') {
                    setReadonlyVehicle($row, true);
                }
            });
        }

        function currentParkingType() {
            return String($('#parking_type').val() || '').toUpperCase();
        }

        function currentWorkerType() {
            return String($('#worker_type').val() || '').toUpperCase();
        }

        function isEmployeeWorkerType() {
            const val = currentWorkerType();
            const text = String($('#worker_type option:selected').text() || '').toUpperCase();

            return val === 'EMPLOYEE' || text.includes('KARYAWAN');
        }        

        function isInternshipWorkerType() {
            const val = currentWorkerType();
            const text = String($('#worker_type option:selected').text() || '').toUpperCase();

            return val === 'INTERNSHIP' || text.includes('INTERNSHIP') || text.includes('MAGANG');
        }

        function isExistingParkingType() {
            const parkingType = currentParkingType();

            return ['RENEWAL', 'CHANGECARD', 'CHANGENOPOL'].includes(parkingType);
        }

        function isOprVehiclesWorkerType() {
            return currentWorkerType() === 'OPRVEHICLES';
        }

        function isNewOrTempParkingType() {
            return ['NEWREQUEST', 'TEMPREQUEST'].includes(currentParkingType());
        }

        function shouldUseDropdownName() {
            /*
            |--------------------------------------------------------------------------
            | Dropdown Name dipakai untuk:
            | 1. EMPLOYEE
            | 2. OPRVEHICLES hanya untuk NEWREQUEST / TEMPREQUEST
            | 3. RENEWAL / CHANGECARD / CHANGENOPOL tetap dari MsParkingKendaraan
            |--------------------------------------------------------------------------
            */
            return isEmployeeWorkerType()
                || (isOprVehiclesWorkerType() && isNewOrTempParkingType())
                || isExistingParkingType();
        }

        function setJenisKendaraanValue($row, jenis) {
            const $jenis = $row.find('.jenisFinalInput');
            jenis = String(jenis || '').trim();

            if (!jenis) {
                $jenis.val('');
                return;
            }

            if ($jenis.find(`option[value="${jenis}"]`).length === 0) {
                $jenis.append(new Option(jenis, jenis, true, true));
            }

            $jenis.val(jenis);
        }

        function destroyEmployeeSelect2($select) {
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.next('.select2-container').remove();
        }

        function initEmployeeSelect2($scope = $(document)) {
            $scope.find('.employeeSelect').each(function () {
                const $select = $(this);

                destroyEmployeeSelect2($select);

                $select.select2({
                    width: '100%',
                    placeholder: 'Search name...',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('parkingregistration.employees') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term || '',
                                cpny_id: $('#cpny_id').val() || '',
                                department_id: $('#department_id').val() || '',
                                site_id_parking: $('#site_id_parking').val() || '',
                                perpost: $('#perpost').val() || '',
                                parking_type: $('#parking_type').val() || '',
                                worker_type: $('#worker_type').val() || ''
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results || []
                            };
                        },
                        cache: true
                    }
                });
            });
        }

        function refreshDetailRowNumber() {
            $('#parkingDetailTable .parking-detail-row').each(function (idx) {
                $(this).find('.row-number').text(idx + 1);
            });

            if ($('#parkingDetailTable .parking-detail-row').length > 1) {
                $('.removeParkingDetail').removeClass('hidden');
            } else {
                $('.removeParkingDetail').addClass('hidden');
            }
        }

        function clearDropdownSelections() {
            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);
                const $select = $row.find('.employeeSelect');

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.val(null).trigger('change');
                } else {
                    $select.val('');
                }

                $row.find('.detailNameHidden').val('');
                $row.find('input[name="detail_no_polisi[]"]').val('');
                $row.find('select[name="detail_jenis_kendaraan[]"]').val('');
            });
        }

        function applyNameMode() {
            const useDropdown = shouldUseDropdownName();

            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);
                const $empSelect = $row.find('.employeeSelect');
                const $manualInput = $row.find('.manualNameInput');
                const $hiddenName = $row.find('.detailNameHidden');

                if (useDropdown) {
                    $manualInput
                        .addClass('hidden')
                        .prop('required', false)
                        .val('');

                    $empSelect
                        .removeClass('hidden')
                        .prop('required', true);

                    initEmployeeSelect2($row);

                    $hiddenName.val('');
                } else {
                    destroyEmployeeSelect2($empSelect);

                    $empSelect
                        .addClass('hidden')
                        .prop('required', false)
                        .val('');

                    $manualInput
                        .removeClass('hidden')
                        .prop('required', true);

                    $hiddenName.val(String($manualInput.val() || '').trim());
                }
            });
        }

        function cleanSelectedUsername(raw) {
            raw = String(raw || '');

            if (raw.startsWith('OPRVEHICLES|') || raw.startsWith('PARKING|') || raw === 'PARKING') {
                return '';
            }

            if (raw.includes('|')) {
                return raw.split('|')[0];
            }

            return raw;
        }

        function syncDetailNamesBeforeSubmit() {
            let isValid = true;
            let firstInvalid = null;

            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);
                const $empSelect = $row.find('.employeeSelect');
                const $manualInput = $row.find('.manualNameInput');
                const $hiddenName = $row.find('.detailNameHidden');

                let finalName = '';

                if (shouldUseDropdownName()) {
                    if ($empSelect.hasClass('select2-hidden-accessible')) {
                        const data = $empSelect.select2('data');
                        const selected = data && data.length ? data[0] : null;

                        finalName = selected?.name || selected?.text || '';
                    }

                    finalName = String(finalName || '').trim();

                    if (
                        finalName === '' ||
                        finalName === 'Select Employee' ||
                        finalName === 'Search name...'
                    ) {
                        isValid = false;
                        firstInvalid = firstInvalid || $empSelect;
                    }
                } else {
                    finalName = String($manualInput.val() || '').trim();

                    if (finalName === '') {
                        isValid = false;
                        firstInvalid = firstInvalid || $manualInput;
                    }
                }

                $hiddenName.val(finalName);
            });

            if (!isValid) {
                toastr.error('Mohon isi Name pada detail.');

                if (firstInvalid && firstInvalid.length) {
                    $('html, body').animate({
                        scrollTop: firstInvalid.closest('tr').offset().top - 120
                    }, 300);
                }

                return false;
            }

            return true;
        }

        function syncDetailUsernameBeforeSubmit() {
            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);
                const $select = $row.find('.employeeSelect');

                if ($select.length && $select.val()) {
                    const cleaned = cleanSelectedUsername($select.val());
                    const data = $select.hasClass('select2-hidden-accessible') ? $select.select2('data') : [];
                    const text = data && data.length ? data[0].text : cleaned;

                    const option = new Option(text, cleaned, true, true);

                    $select.empty().append(option);
                }
            });
        }       

        function parkingDetailRowTemplate() {
            return `
                <tr class="parking-detail-row">
                    <td class="row-number border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">1</td>

                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <select name="detail_username[]"
                            class="employeeSelect hidden h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        </select>

                        <input type="text" name="detail_name_manual[]"
                            class="manualNameInput h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Input name...">

                        <input type="hidden" name="detail_name[]" class="detailNameHidden">
                    </td>

                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <input type="hidden" name="detail_nopol_lama[]" class="oldNopolHidden">

                        <div class="oldNopolWrapper mb-2 hidden">
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">No Polisi Lama</label>
                            <input type="text"
                                class="oldNopolDisplay uppercase h-10 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                readonly>
                        </div>

                        <label class="nopolFinalLabel mb-1 hidden text-xs font-semibold text-gray-500 dark:text-gray-400">
                            No Polisi Baru
                        </label>

                        <input type="text" name="detail_no_polisi[]"
                            class="nopolFinalInput uppercase h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="B 1234 ABC" required>
                    </td>

                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <input type="hidden" name="detail_jenis_lama[]" class="oldJenisHidden">

                        <div class="oldJenisWrapper mb-2 hidden">
                            <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Jenis Kendaraan Lama</label>
                            <input type="text"
                                class="oldJenisDisplay h-10 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                readonly>
                        </div>

                        <label class="jenisFinalLabel mb-1 hidden text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Jenis Kendaraan Baru
                        </label>

                        <select name="detail_jenis_kendaraan[]"
                            class="jenisFinalInput sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            required>
                            <option value="">Select</option>
                            <option value="Motor">Motor</option>
                            <option value="Mobil">Mobil</option>
                        </select>
                    </td>

                    <td class="stnkCell border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <input type="file" name="detail_attach_stnk[]"
                            class="attachStnkInput parking-file-input">
                    </td>

                    <td class="idcardCell border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <input type="file" name="detail_attach_idcard[]"
                            class="attachIdcardInput parking-file-input">
                    </td>

                    <td class="buktiBayarCell border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <input type="file" name="detail_attach_bukti_bayar[]"
                            class="attachBuktiBayarInput parking-file-input">
                    </td>

                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center align-middle">
                        <button type="button"
                            class="removeParkingDetail h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    </td>
                </tr>
            `;
        }
       
        $(document).on('click', '#addParkingDetail', function () {
            const $row = $(parkingDetailRowTemplate());

            $('#parkingDetailTable').append($row);

            refreshDetailRowNumber();
            applyNameMode();
            applyParkingTypeDetailMode();
        });

        $(document).on('click', '.removeParkingDetail', function () {
            const $row = $(this).closest('.parking-detail-row');
            const $select = $row.find('.employeeSelect');

            destroyEmployeeSelect2($select);

            $row.remove();

            refreshDetailRowNumber();
            applyNameMode();
            applyParkingTypeDetailMode();
        });

        // function toggleNonEmployeeExtraFields() {
        //     const isEmp = isEmployeeWorkerType();
        //     const workerType = String($('#worker_type').val() || '').trim();

        //     if (workerType !== '' && !isEmp) {
        //         $('#nonEmployeeExtraSection').removeClass('hidden');

        //         $('#startdate').prop('required', true);
        //         $('#enddate').prop('required', true);
        //     } else {
        //         $('#nonEmployeeExtraSection').addClass('hidden');

        //         $('#startdate').prop('required', false).val('');
        //         $('#enddate').prop('required', false).val('');
        //         $('#info').val('');
        //     }
        // }

        function toggleNonEmployeeExtraFields() {
            const parkingType = currentParkingType();
            const workerType  = String($('#worker_type').val() || '').trim();
            const isEmp       = isEmployeeWorkerType();

            /*
            |--------------------------------------------------------------------------
            | Extra section tampil jika:
            | 1. Worker Type selain EMPLOYEE
            | 2. Parking Type NEWREQUEST / TEMPREQUEST + Worker Type EMPLOYEE
            |
            | Date Range tampil dan wajib jika:
            | 1. Worker Type selain EMPLOYEE
            | 2. Parking Type TEMPREQUEST + Worker Type EMPLOYEE
            |--------------------------------------------------------------------------
            */
            const shouldShowEmployeeInfo = ['NEWREQUEST', 'TEMPREQUEST'].includes(parkingType) && isEmp;
            const shouldShowEmployeeDateRange = parkingType === 'TEMPREQUEST' && isEmp;
            const showExtraSection =
                workerType !== '' &&
                (
                    !isEmp ||
                    shouldShowEmployeeInfo
                );
            const shouldRequireDateRange = workerType !== '' && (!isEmp || shouldShowEmployeeDateRange);

            if (showExtraSection) {
                $('#nonEmployeeExtraSection').removeClass('hidden');
                $('#extraDateRangeBox').toggleClass('hidden', !shouldRequireDateRange);
                $('#infoBox').toggleClass('lg:col-span-2', !shouldRequireDateRange);

                $('#startdate').prop('required', shouldRequireDateRange);
                $('#enddate').prop('required', shouldRequireDateRange);

                if (!shouldRequireDateRange) {
                    $('#startdate').val('');
                    $('#enddate').val('');
                }
            } else {
                $('#nonEmployeeExtraSection').addClass('hidden');
                $('#extraDateRangeBox').removeClass('hidden');
                $('#infoBox').removeClass('lg:col-span-2');

                $('#startdate').prop('required', false).val('');
                $('#enddate').prop('required', false).val('');
                $('#info').val('');
            }
        }
       
        $(document).on('change', '#parking_type', function () {
            resetParkingDetailRows();

            if (typeof toggleNonEmployeeExtraFields === 'function') {
                toggleNonEmployeeExtraFields();
            }
        });

        $(document).on('change', '#worker_type', function () {
            clearDropdownSelections();
            applyNameMode();
            applyParkingTypeDetailMode();

            if (typeof toggleNonEmployeeExtraFields === 'function') {
                toggleNonEmployeeExtraFields();
            }
        });

        $(document).on('change', '#cpny_id, #department_id, #site_id_parking, #perpost', function () {
            if (shouldUseDropdownName()) {
                clearDropdownSelections();
                applyNameMode();
                applyParkingTypeDetailMode();
            }
        });
       
        $(document).on('change', '.employeeSelect', function () {
            const data = $(this).select2('data');
            const selected = data && data.length ? data[0] : null;

            const $row = $(this).closest('.parking-detail-row');

            const name = selected?.name || selected?.text || '';
            const nopol = selected?.nopol || '';
            const jenis = selected?.jenis_kendaraan || '';

            $row.find('.detailNameHidden').val(name || '');

            /*
            |--------------------------------------------------------------------------
            | OPRVEHICLES
            |--------------------------------------------------------------------------
            | Data dari MsKendaraan:
            | - name = namakendaraan
            | - nopol = no_polisi
            | - jenis = typekendaraan
            |--------------------------------------------------------------------------
            */
            if (isOprVehiclesWorkerType() && isNewOrTempParkingType()) {
                $row.find('.nopolFinalInput')
                    .val(nopol)
                    .prop('readonly', true)
                    .addClass('bg-gray-100 cursor-not-allowed');

                setJenisKendaraanValue($row, jenis);

                $row.find('.jenisFinalInput')
                    .addClass('bg-gray-100 cursor-not-allowed pointer-events-none')
                    .attr('tabindex', '-1');

                applyParkingTypeDetailMode();
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | RENEWAL / CHANGECARD / CHANGENOPOL
            |--------------------------------------------------------------------------
            | Data dari MsParkingKendaraan.
            |--------------------------------------------------------------------------
            */
            if (isRenewalType() || isChangeCardType() || isChangeNopolType()) {
                $row.find('.oldNopolHidden').val(nopol);
                $row.find('.oldJenisHidden').val(jenis);

                $row.find('.oldNopolDisplay').val(nopol);
                $row.find('.oldJenisDisplay').val(jenis);

                if (isChangeNopolType()) {
                    $row.find('.nopolFinalInput').val('').prop('readonly', false);
                    $row.find('.jenisFinalInput').val('').removeClass('pointer-events-none');
                } else {
                    $row.find('.nopolFinalInput').val(nopol);
                    setJenisKendaraanValue($row, jenis);
                }
            }

            applyParkingTypeDetailMode();
        });

        $(document).on('input change blur', '.manualNameInput', function () {
            const val = String($(this).val() || '').trim();

            $(this)
                .closest('.parking-detail-row')
                .find('.detailNameHidden')
                .val(val);
        });
      
        $(function () {
            refreshDetailRowNumber();
            applyNameMode();
            applyParkingTypeDetailMode();

            if (typeof toggleNonEmployeeExtraFields === 'function') {
                toggleNonEmployeeExtraFields();
            }

            $('#site_id_parking, #parking_type, #worker_type').select2({
                width: '100%'
            });
        });
    </script>

    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').text(text + '...');
            $ov.css('display', 'flex').hide().fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').fadeOut(120);
        }

        function toggleDeleteButton() {
            if ($('.attachment-row').length > 1) {
                $('.removeAttachment').removeClass('hidden');
            } else {
                $('.removeAttachment').addClass('hidden');
            }
        }

        $(document).on('click', '#addAttachment', function () {
            $('#attachmentsContainer').append(`
                <div class="attachment-row mt-2 flex items-center gap-2">
                    <input type="file" name="attachments[]"
                        class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                    <button type="button"
                        class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                </div>
            `);

            toggleDeleteButton();
        });

        $(document).on('click', '.removeAttachment', function () {
            $(this).closest('.attachment-row').remove();
            toggleDeleteButton();
        });

        $('#backBtn').on('click', function () {
            window.location.href = "{{ route('parkingregistration') }}";
        });

        function syncDetailNamesBeforeSubmit() {
            let isValid = true;
            let firstInvalid = null;

            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);
                const $empSelect = $row.find('.employeeSelect');
                const $manualInput = $row.find('.manualNameInput');
                const $hiddenName = $row.find('.detailNameHidden');

                let finalName = '';

                if (shouldUseDropdownName()) {
                    if ($empSelect.hasClass('select2-hidden-accessible')) {
                        const data = $empSelect.select2('data');
                        const selected = data && data.length ? data[0] : null;

                        finalName = selected?.name || selected?.text || '';
                    }

                    if (!finalName) {
                        finalName = $empSelect.find(':selected').data('name') ||
                            $empSelect.find(':selected').text() ||
                            '';
                    }

                    finalName = String(finalName).trim();

                    if (
                        finalName === '' ||
                        finalName === 'Select Employee' ||
                        finalName === 'Search name...'
                    ) {
                        isValid = false;
                        firstInvalid = firstInvalid || $empSelect;
                    }
                } else {
                    finalName = String($manualInput.val() || '').trim();

                    if (finalName === '') {
                        isValid = false;
                        firstInvalid = firstInvalid || $manualInput;
                    }
                }

                $hiddenName.val(finalName);
            });

            if (!isValid) {
                toastr.error('Mohon isi Name pada detail.');

                if (firstInvalid && firstInvalid.length) {
                    $('html, body').animate({
                        scrollTop: firstInvalid.closest('tr').offset().top - 120
                    }, 300);

                    firstInvalid.focus();
                }

                return false;
            }

            return true;
        }

        function submitParkingForm(isDraft) {
            if (!isDraft && !syncDetailNamesBeforeSubmit()) {
                return;
            }

            $('#isDraftField').val(isDraft ? '1' : '0');

            const $btnText = isDraft ? $('#draftBtnText') : $('#btnText');
            const $spinner = isDraft ? $('#draftLoadingSpinner') : $('#loadingSpinner');
            const idleText = isDraft ? 'Save as Draft' : 'Submit Approval';

            $('#submitBtn, #saveDraftBtn, #backBtn').prop('disabled', true);
            $btnText.text('Processing...');
            $spinner.removeClass('hidden');
            showOverlay(isDraft ? 'Saving draft' : 'Submitting');

            syncDetailUsernameBeforeSubmit();

            const formData = new FormData(document.getElementById('parkingRegistrationForm'));
            const selectedFileCount = Array.from(
                document.querySelectorAll('#parkingRegistrationForm input[type="file"]')
            ).reduce((total, input) => total + input.files.length, 0);

            formData.set('selected_file_count', String(selectedFileCount));

            $.ajax({
                url: "{{ route('parkingregistration.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false
            })
            .done(function (res) {
                toastr.success(res.message || (isDraft ? 'Parking Registration saved as draft.' : 'Parking Registration created successfully.'));

                setTimeout(function () {
                    window.location.href = "{{ route('parkingregistration') }}";
                }, 700);
            })
            .fail(function (xhr) {
                let msg = 'Error! Please check the input.';

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    msg = 'Mohon periksa input:<br>';

                    Object.keys(xhr.responseJSON.errors).forEach(function (key) {
                        msg += `- ${xhr.responseJSON.errors[key].join(', ')}<br>`;
                    });

                    toastr.error(msg);
                } else {
                    // Prioritas tampilkan error dulu, baru message
                    if (xhr.responseJSON?.error) {
                        msg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }

                    toastr.error(msg);
                }

                console.error(xhr.responseText);
            })
            .always(function () {
                $('#submitBtn, #saveDraftBtn, #backBtn').prop('disabled', false);
                $btnText.text(idleText);
                $spinner.addClass('hidden');
                hideOverlay();
            });
        }

        $('#parkingRegistrationForm').on('submit', function (e) {
            e.preventDefault();
            submitParkingForm(false);
        });

        $('#saveDraftBtn').on('click', function () {
            submitParkingForm(true);
        });

        $(function () {
            toggleDeleteButton();
        });
    </script>
</x-app-layout>
