{{-- One table of the process sheet (Process or Routing). Expects: $rows, $available (id => name),
     $label ('Process' | 'Routing'); $canUpdateDocs and $dash come from the including view. --}}
@php
    $inputCls = 'proc-edit agr-input proc-field hidden w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
    $addCls = 'agr-input proc-field w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
    $fmtDate = fn ($d) => $d ? $d->format('D, d M Y') : null;
@endphp

<div>
    @if ($label === 'Routing')
        <h3 class="mb-2 text-sm font-semibold text-slate-800 dark:text-slate-100">Routing</h3>
    @endif

    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-white/[0.06]">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                <tr>
                    <th class="px-3 py-2 text-left" style="min-width:170px;">{{ $label }}</th>
                    <th class="px-3 py-2 text-left" style="width:150px;">Start</th>
                    <th class="px-3 py-2 text-left" style="width:150px;">End</th>
                    <th class="px-3 py-2 text-left">Notes</th>
                    <th class="px-3 py-2 text-right" style="width:90px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $proc)
                    <tr class="border-t border-slate-100 align-middle dark:border-white/[0.06]" data-proc-row>
                        <td class="px-3 py-2">{{ $proc->descr }}</td>

                        {{-- Start: derived (Pengumpulan Dokumen) or picked --}}
                        <td class="px-3 py-2">
                            <span class="{{ $proc->start_locked ? '' : 'proc-static' }}">{!! $dash($fmtDate($proc->date_start)) !!}</span>
                            @unless ($proc->start_locked)
                                <input type="date" name="process[{{ $proc->process_id }}][date_start]" value="{{ optional($proc->date_start)->format('Y-m-d') }}"
                                    class="{{ $inputCls }}">
                            @endunless
                        </td>

                        {{-- End: set => the process is done --}}
                        <td class="px-3 py-2">
                            @if ($proc->date_end)
                                <span class="proc-static inline-flex items-center gap-1.5 text-green-700"><i class="fa-solid fa-circle-check"></i> {{ $fmtDate($proc->date_end) }}</span>
                            @else
                                <span class="proc-static text-slate-400">-</span>
                            @endif

                            <input type="date" name="process[{{ $proc->process_id }}][date_end]" value="{{ optional($proc->date_end)->format('Y-m-d') }}"
                                class="{{ $inputCls }}">
                        </td>

                        <td class="px-3 py-2">
                            <span class="proc-static whitespace-pre-line">{!! $dash($proc->note) !!}</span>
                            <input type="text" maxlength="2000" name="process[{{ $proc->process_id }}][note]" value="{{ $proc->note }}"
                                class="{{ $inputCls }}" placeholder="Notes">
                        </td>

                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            @if ($canUpdateDocs)
                                <button type="button" class="btn-proc-edit inline-flex h-7 w-7 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-[12px] text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-900/20 dark:text-blue-300" title="Update" aria-label="Update">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <span class="proc-row-actions hidden items-center gap-1.5">
                                    @if ($proc->removable)
                                        <button type="button" class="btn-proc-remove inline-flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 text-[12px] text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400" title="Remove" aria-label="Remove" data-process-id="{{ $proc->process_id }}"><i class="fa-solid fa-trash-can"></i></button>
                                    @endif
                                    <button type="button" class="btn-proc-cancel inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-[12px] text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300" title="Cancel" aria-label="Cancel"><i class="fa-solid fa-xmark"></i></button>
                                    <button type="button" class="btn-proc-save inline-flex h-7 w-7 items-center justify-center rounded-lg bg-green-600 text-[12px] text-white hover:bg-green-700" title="Save" aria-label="Save"><i class="fa-solid fa-floppy-disk"></i></button>
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

            {{-- Add row: pick the next one from the master list; it is added as its own row. --}}
            @if ($canUpdateDocs && count($available))
                <tfoot>
                    <tr class="proc-add-row border-t-2 border-dashed border-blue-200 align-bottom dark:border-blue-900/50" data-add-row>
                        <td class="px-3 py-3">
                            <div class="proc-add-label"><i class="fa-solid fa-plus text-[9px]"></i> Add {{ $label }}</div>
                            <select class="add-proc-id {{ $addCls }}">
                                <option value="">Choose...</option>
                                @foreach ($available as $pid => $pdescr)
                                    <option value="{{ $pid }}">{{ $pdescr }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-3">
                            <div class="proc-add-label">Start</div>
                            <input type="date" class="add-proc-start {{ $addCls }}" value="{{ now()->format('Y-m-d') }}">
                        </td>
                        <td class="px-3 py-3">
                            <div class="proc-add-label">End</div>
                            <input type="date" class="add-proc-end {{ $addCls }}" placeholder="Optional">
                        </td>
                        <td class="px-3 py-3">
                            <div class="proc-add-label">Notes</div>
                            <input type="text" maxlength="2000" class="add-proc-note {{ $addCls }}" placeholder="Optional">
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <button type="button" class="btn-proc-add inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700" style="height:34px;" title="Add {{ $label }}">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
