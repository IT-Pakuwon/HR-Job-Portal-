<x-app-layout>
    <style>
        .parking-detail-table {
            min-width: 1600px;
            table-layout: fixed;
        }

        .parking-file-input {
            width: 100%;
            min-width: 220px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: #ffffff;
            padding: 0.35rem;
            font-size: 0.875rem;
            color: #374151;
        }

        .parking-file-input::file-selector-button {
            margin-right: 0.75rem;
            border: 0;
            border-radius: 9999px;
            background: #e0e7ff;
            color: #4338ca;
            padding: 0.45rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .parking-file-input:hover::file-selector-button {
            background: #c7d2fe;
        }

        .dark .parking-file-input {
            border-color: #4b5563;
            background: #374151;
            color: #d1d5db;
        }

        .dark .parking-file-input::file-selector-button {
            background: #4338ca;
            color: #ffffff;
        }

        .dark .parking-file-input:hover::file-selector-button {
            background: #4f46e5;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">
        <form id="parkingRegistrationForm" class="flex flex-col gap-6" enctype="multipart/form-data">
            @csrf

            <input type="hidden" id="docid" value="{{ $parkingRegistration->docid }}">

            {{-- HEADER --}}
            <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                        Edit Parking Registration - {{ $parkingRegistration->docid }}
                    </h2>
                </div>

                {{-- BARIS 1 --}}
                <div class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-6">

                    {{-- Company --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Company
                        </label>
                        <select name="cpny_id" id="cpny_id"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpny_id }}"
                                    {{ $parkingRegistration->cpny_id == $p->cpny_id ? 'selected' : '' }}>
                                    {{ $p->cpny_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Department
                        </label>
                        <select name="department_id" id="department_id"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select Department</option>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->department_id }}"
                                    {{ $parkingRegistration->department_id == $p->department_id ? 'selected' : '' }}>
                                    {{ $p->department_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Periode
                        </label>

                        @php
                            $currentYear = now()->format('Y');
                            $nextYear = now()->addYear()->format('Y');
                            $editYear = $parkingRegistration->perpost;
                        @endphp

                        <select name="perpost" id="perpost"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select Year</option>

                            <option value="{{ $currentYear }}" {{ $editYear == $currentYear ? 'selected' : '' }}>
                                {{ $currentYear }}
                            </option>

                            <option value="{{ $nextYear }}" {{ $editYear == $nextYear ? 'selected' : '' }}>
                                {{ $nextYear }}
                            </option>

                            @if ($editYear && !in_array($editYear, [$currentYear, $nextYear]))
                                <option value="{{ $editYear }}" selected>
                                    {{ $editYear }}
                                </option>
                            @endif
                        </select>
                    </div>

                    {{-- Site ID Parking --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Site ID Parking
                        </label>
                        <select name="site_id_parking" id="site_id_parking"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select Site Parking</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->siteid }}"
                                    {{ $parkingRegistration->site_id_parking == $site->siteid ? 'selected' : '' }}>
                                    {{ $site->siteid }} - {{ $site->site_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Parking Type --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parking Type
                        </label>
                        <select name="parking_type" id="parking_type"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select Parking Type</option>
                            @foreach ($parkingTypes as $row)
                                <option value="{{ $row->categoryid }}"
                                    {{ $parkingRegistration->parking_type == $row->categoryid ? 'selected' : '' }}>
                                    {{ $row->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Worker Type --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Worker Type
                        </label>
                        <select name="worker_type" id="worker_type"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select Worker Type</option>
                            @foreach ($workerTypes as $row)
                                <option value="{{ $row->categoryid }}"
                                    {{ $parkingRegistration->worker_type == $row->categoryid ? 'selected' : '' }}>
                                    {{ $row->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Date Range + Info: tampil hanya jika Worker Type bukan EMPLOYEE --}}
                @php
                    $firstDetail = $parkingRegistrationDetail->first();
                @endphp

                <div id="nonEmployeeExtraSection" class="mt-6 hidden grid grid-cols-1 gap-6 lg:grid-cols-2">

                    {{-- Date Range --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Date Range
                        </label>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 dark:text-gray-400">Start Date</label>
                                <input type="date" name="startdate" id="startdate"
                                    value="{{ $firstDetail?->startdate }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 dark:text-gray-400">End Date</label>
                                <input type="date" name="enddate" id="enddate"
                                    value="{{ $firstDetail?->enddate }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="flex flex-col gap-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Info
                        </label>

                        <textarea name="info" id="info" rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="Input info...">{{ $parkingRegistration->info }}</textarea>
                    </div>
                </div>
            </div>

            {{-- DETAIL --}}
            <div id="detailSection" class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <details class="group" open>
                    <summary
                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                        <span>Detail</span>
                        <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details →</span>
                        <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details ↓</span>
                    </summary>

                    <div class="flex h-auto flex-col justify-start pt-4">
                        <div class="overflow-x-auto">
                            <table class="parking-detail-table mb-4 mt-3">
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

                                <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="border p-3 text-center">No</th>
                                        <th class="req border p-3 text-left">Name</th>
                                        <th class="req border p-3 text-left">No Polisi</th>
                                        <th class="req border p-3 text-left">Jenis Kendaraan</th>
                                        <th class="stnkHeader req border p-3 text-left">Attach STNK</th>
                                        <th class="idcardHeader req border p-3 text-left">Attach ID Card</th>
                                        <th class="buktiBayarHeader border p-3 text-left">Attach Bukti Bayar</th>
                                        <th class="border p-3 text-center"></th>
                                    </tr>
                                </thead>

                                <tbody id="parkingDetailTable">
                                    @forelse ($parkingRegistrationDetail as $i => $d)
                                        <tr class="parking-detail-row">
                                            <td class="row-number border p-3 text-center align-middle">{{ $i + 1 }}</td>

                                            <td class="border p-3">
                                                <select name="detail_username[]"
                                                    class="employeeSelect hidden w-full rounded border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                    @if ($d->username)
                                                        <option value="{{ $d->username }}" selected>{{ $d->nama }}</option>
                                                    @endif
                                                </select>

                                                <input type="text" name="detail_name_manual[]"
                                                    value="{{ $d->nama }}"
                                                    class="manualNameInput w-full rounded border border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    placeholder="Input name...">

                                                <input type="hidden" name="detail_name[]" class="detailNameHidden" value="{{ $d->nama }}">
                                            </td>

                                            <td class="border p-3">
                                                <input type="hidden" name="detail_nopol_lama[]" class="oldNopolHidden" value="{{ $d->nopol_lama }}">

                                                <div class="oldNopolWrapper mb-2 hidden">
                                                    <label class="mb-1 block text-xs font-semibold text-gray-500">
                                                        No Polisi Lama
                                                    </label>
                                                    <input type="text"
                                                        value="{{ $d->nopol_lama }}"
                                                        class="oldNopolDisplay w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm uppercase text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        readonly>
                                                </div>

                                                <label class="nopolFinalLabel mb-1 hidden text-xs font-semibold text-gray-500">
                                                    No Polisi Baru
                                                </label>

                                                <input type="text" name="detail_no_polisi[]"
                                                    value="{{ $d->nopol }}"
                                                    class="nopolFinalInput w-full rounded border border-gray-300 bg-white p-2 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    placeholder="B 1234 ABC" required>
                                            </td>

                                            <td class="border p-3">
                                                <input type="hidden" name="detail_jenis_lama[]" class="oldJenisHidden" value="{{ $d->jenis_lama }}">

                                                <div class="oldJenisWrapper mb-2 hidden">
                                                    <label class="mb-1 block text-xs font-semibold text-gray-500">
                                                        Jenis Kendaraan Lama
                                                    </label>
                                                    <input type="text"
                                                        value="{{ $d->jenis_lama }}"
                                                        class="oldJenisDisplay w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        readonly>
                                                </div>

                                                <label class="jenisFinalLabel mb-1 hidden text-xs font-semibold text-gray-500">
                                                    Jenis Kendaraan Baru
                                                </label>

                                                <select name="detail_jenis_kendaraan[]"
                                                    class="jenisFinalInput w-full rounded border border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    required>
                                                    <option value="">Select</option>
                                                    <option value="Motor" {{ $d->jenis_kendaraan == 'Motor' ? 'selected' : '' }}>Motor</option>
                                                    <option value="Mobil" {{ $d->jenis_kendaraan == 'Mobil' ? 'selected' : '' }}>Mobil</option>
                                                </select>
                                            </td>

                                            <td class="stnkCell border p-3">
                                                <input type="hidden" name="old_attach_stnk[]" value="{{ $d->attach_stnk }}">
                                                @if ($d->attach_stnk_url)
                                                    <a href="{{ $d->attach_stnk_url }}" target="_blank"
                                                        class="mb-2 inline-block rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 hover:underline">
                                                        📎 Current STNK
                                                    </a>
                                                @endif

                                                <input type="file" name="detail_attach_stnk[]" class="attachStnkInput parking-file-input">
                                            </td>

                                            <td class="idcardCell border p-3">
                                                <input type="hidden" name="old_attach_idcard[]" value="{{ $d->attach_idcard }}">
                                                @if ($d->attach_idcard_url)
                                                    <a href="{{ $d->attach_idcard_url }}" target="_blank"
                                                        class="mb-2 inline-block rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 hover:underline">
                                                        📎 Current ID Card
                                                    </a>
                                                @endif

                                                <input type="file" name="detail_attach_idcard[]" class="attachIdcardInput parking-file-input">
                                            </td>

                                            <td class="buktiBayarCell border p-3">
                                                <input type="hidden" name="old_attach_bukti_bayar[]" value="{{ $d->attach_bukti_bayar }}">
                                                @if ($d->attach_bukti_bayar_url)
                                                    <a href="{{ $d->attach_bukti_bayar_url }}" target="_blank"
                                                        class="mb-2 inline-block rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 hover:underline">
                                                        📎 Current Bukti Bayar
                                                    </a>
                                                @endif

                                                <input type="file" name="detail_attach_bukti_bayar[]" class="attachBuktiBayarInput parking-file-input">
                                            </td>

                                            <td class="border p-3 text-center align-middle">
                                                <button type="button"
                                                    class="removeParkingDetail rounded border border-red-500 px-2 py-2 text-red-500 hover:bg-red-50">
                                                    🗑️
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="parking-detail-row">
                                            <td class="row-number border p-3 text-center align-middle">1</td>

                                            <td class="border p-3">
                                                <select name="detail_username[]"
                                                    class="employeeSelect hidden w-full rounded border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                </select>

                                                <input type="text" name="detail_name_manual[]"
                                                    class="manualNameInput w-full rounded border border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    placeholder="Input name...">

                                                <input type="hidden" name="detail_name[]" class="detailNameHidden">
                                            </td>

                                            <td class="border p-3">
                                                <input type="hidden" name="detail_nopol_lama[]" class="oldNopolHidden">

                                                <div class="oldNopolWrapper mb-2 hidden">
                                                    <label class="mb-1 block text-xs font-semibold text-gray-500">
                                                        No Polisi Lama
                                                    </label>
                                                    <input type="text"
                                                        class="oldNopolDisplay w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm uppercase text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        readonly>
                                                </div>

                                                <label class="nopolFinalLabel mb-1 hidden text-xs font-semibold text-gray-500">
                                                    No Polisi Baru
                                                </label>

                                                <input type="text" name="detail_no_polisi[]"
                                                    class="nopolFinalInput w-full rounded border border-gray-300 bg-white p-2 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    placeholder="B 1234 ABC" required>
                                            </td>

                                            <td class="border p-3">
                                                <input type="hidden" name="detail_jenis_lama[]" class="oldJenisHidden">

                                                <div class="oldJenisWrapper mb-2 hidden">
                                                    <label class="mb-1 block text-xs font-semibold text-gray-500">
                                                        Jenis Kendaraan Lama
                                                    </label>
                                                    <input type="text"
                                                        class="oldJenisDisplay w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        readonly>
                                                </div>

                                                <label class="jenisFinalLabel mb-1 hidden text-xs font-semibold text-gray-500">
                                                    Jenis Kendaraan Baru
                                                </label>

                                                <select name="detail_jenis_kendaraan[]"
                                                    class="jenisFinalInput w-full rounded border border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    required>
                                                    <option value="">Select</option>
                                                    <option value="Motor">Motor</option>
                                                    <option value="Mobil">Mobil</option>
                                                </select>
                                            </td>

                                            <td class="stnkCell border p-3">
                                                <input type="hidden" name="old_attach_stnk[]" value="">
                                                <input type="file" name="detail_attach_stnk[]" class="attachStnkInput parking-file-input">
                                            </td>

                                            <td class="idcardCell border p-3">
                                                <input type="hidden" name="old_attach_idcard[]" value="">
                                                <input type="file" name="detail_attach_idcard[]" class="attachIdcardInput parking-file-input">
                                            </td>

                                            <td class="buktiBayarCell border p-3">
                                                <input type="hidden" name="old_attach_bukti_bayar[]" value="">
                                                <input type="file" name="detail_attach_bukti_bayar[]" class="attachBuktiBayarInput parking-file-input">
                                            </td>

                                            <td class="border p-3 text-center align-middle">
                                                <button type="button"
                                                    class="removeParkingDetail hidden rounded border border-red-500 px-2 py-2 text-red-500 hover:bg-red-50">
                                                    🗑️
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="addParkingDetail"
                            class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
            <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <details class="group" open>
                    <summary
                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                        <span>Attachments <span class="text-sm font-normal text-gray-500">(Optional)</span></span>
                        <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">
                            See details →
                        </span>
                        <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">
                            Hide details ↓
                        </span>
                    </summary>

                    <div class="flex flex-col pt-6">
                        <div id="attachmentsContainer">
                            <div class="attachment-row flex items-center gap-2">
                                <input type="file" name="attachments[]"
                                    class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                <button type="button"
                                    class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <button type="button" id="addAttachment"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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

                <div class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                    <button type="button" id="backBtn"
                        class="flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Back</span>
                    </button>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <button type="button" id="cancelBtn"
                            class="flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            <i class="fa-solid fa-ban"></i>
                            <span>Cancel</span>
                        </button>

                        <button type="submit" id="submitBtn"
                            class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Update Approval</span>
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
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

        function isOprVehiclesWorkerType() {
            return currentWorkerType() === 'OPRVEHICLES';
        }

        function isNewOrTempParkingType() {
            return ['NEWREQUEST', 'TEMPREQUEST'].includes(currentParkingType());
        }

        function isOprVehiclesNewOrTemp() {
            return isOprVehiclesWorkerType() && isNewOrTempParkingType();
        }

        function setFileCell($cell, $input, show, required = false) {
            const hasOld = String($cell.find('input[type="hidden"][name^="old_attach"]').val() || '').trim() !== '';

            if (show) {
                $cell.removeClass('hidden').show();
                $input.prop('required', required && !hasOld);
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
        }

        function resetParkingDetailRows() {
            $('#parkingDetailTable .employeeSelect').each(function () {
                destroyEmployeeSelect2($(this));
            });

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

            /*
            |--------------------------------------------------------------------------
            | Default NEWREQUEST / TEMPREQUEST
            |--------------------------------------------------------------------------
            */
            setFileHeader(true, true, true);

            /*
            |--------------------------------------------------------------------------
            | RENEWAL
            |--------------------------------------------------------------------------
            */
            if (parkingType === 'RENEWAL') {
                setFileHeader(false, false, false);
            }

            /*
            |--------------------------------------------------------------------------
            | CHANGENOPOL
            |--------------------------------------------------------------------------
            */
            if (parkingType === 'CHANGENOPOL') {
                setFileHeader(true, false, true);
            }

            /*
            |--------------------------------------------------------------------------
            | CHANGECARD / Pergantian Kartu
            | Attach STNK dan Attach ID Card hidden
            |--------------------------------------------------------------------------
            */
            if (parkingType === 'CHANGECARD') {
                setFileHeader(false, false, true);
            }

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

                setFileCell($stnkCell, $stnkInput, true, true);
                setFileCell($idcardCell, $idcardInput, true, true);
                setFileCell($buktiCell, $buktiInput, true, false);

                /*
                |--------------------------------------------------------------------------
                | RENEWAL
                |--------------------------------------------------------------------------
                */
                if (parkingType === 'RENEWAL') {
                    setReadonlyVehicle($row, true);

                    setFileCell($stnkCell, $stnkInput, false, false);
                    setFileCell($idcardCell, $idcardInput, false, false);
                    setFileCell($buktiCell, $buktiInput, false, false);
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

                    setFileCell($stnkCell, $stnkInput, true, true);
                    setFileCell($idcardCell, $idcardInput, false, false);
                    setFileCell($buktiCell, $buktiInput, true, false);
                }

                /*
                |--------------------------------------------------------------------------
                | CHANGECARD / Pergantian Kartu
                |--------------------------------------------------------------------------
                */
                if (parkingType === 'CHANGECARD') {
                    setReadonlyVehicle($row, true);

                    setFileCell($stnkCell, $stnkInput, false, false);
                    setFileCell($idcardCell, $idcardInput, false, false);
                    setFileCell($buktiCell, $buktiInput, true, false);
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

        function isExistingParkingType() {
            const parkingType = currentParkingType();

            return ['RENEWAL', 'CHANGECARD', 'CHANGENOPOL'].includes(parkingType);
        }

        function shouldUseDropdownName() {
            /*
            |--------------------------------------------------------------------------
            | Dropdown Name dipakai untuk:
            | 1. EMPLOYEE
            | 2. OPRVEHICLES khusus NEWREQUEST / TEMPREQUEST dari MsKendaraan
            | 3. RENEWAL / CHANGECARD / CHANGENOPOL dari MsParkingKendaraan
            |--------------------------------------------------------------------------
            */
            return isEmployeeWorkerType()
                || isOprVehiclesNewOrTemp()
                || isExistingParkingType();
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
                        .prop('required', false);

                    $empSelect
                        .removeClass('hidden')
                        .prop('required', true);

                    initEmployeeSelect2($row);

                    const selectedText = String($empSelect.find(':selected').text() || '').trim();
                    if (selectedText && selectedText !== 'Select Employee') {
                        $hiddenName.val(selectedText);
                    }
                } else {
                    destroyEmployeeSelect2($empSelect);

                    $empSelect
                        .addClass('hidden')
                        .prop('required', false)
                        .val('');

                    $manualInput
                        .removeClass('hidden')
                        .prop('required', true);

                    if ($hiddenName.val()) {
                        $manualInput.val($hiddenName.val());
                    } else {
                        $hiddenName.val(String($manualInput.val() || '').trim());
                    }
                }
            });
        }

        function cleanSelectedUsername(raw) {
            raw = String(raw || '');

            /*
            |--------------------------------------------------------------------------
            | OPRVEHICLES berasal dari MsKendaraan, bukan User.
            | Jadi username dikirim kosong/null.
            |--------------------------------------------------------------------------
            */
            if (raw.startsWith('OPRVEHICLES|')) {
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

                    if (!finalName) {
                        finalName = $empSelect.find(':selected').data('name') ||
                            $empSelect.find(':selected').text() ||
                            $hiddenName.val() ||
                            '';
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

                    firstInvalid.focus();
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
                    <td class="row-number border p-3 text-center align-middle">1</td>

                    <td class="border p-3">
                        <select name="detail_username[]"
                            class="employeeSelect hidden w-full rounded border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </select>

                        <input type="text" name="detail_name_manual[]"
                            class="manualNameInput w-full rounded border border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="Input name...">

                        <input type="hidden" name="detail_name[]" class="detailNameHidden">
                    </td>

                    <td class="border p-3">
                        <input type="hidden" name="detail_nopol_lama[]" class="oldNopolHidden">

                        <div class="oldNopolWrapper mb-2 hidden">
                            <label class="mb-1 block text-xs font-semibold text-gray-500">No Polisi Lama</label>
                            <input type="text"
                                class="oldNopolDisplay w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm uppercase text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                readonly>
                        </div>

                        <label class="nopolFinalLabel mb-1 hidden text-xs font-semibold text-gray-500">
                            No Polisi Baru
                        </label>

                        <input type="text" name="detail_no_polisi[]"
                            class="nopolFinalInput w-full rounded border border-gray-300 bg-white p-2 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="B 1234 ABC" required>
                    </td>

                    <td class="border p-3">
                        <input type="hidden" name="detail_jenis_lama[]" class="oldJenisHidden">

                        <div class="oldJenisWrapper mb-2 hidden">
                            <label class="mb-1 block text-xs font-semibold text-gray-500">Jenis Kendaraan Lama</label>
                            <input type="text"
                                class="oldJenisDisplay w-full rounded border border-gray-300 bg-gray-100 p-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                readonly>
                        </div>

                        <label class="jenisFinalLabel mb-1 hidden text-xs font-semibold text-gray-500">
                            Jenis Kendaraan Baru
                        </label>

                        <select name="detail_jenis_kendaraan[]"
                            class="jenisFinalInput w-full rounded border border-gray-300 bg-white p-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">Select</option>
                            <option value="Motor">Motor</option>
                            <option value="Mobil">Mobil</option>
                        </select>
                    </td>

                    <td class="stnkCell border p-3">
                        <input type="hidden" name="old_attach_stnk[]" value="">
                        <input type="file" name="detail_attach_stnk[]" class="attachStnkInput parking-file-input">
                    </td>

                    <td class="idcardCell border p-3">
                        <input type="hidden" name="old_attach_idcard[]" value="">
                        <input type="file" name="detail_attach_idcard[]" class="attachIdcardInput parking-file-input">
                    </td>

                    <td class="buktiBayarCell border p-3">
                        <input type="hidden" name="old_attach_bukti_bayar[]" value="">
                        <input type="file" name="detail_attach_bukti_bayar[]" class="attachBuktiBayarInput parking-file-input">
                    </td>

                    <td class="border p-3 text-center align-middle">
                        <button type="button"
                            class="removeParkingDetail rounded border border-red-500 px-2 py-2 text-red-500 hover:bg-red-50">
                            🗑️
                        </button>
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

        function toggleNonEmployeeExtraFields() {
            const parkingType = currentParkingType();
            const workerType  = String($('#worker_type').val() || '').trim();
            const isEmp       = isEmployeeWorkerType();

            /*
            |--------------------------------------------------------------------------
            | Date Range + Info tampil jika:
            | 1. Worker Type selain EMPLOYEE
            | 2. Parking Type TEMPREQUEST + Worker Type EMPLOYEE/Karyawan
            |--------------------------------------------------------------------------
            */
            const showExtraSection =
                workerType !== '' &&
                (
                    !isEmp ||
                    (parkingType === 'TEMPREQUEST' && isEmp)
                );

            if (showExtraSection) {
                $('#nonEmployeeExtraSection').removeClass('hidden').show();

                $('#startdate').prop('required', true);
                $('#enddate').prop('required', true);
            } else {
                $('#nonEmployeeExtraSection').addClass('hidden').hide();

                $('#startdate').prop('required', false);
                $('#enddate').prop('required', false);
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
            | OPRVEHICLES + NEWREQUEST / TEMPREQUEST
            |--------------------------------------------------------------------------
            | Source dari MsKendaraan:
            | - text/name = namakendaraan
            | - nopol = no_polisi
            | - jenis = typekendaraan
            |--------------------------------------------------------------------------
            */
            if (isOprVehiclesNewOrTemp()) {
                $row.find('.oldNopolHidden').val('');
                $row.find('.oldJenisHidden').val('');
                $row.find('.oldNopolDisplay').val('');
                $row.find('.oldJenisDisplay').val('');

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
            | Source tetap dari MsParkingKendaraan.
            |--------------------------------------------------------------------------
            */
            if (isRenewalType() || isChangeCardType() || isChangeNopolType()) {
                if (nopol) {
                    $row.find('.oldNopolHidden').val(nopol);
                    $row.find('.oldNopolDisplay').val(nopol);
                }

                if (jenis) {
                    $row.find('.oldJenisHidden').val(jenis);
                    $row.find('.oldJenisDisplay').val(jenis);
                }

                if (isChangeNopolType()) {
                    $row.find('.nopolFinalInput')
                        .val('')
                        .prop('readonly', false)
                        .removeClass('bg-gray-100 cursor-not-allowed');

                    $row.find('.jenisFinalInput')
                        .val('')
                        .removeClass('bg-gray-100 cursor-not-allowed pointer-events-none')
                        .removeAttr('tabindex');
                } else {
                    if (nopol) {
                        $row.find('.nopolFinalInput').val(nopol);
                    }

                    if (jenis) {
                        setJenisKendaraanValue($row, jenis);
                    }
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

            $('#parkingDetailTable .parking-detail-row').each(function () {
                const $row = $(this);
                const name = $row.find('.detailNameHidden').val();

                if (!shouldUseDropdownName()) {
                    $row.find('.manualNameInput').val(name);
                }

                /*
                |--------------------------------------------------------------------------
                | OPRVEHICLES + NEWREQUEST / TEMPREQUEST
                |--------------------------------------------------------------------------
                | No Polisi dan Jenis Kendaraan readonly karena dari MsKendaraan.
                |--------------------------------------------------------------------------
                */
                if (isOprVehiclesNewOrTemp()) {
                    const selectedData = $row.find('.employeeSelect').hasClass('select2-hidden-accessible')
                        ? $row.find('.employeeSelect').select2('data')
                        : [];

                    const selected = selectedData && selectedData.length ? selectedData[0] : null;

                    if (selected) {
                        $row.find('.nopolFinalInput')
                            .prop('readonly', true)
                            .addClass('bg-gray-100 cursor-not-allowed');

                        $row.find('.jenisFinalInput')
                            .addClass('bg-gray-100 cursor-not-allowed pointer-events-none')
                            .attr('tabindex', '-1');
                    }
                }
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
                        class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700
                               file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700
                               hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button"
                        class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        🗑️
                    </button>
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

        $('#cancelBtn').on('click', function () {
            Swal.fire({
                title: 'Anda yakin mau cancel?',
                text: 'Dokumen akan diubah menjadi Cancelled.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cancel',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $('#cancelBtn, #submitBtn, #backBtn').prop('disabled', true);
                showOverlay('Cancelling');

                $.ajax({
                    url: "{{ route('parkingregistration.cancel', $parkingRegistration->docid) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "PUT"
                    }
                })
                .done(function (res) {
                    toastr.success(res.message || 'Parking Registration cancelled successfully.');

                    setTimeout(function () {
                        window.location.href = "{{ route('parkingregistration') }}";
                    }, 700);
                })
                .fail(function (xhr) {
                    let msg = 'Failed to cancel Parking Registration.';

                    if (xhr.responseJSON?.error) {
                        msg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }

                    toastr.error(msg);
                    console.error(xhr.responseText);
                })
                .always(function () {
                    $('#cancelBtn, #submitBtn, #backBtn').prop('disabled', false);
                    hideOverlay();
                });
            });
        });

        $('#parkingRegistrationForm').on('submit', function (e) {
            e.preventDefault();

            if (!syncDetailNamesBeforeSubmit()) {
                return;
            }

            $('#submitBtn, #backBtn').prop('disabled', true);
            $('#btnText').text('Processing...');
            $('#loadingSpinner').removeClass('hidden');
            showOverlay('Updating');

            syncDetailUsernameBeforeSubmit();

            const formData = new FormData(document.getElementById('parkingRegistrationForm'));

            formData.append('_method', 'PUT');
            
            $.ajax({
                url: "{{ route('parkingregistration.update', $parkingRegistration->docid) }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false
            })
            .done(function (res) {
                toastr.success(res.message || 'Parking Registration updated successfully.');

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
                $('#submitBtn, #backBtn').prop('disabled', false);
                $('#btnText').text('Update Approval');
                $('#loadingSpinner').addClass('hidden');
                hideOverlay();
            });
        });

        $(function () {
            toggleDeleteButton();
        });
    </script>
</x-app-layout>