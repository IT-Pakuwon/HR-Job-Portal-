{{-- Header of a New Agreement list card: title, Company + Type filter and Export, all in one row.
     Changing a filter reloads that tab's table; Export downloads what the table shows.
     Widths sit on wrappers: .agr-input sets width:100% outside Tailwind's layers, so a
     w-* utility on the select itself would lose. --}}
<x-list-page-header :title="$title" :subtitle="$subtitle">
    <div class="w-full sm:w-60">
        <select id="{{ $prefix }}_cpny_filter" data-list-filter="{{ $prefix }}" data-key="cpny_id" class="agr-input agr-select2">
            <option value="">All Companies</option>
            @foreach ($allCompanies as $c)
                <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="w-full sm:w-44">
        <select id="{{ $prefix }}_property_filter" data-list-filter="{{ $prefix }}" data-key="property_cd" class="agr-input agr-select2">
            <option value="">All Types</option>
            <option value="OFF">Office (OLA)</option>
            <option value="MALL">Mall (PSM)</option>
        </select>
    </div>

    @unless ($noExport ?? false)
    <button type="button" data-list-export="{{ $prefix }}" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 transition hover:bg-green-100 dark:border-green-800/60 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30">
        <i class="fa-solid fa-file-excel"></i> Export to Excel
    </button>
    @endunless
</x-list-page-header>
