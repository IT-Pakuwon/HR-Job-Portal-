{{--
    "Edit COA" popup of the show pages (SPPB, SPPJ, SPPK, SPPT, SPB, IM Budget Non-Purch).
    The slot holds one <x-coa-edit-row> per document line; the page script opens it, saves it and handles .btnPickCoa.
    A line counts as changed once its hidden .picked_account_id gets a value (pure CSS, no extra script).
--}}
<style>
    #editCoaModal { counter-reset: coa-changed; }
    #editCoaModal table { width: 100%; }
    #editCoaModal table thead th {
        position: sticky; top: 0; z-index: 1; padding: .625rem 1rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; white-space: nowrap;
    }
    #editCoaModal table tbody td { padding: .875rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; overflow-wrap: break-word; word-break: normal; }
    #editCoaModal table tbody tr:last-child td { border-bottom: 0; }

    /* the "new COA" box: dashed while untouched, blue once a COA was picked */
    #editCoaModal .coa-new { border: 1px dashed #d1d5db; background: #fff; }
    #editCoaModal .picked-coa-text { color: #9ca3af; }
    #editCoaModal .coa-flag { display: none; }
    #editCoaTableBody tr:has(.picked_account_id:not([value=""])) { counter-increment: coa-changed; }
    #editCoaTableBody tr:has(.picked_account_id:not([value=""])) .coa-new { border: 1px solid #93c5fd; background: #eff6ff; }
    #editCoaTableBody tr:has(.picked_account_id:not([value=""])) .picked-coa-text { color: #1e3a8a; font-weight: 600; }
    #editCoaTableBody tr:has(.picked_account_id:not([value=""])) .coa-flag { display: inline-flex; }
    #editCoaModal .coa-count::after { content: counter(coa-changed); }

    .dark #editCoaModal table thead th { background: #1e293b; border-color: rgba(255, 255, 255, .08); color: #9ca3af; }
    .dark #editCoaModal table tbody td { border-color: rgba(255, 255, 255, .06); }
    .dark #editCoaModal .coa-new { border-color: rgba(255, 255, 255, .15); background: transparent; }
    .dark #editCoaTableBody tr:has(.picked_account_id:not([value=""])) .coa-new { border-color: rgba(96, 165, 250, .6); background: rgba(59, 130, 246, .12); }
    .dark #editCoaTableBody tr:has(.picked_account_id:not([value=""])) .picked-coa-text { color: #bfdbfe; }
</style>

<div id="editCoaModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm dark:bg-black/70">
    <div class="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 dark:bg-[#0f172a] dark:ring-white/10">

        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-3.5 dark:border-white/[0.06]">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                <div class="min-w-0">
                    <h3 class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">Edit COA</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pick a new COA for the lines you want to change, then press Save.</p>
                </div>
            </div>
            <button type="button" id="btnCloseEditCoa" aria-label="Close"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-gray-200">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 p-4 dark:bg-[#0b1220]">
            <div class="overflow-x-auto rounded-xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                <table class="min-w-[760px] border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr>
                            <th class="w-[34%] text-left">Item</th>
                            <th class="w-[10%] text-center">Qty / UOM</th>
                            <th class="w-[20%] text-left">Current COA</th>
                            <th class="w-[36%] text-left">New COA</th>
                        </tr>
                    </thead>
                    <tbody id="editCoaTableBody">
                        {{ $slot }}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/70 px-6 py-3.5 dark:border-white/[0.06] dark:bg-white/[0.02]">
            <p class="text-xs text-gray-500 dark:text-gray-400">Lines changed: <span class="coa-count font-bold text-blue-600 dark:text-blue-300"></span></p>
            <div class="ml-auto flex gap-2">
                <button type="button" id="btnCancelEditCoa"
                    class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">
                    Cancel
                </button>
                <button type="button" id="btnSaveEditCoa"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fa-solid fa-floppy-disk"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
