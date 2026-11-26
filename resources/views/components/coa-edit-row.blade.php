{{-- One document line inside <x-coa-edit-modal>. The data-* values and picked_* inputs are read by the page script. --}}
@props([
    'rowId',
    'cpny' => null,
    'bu' => null,
    'deptfin' => null,
    'perpost' => null,
    'descr' => null,
    'note' => null,
    'location' => null,
    'qty' => 0,
    'uom' => null,
    'account' => null,
    'activity' => null,
])

<tr data-row-id="{{ $rowId }}" data-cpny="{{ $cpny }}" data-bu="{{ $bu }}" data-deptfin="{{ $deptfin }}"
    data-dept="{{ $deptfin }}" data-perpost="{{ $perpost }}">

    <td>
        <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $descr }}</div>
        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Note : {{ $note ?: '-' }}</div>
        @if (!is_null($location))
            <div class="mt-0.5 flex items-start gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-location-dot mt-1 text-[10px] text-gray-400"></i><span>{{ $location }}</span>
            </div>
        @endif
    </td>

    <td class="whitespace-nowrap text-center">
        <div class="font-semibold tabular-nums text-gray-800 dark:text-gray-100">{{ number_format((float) $qty, 2, ',', '.') }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $uom }}</div>
    </td>

    <td>
        @if ($account)
            <span class="inline-block rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold tabular-nums text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $account }}</span>
        @endif
        <div class="mt-1 text-[13px] text-gray-700 dark:text-gray-300">{{ $activity ?: '-' }}</div>
    </td>

    <td>
        <div class="flex items-center gap-3">
            <div class="coa-new flex min-h-12 min-w-0 flex-1 items-center gap-2 rounded-lg px-3 py-2">
                <span class="coa-flag shrink-0 items-center rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">New</span>
                <span class="picked-coa-text min-w-0 break-words text-[13px]">Not changed</span>
            </div>

            <button type="button" data-row-id="{{ $rowId }}"
                class="btnPickCoa inline-flex h-9 shrink-0 items-center gap-2 whitespace-nowrap rounded-lg border border-blue-100 bg-blue-50 px-3.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white">
                <i class="fa-solid fa-magnifying-glass text-[11px]"></i> Pick COA
            </button>
        </div>

        {{-- new values, filled by the picker and sent on Save --}}
        <input type="hidden" class="picked_account_id" value="">
        <input type="hidden" class="picked_activity_descr" value="">
        <input type="hidden" class="picked_activity_id" value="">
        <input type="hidden" class="picked_business_unit_id" value="">
        <input type="hidden" class="picked_department_fin_id" value="">
    </td>
</tr>
