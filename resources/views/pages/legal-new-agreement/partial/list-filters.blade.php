{{-- Company + Type filter and Export for the Active / Completed lists, all in one row.
     Changing a filter reloads that tab's table; Export downloads what the table shows.
     Widths sit on wrappers: .agr-input sets width:100% outside Tailwind's layers, so a
     w-* utility on the select itself would lose. --}}
<div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center dark:border-white/[0.06] dark:bg-white/[0.02]">
    <div class="min-w-0 flex-1">
        <select id="{{ $prefix }}_cpny_filter" data-list-filter="{{ $prefix }}" data-key="cpny_id" class="agr-input agr-select2">
            <option value="">All Companies</option>
            @foreach ($allCompanies as $c)
                <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="min-w-0 flex-1">
        <select id="{{ $prefix }}_property_filter" data-list-filter="{{ $prefix }}" data-key="property_cd" class="agr-input agr-select2">
            <option value="">All Types</option>
            <option value="OFF">Office (OLA)</option>
            <option value="MALL">Mall (PSM)</option>
        </select>
    </div>

    <button type="button" data-list-export="{{ $prefix }}" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 hover:bg-green-100 dark:border-green-800/60 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30">
        <i class="fa-solid fa-file-excel"></i> Export to Excel
    </button>
</div>
