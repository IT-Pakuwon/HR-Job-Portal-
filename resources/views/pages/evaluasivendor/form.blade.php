<x-app-layout>
    <div class="mx-auto w-full space-y-3 p-2 md:px-4">
        @php
            $creating = !$evaluation->exists;
            $lbl = 'flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
            $ctl = 'block w-full rounded-lg border border-gray-300 bg-white text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100';
            $box = 'height:2.5rem;line-height:1.25rem;padding:0 .75rem;';
            $sel = $box . "appearance:none;-webkit-appearance:none;padding-right:2.25rem;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right .75rem center;background-size:1rem;";
        @endphp
        <div class="flex items-center gap-3">
            <a href="{{ route('evaluasivendor') }}" title="Kembali ke Penilaian Vendor" aria-label="Kembali ke Penilaian Vendor" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg font-bold leading-tight">{{ $creating ? 'Input Penilaian Vendor' : 'Edit Penilaian Vendor' }}</h1>
                    @unless($creating)<span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">{{ $evaluation->vendor_perfomance_id }}</span>@endunless
                </div>
                <p class="text-xs text-gray-500">Penilaian Vendor &rsaquo; {{ $creating ? 'Input baru' : 'Edit' }}</p>
            </div>
        </div>
        @if(session('success'))<div role="status" class="rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div role="alert" class="rounded-lg bg-red-50 p-3 text-red-700"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if(!$creating && $evaluation->status === 'D')
            <div class="rounded-lg border border-orange-300 bg-orange-50 p-3 text-sm text-orange-800"><strong>Penilaian perlu direvisi.</strong><p class="mt-1 whitespace-pre-line">{{ $evaluation->template_vpe_aspect_comment ?: 'Periksa dan perbaiki informasi penilaian.' }}</p><p class="mt-2">Perbarui informasi dan skor, lalu submit ulang untuk approval.</p></div>
        @endif
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 4h7l5 5v11a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
                    Informasi Dokumen
                </h2>
                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-bold tracking-wide text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">{{ $evaluation->vendor_perfomance_type }}</span>
            </div>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 text-sm sm:grid-cols-2 xl:grid-cols-3">
                <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">No. Dokumen</dt><dd class="mt-1 font-semibold">{{ $evaluation->ponbr }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Company / Department</dt><dd class="mt-1">{{ $evaluation->cpny_id }} <span class="text-gray-400">/</span> {{ $evaluation->department_id ?: '—' }}</dd></div>
                <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Vendor</dt><dd class="mt-1 font-semibold">{{ $evaluation->vendorname }}</dd></div>
                <div class="sm:col-span-2 xl:col-span-3"><dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Pekerjaan</dt><dd class="mt-1 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $evaluation->keperluan }}</dd></div>
            </dl>
        </section>
        <form method="POST" id="evaluation-form" action="{{ $creating ? route('evaluasivendor.store') : route('evaluasivendor.update', $evaluation->id) }}" class="space-y-4">
            @csrf
            @unless($creating) @method('PUT') @endunless
            @if($creating)
                <input type="hidden" name="source_type" value="{{ $evaluation->vendor_perfomance_type }}">
                <input type="hidden" name="source_id" value="{{ $sourceId }}">
            @endif
            <fieldset @disabled(!$editable) class="space-y-3">
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Periode &amp; Template Evaluasi</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3 xl:grid-cols-[1fr_1fr_1fr_2fr]">
                        <label class="{{ $lbl }}"><span>Tanggal Evaluasi</span><input type="date" name="vendor_perfomance_date" required value="{{ old('vendor_perfomance_date', $evaluation->vendor_perfomance_date) }}" class="{{ $ctl }}" style="{{ $box }}"></label>
                        <label class="{{ $lbl }}"><span>Periode Mulai</span><input type="date" name="vendor_perfomance_start_date" required value="{{ old('vendor_perfomance_start_date', $evaluation->vendor_perfomance_start_date) }}" class="{{ $ctl }}" style="{{ $box }}"></label>
                        <label class="{{ $lbl }}"><span>Periode Selesai</span><input type="date" name="vendor_perfomance_end_date" required value="{{ old('vendor_perfomance_end_date', $evaluation->vendor_perfomance_end_date) }}" class="{{ $ctl }}" style="{{ $box }}"></label>
                        <label class="{{ $lbl }} sm:col-span-3 xl:col-span-1"><span>Jenis Pekerjaan / Template <span class="text-red-600">*</span></span>
                            <select name="template_vpe_id" id="template-select" required class="w-full">
                                <option value="">Pilih jenis pekerjaan</option>
                                @foreach($templates as $template)<option value="{{ $template->template_vpe_id }}" @selected(old('template_vpe_id', $evaluation->template_vpe_id) === $template->template_vpe_id)>{{ $template->template_vpe_descr }}</option>@endforeach
                            </select>
                        </label>
                    </div>
                </section>
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a1 1 0 001-1V4a1 1 0 00-1-1H5a1 1 0 00-1 1v16a1 1 0 001 1z"/></svg>
                                    Aspek Penilaian (KPI)
                                </h2>
                                @include('pages.evaluasivendor.partials.score-guide-button')
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Skor 1–5 dikonversi ke nilai 40, 60, 80, 100, 120. Nilai terbobot = nilai × bobot / 100.</p>
                        </div>
                        <span id="kpi-progress" class="hidden rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-600 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600"></span>
                    </div>
                    <p id="template-error" class="p-4 text-sm text-gray-500" role="status">{{ $creating ? 'Pilih template untuk menampilkan kriteria evaluasi.' : '' }}</p>
                    <div class="overflow-x-auto" style="overflow-y:hidden"><table class="w-full text-left text-sm" id="rating-table" style="table-layout:fixed;min-width:1100px;overflow-wrap:anywhere">
                        <colgroup>
                            <col style="width:16%">
                            <col style="width:5%">
                            <col style="width:26%">
                            <col style="width:15%">
                            <col style="width:8%">
                            <col style="width:22%">
                            <col style="width:8%">
                        </colgroup>
                        <thead class="border-b border-gray-200 bg-white text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:bg-gray-900"><tr><th class="px-4 py-3">Aspek / Subaspek</th><th class="px-3 py-3 text-center">Bobot %</th><th class="px-3 py-3">Kriteria Penilaian</th><th class="px-3 py-3">Skor</th><th class="px-3 py-3 text-center">Nilai Terbobot</th><th class="px-3 py-3">Catatan / Bukti Lapangan</th><th class="px-3 py-3 text-center">Attachment</th></tr></thead>
                        <tbody id="rating-body"></tbody>
                    </table></div>
                    <div class="grid grid-cols-1 gap-4 border-t border-gray-100 px-5 py-5 dark:border-gray-700 md:grid-cols-2">
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><span>Catatan Evaluasi</span><textarea name="vendor_perfomance_note" rows="3" maxlength="10000" placeholder="Tulis catatan umum hasil evaluasi…" class="kpi-text" style="min-height:5.5rem;text-transform:none;letter-spacing:normal;font-weight:400">{{ old('vendor_perfomance_note', $evaluation->vendor_perfomance_note) }}</textarea></label>
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><span>Komentar / Rekomendasi</span><textarea name="template_vpe_aspect_comment" rows="3" maxlength="10000" placeholder="Tulis komentar atau rekomendasi untuk vendor…" class="kpi-text" style="min-height:5.5rem;text-transform:none;letter-spacing:normal;font-weight:400">{{ old('template_vpe_aspect_comment', $evaluation->template_vpe_aspect_comment) }}</textarea></label>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-indigo-100 bg-linear-to-r from-indigo-50 to-white px-5 py-4 text-indigo-900 dark:border-gray-700 dark:from-indigo-500/10 dark:to-transparent dark:text-indigo-200">
                        <span class="text-sm font-semibold uppercase tracking-wide">Total Nilai KPI</span>
                        <div class="flex items-center gap-3"><span id="score-total" class="text-3xl font-bold leading-none">{{ $evaluation->template_vpe_aspect_grandtotal_score ?? '0.00' }}</span><span id="score-result" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-200 empty:hidden dark:bg-gray-800 dark:text-indigo-300 dark:ring-indigo-500/40">{{ $evaluation->template_vpe_aspect_result }}</span></div>
                    </div>
                    @if($editable)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-xs text-gray-500">Pastikan semua subaspek sudah dinilai sebelum submit.</p>
                            <button id="save-evaluation" type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-6 6l6-6 6 6"/></svg>
                                {{ !$creating && $evaluation->status === 'D' ? 'Submit Ulang Approval' : 'Submit Approval' }}
                            </button>
                        </div>
                    @endif
                </section>
            </fieldset>
        </form>
        @if($evaluation->exists && $evaluation->status === 'P')
            <form method="POST" action="{{ route('evaluasivendor.status', $evaluation->id) }}" class="space-y-3 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                @csrf
                <h2 class="font-bold">Proses Penilaian</h2>
                <label class="block text-sm">Komentar Proses (wajib untuk Rejected / Revised)<textarea name="comment" maxlength="10000" rows="2" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800">{{ old('comment') }}</textarea></label>
                <p class="text-xs text-gray-500">Simpan perubahan skor terlebih dahulu. Completed dan Rejected akan mengunci penilaian.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button name="status" value="R" class="rounded border border-red-300 px-4 py-2 text-sm text-red-600">Rejected</button>
                    <button name="status" value="D" class="rounded border border-orange-300 px-4 py-2 text-sm text-orange-600">Revised</button>
                    <button name="status" value="C" class="rounded bg-green-600 px-4 py-2 text-sm text-white">Completed</button>
                </div>
            </form>
        @endif
    </div>
    @include('pages.evaluasivendor.partials.score-guide')
    <dialog id="kpi-attachment-dialog" aria-labelledby="kpi-attachment-title" class="w-full max-w-xl rounded-xl border-0 p-0 shadow-xl backdrop:bg-black/50">
        <div class="space-y-4 p-5">
            <div class="flex items-start justify-between gap-3"><div><h2 id="kpi-attachment-title" class="text-lg font-bold">Attachment KPI</h2><p id="kpi-attachment-aspect" class="mt-1 text-sm text-gray-500"></p></div><button type="button" id="kpi-attachment-close" aria-label="Tutup" class="text-xl text-gray-500">&times;</button></div>
            <div><span class="block text-sm font-medium">Pilih file</span><label class="kpi-file-picker" for="kpi-attachment-files"><svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m-4 4 4-4 4 4"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg> Pilih file<input id="kpi-attachment-files" type="file" multiple></label></div>
            <p class="text-xs text-gray-500">Maksimal 10 file baru per detail, masing-masing 10 MB. File disimpan saat submit.</p>
            <ul id="kpi-attachment-list" class="max-h-64 space-y-2 overflow-y-auto text-sm"></ul>
            <div class="flex justify-end"><button type="button" id="kpi-attachment-done" class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">Selesai</button></div>
        </div>
    </dialog>
    <style>
        .kpi-score{display:flex;gap:.3rem}
        .kpi-score-btn{flex:1;height:2.25rem;min-width:0;border:1px solid #d1d5db;border-radius:.5rem;background:#fff;color:#6b7280;font-size:.85rem;font-weight:700;transition:all .15s}
        .kpi-score-btn:hover:not(:disabled){border-color:#7bc8ff;color:#1c71ae;background:#e3f3ff;transform:translateY(-1px)}
        .kpi-score-btn:disabled{cursor:not-allowed;opacity:.6}
        .kpi-score-btn.is-active{color:#fff;border-color:transparent;box-shadow:0 2px 6px rgb(0 0 0/.18)}
        .kpi-score[data-score="1"] .is-active{background:#ef4444}
        .kpi-score[data-score="2"] .is-active{background:#f97316}
        .kpi-score[data-score="3"] .is-active{background:#eab308}
        .kpi-score[data-score="4"] .is-active{background:#22c55e}
        .kpi-score[data-score="5"] .is-active{background:#059669}
        html.dark .kpi-score-btn{background:#1f2937;border-color:#4b5563;color:#9ca3af}
        .kpi-select-hidden{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
        #rating-table td{position:relative}
        #rating-table .kpi-aspect th{padding:0;background:#e3f3ff;border-top:1px solid #d1ecff;border-bottom:1px solid #d1ecff}
        #rating-table .kpi-aspect th>span{display:inline-block;padding:.6rem 1rem}
        .kpi-aspect-title{font-size:.8rem;font-weight:700;color:#124d79;border-left:4px solid #56b1f3}
        .kpi-aspect-weight{float:right;font-size:.7rem;font-weight:600;color:#3193da}
        html.dark #rating-table .kpi-aspect th{background:rgb(86 177 243/.12);border-color:#374151}
        html.dark .kpi-aspect-title{color:#b6e1ff}
        .kpi-score{display:flex;gap:.3rem}
        .kpi-score-label{min-width:0;font-size:.7rem}
        .kpi-aspect-title{font-size:.8rem;font-weight:700;color:#124d79}
        .kpi-aspect-weight{float:right;font-size:.7rem;font-weight:600;color:#3193da}
        html.dark .kpi-aspect-title{color:#b6e1ff}
        .kpi-weight{display:inline-block;min-width:3.25rem;padding:.15rem .5rem;border-radius:999px;background:#f3f4f6;color:#374151;font-size:.75rem;font-weight:600}
        html.dark .kpi-weight{background:#374151;color:#e5e7eb}
        .kpi-aspect{cursor:pointer;user-select:none}
        .kpi-aspect:hover th{background:#d1ecff}
        .kpi-chevron{display:inline-block;vertical-align:-2px;margin-right:.45rem;transition:transform .2s}
        .kpi-aspect[aria-expanded="false"] .kpi-chevron{transform:rotate(-90deg)}
        .kpi-aspect-progress{float:right;margin-right:1rem;padding:.15rem .5rem;border-radius:999px;background:#fff;color:#1c71ae;font-size:.65rem;font-weight:600;margin-top:.5rem}
        .kpi-aspect-weight{margin-top:.7rem}
        .kpi-text{field-sizing:content;overflow:hidden;display:block;width:100%;min-height:4.5rem;padding:.5rem .65rem;border:1px solid #e5e7eb;border-radius:.5rem;background:#fff;color:#374151;font-size:.8rem;line-height:1.35;resize:vertical;transition:border-color .15s,box-shadow .15s}
        .kpi-text:focus{outline:none;border-color:#56b1f3;box-shadow:0 0 0 2px rgb(86 177 243/.25)}
        .kpi-text:disabled{background:#f9fafb;color:#6b7280}
        html.dark .kpi-text{background:#1f2937;border-color:#4b5563;color:#e5e7eb}
        .kpi-select{display:block;width:100%;height:2.5rem;padding:0 2rem 0 .65rem;border:1px solid #d1d5db;border-radius:.5rem;background-color:#fff;color:#374151;font-size:.8rem;font-weight:600;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .55rem center;background-size:1rem;transition:border-color .15s,box-shadow .15s,background-color .15s}
        .kpi-select:focus{outline:none;border-color:#56b1f3;box-shadow:0 0 0 2px rgb(86 177 243/.25)}
        .kpi-select[data-score="1"]{background-color:#fef2f2;border-color:#fca5a5;color:#b91c1c}
        .kpi-select[data-score="2"]{background-color:#fff7ed;border-color:#fdba74;color:#c2410c}
        .kpi-select[data-score="3"]{background-color:#fefce8;border-color:#fde047;color:#a16207}
        .kpi-select[data-score="4"]{background-color:#f0fdf4;border-color:#86efac;color:#15803d}
        .kpi-select[data-score="5"]{background-color:#ecfdf5;border-color:#34d399;color:#047857}
        html.dark .kpi-select{background-color:#1f2937;border-color:#4b5563;color:#e5e7eb}
        .kpi-clip{display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .7rem;border:1px solid #b6e1ff;border-radius:.5rem;background:#e3f3ff;color:#1c71ae;font-size:.75rem;font-weight:600;transition:background .15s}
        .kpi-clip:hover{background:#d1ecff}
        html.dark .kpi-clip{background:rgb(86 177 243/.15);border-color:rgb(86 177 243/.4);color:#b6e1ff}
        .select2-filter .select2-selection--single{height:2.5rem!important;border-radius:.5rem;border-color:#d1d5db;background:#fff;box-shadow:0 1px 2px rgb(0 0 0/.04)}
        .select2-filter .select2-selection--single:hover{border-color:#9ca3af}
        .select2-filter .select2-selection__rendered{text-transform:none!important;font-weight:400!important;letter-spacing:normal!important;color:#1f2937;font-size:.875rem!important;line-height:2.4rem!important;padding-left:.75rem;padding-right:2rem}
        .select2-filter .select2-selection__arrow{height:2.4rem!important;right:.5rem!important}
        .select2-filter.select2-container--focus .select2-selection--single,.select2-filter.select2-container--open .select2-selection--single{border-color:#56b1f3;box-shadow:0 0 0 2px rgb(86 177 243/.3)}
        .select2-dropdown.select2-filter{border-color:#d1d5db;border-radius:.5rem;box-shadow:0 8px 24px rgb(0 0 0/.12);overflow:hidden;font-size:.875rem}
        .select2-dropdown.select2-filter .select2-results__option{padding:.5rem .75rem;text-transform:none;letter-spacing:normal;font-weight:400}
        .select2-dropdown.select2-filter .select2-results__option--highlighted[aria-selected]{background:#3193da;color:#fff}
        .select2-dropdown.select2-filter .select2-results__option[aria-selected=true]{background:#e3f3ff;color:#1c71ae;font-weight:600}
        html.dark .select2-filter .select2-selection--single{background:#1f2937;border-color:#4b5563}
        html.dark .select2-filter .select2-selection__rendered{color:#e5e7eb}
        html.dark .select2-dropdown.select2-filter{background:#1f2937;border-color:#4b5563;color:#e5e7eb}
        #kpi-attachment-dialog { position: fixed; inset: 0; margin: auto; width: min(36rem, calc(100% - 2rem)); max-height: calc(100dvh - 2rem); overflow-y: auto; }
        .kpi-file-picker { position: relative; display: inline-flex; align-items: center; gap: 8px; margin-top: 8px; padding: 9px 14px; border: 1px solid #3193da; border-radius: 8px; background: #e3f3ff; color: #1c71ae; font-size: 13px; font-weight: 600; cursor: pointer; }
        .kpi-file-picker:hover { background: #d1ecff; }
        .kpi-file-picker:focus-within { outline: 2px solid #56b1f3; outline-offset: 2px; }
        .kpi-file-picker input { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; }
        .kpi-file-remove { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: 34px; height: 34px; border: 0; border-radius: 7px; background: #fef2f2; color: #dc2626; cursor: pointer; }
        .kpi-file-remove:hover { background: #fee2e2; }
        .kpi-file-remove:focus-visible { outline: 2px solid #dc2626; outline-offset: 2px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editable = @json($editable);
            const creating = @json($creating);
            const oldRatings = @json(old('ratings', []));
            const oldNotes = @json(old('notes', []));
            const oldCriteria = @json(old('criteria', []));
            const recorded = @json($details);
            const existingAttachments = @json($attachments ?? []);
            const selectedFiles = new Map(), removedAttachments = new Set();
            const attachmentDialog = document.getElementById('kpi-attachment-dialog');
            const attachmentList = document.getElementById('kpi-attachment-list');
            const attachmentInput = document.getElementById('kpi-attachment-files');
            let activeAttachment = null;
            const escape = value => {const el = document.createElement('span'); el.textContent = value ?? ''; return el.innerHTML.replaceAll('"', '&quot;').replaceAll("'", '&#39;');};
            let scale = {}, requestNumber = 0;
            const body = document.getElementById('rating-body');
            const message = document.getElementById('template-error');
            const save = document.getElementById('save-evaluation');
            const form = document.getElementById('evaluation-form');
            let submitting = false;
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                openInvalid();
                if (submitting || !form.reportValidity() || !save || save.disabled) return;
                const payload = new FormData(form);
                const selectedTemplate = document.getElementById('template-select').value;
                selectedFiles.forEach((files, key) => {
                    if (key.startsWith(selectedTemplate + ':')) files.forEach(file => payload.append(`attachments[${key.slice(selectedTemplate.length + 1)}][]`, file));
                });
                removedAttachments.forEach(id => payload.append('remove_attachments[]', id));
                submitting = true;
                save.disabled = true;
                const label = save.innerHTML;
                save.textContent = 'Mengirim…';
                const fields = form.querySelector('fieldset');
                fields.disabled = true;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST', body: payload,
                        headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const errors = result.errors ? Object.values(result.errors).flat().join('\n') : '';
                        const fallback = response.status === 419 ? 'Sesi telah berakhir. Silakan muat ulang halaman.'
                            : 'Pengajuan gagal diproses. Silakan coba kembali.';
                        await Swal.fire({icon: response.status === 422 ? 'warning' : 'error',
                            title: 'Pengajuan belum berhasil',
                            text: errors || (response.status < 500 ? result.message : '') || fallback,
                            confirmButtonText: 'OK'});
                        return;
                    }
                    window.location.assign(result.redirect);
                } catch (error) {
                    await Swal.fire({icon: 'error', title: 'Koneksi bermasalah',
                        text: 'Respons server tidak dapat diterima. Isian Anda tetap tersedia; periksa koneksi dan daftar penilaian sebelum mencoba kembali.', confirmButtonText: 'OK'});
                } finally {
                    submitting = false;
                    save.disabled = false;
                    save.innerHTML = label;
                    fields.disabled = !editable;
                }
            });
            function requiredMessage(field, text) {
                field.addEventListener('invalid', () => {
                    if (field.validity.valueMissing) field.setCustomValidity(text);
                });
                field.addEventListener('change', () => field.setCustomValidity(''));
            }
            function setSection(head, open) {
                head.setAttribute('aria-expanded', open ? 'true' : 'false');
                body.querySelectorAll('tr[data-group="' + head.dataset.aspect + '"]').forEach(row => { row.hidden = !open; });
                if (open) autosizeAll();
            }
            function openInvalid() {
                body.querySelectorAll('.rating-input:invalid').forEach(input => {
                    const head = body.querySelector('.kpi-aspect[data-aspect="' + input.dataset.aspect + '"]');
                    if (head && head.getAttribute('aria-expanded') !== 'true') setSection(head, true);
                });
            }
            function autosizeAll() {
                if (CSS.supports('field-sizing', 'content')) return;
                body.querySelectorAll('.kpi-text').forEach(area => { area.style.height = 'auto'; area.style.height = area.scrollHeight + 2 + 'px'; });
            }
            function paint() {
                const inputs = [...document.querySelectorAll('.rating-input')];
                const names = {1: 'Sangat Kurang', 2: 'Kurang', 3: 'Cukup', 4: 'Baik', 5: 'Sangat Baik'};
                inputs.forEach(input => {
                    input.dataset.score = input.value;
                    const group = input.parentElement.querySelector('.kpi-score');
                    group.dataset.score = input.value;
                    group.querySelectorAll('.kpi-score-btn').forEach(btn => btn.classList.toggle('is-active', btn.dataset.value === input.value));
                    input.parentElement.querySelector('.kpi-score-label').textContent = input.value ? names[input.value] : 'Belum dinilai';
                });
                const badge = document.getElementById('kpi-progress');
                badge.classList.toggle('hidden', !inputs.length);
                badge.textContent = inputs.filter(input => input.value).length + ' / ' + inputs.length + ' subaspek dinilai';
                document.querySelectorAll('.kpi-aspect').forEach(head => {
                    const group = inputs.filter(input => input.dataset.aspect === head.dataset.aspect);
                    head.querySelector('.kpi-aspect-progress').textContent = group.filter(input => input.value).length + ' / ' + group.length + ' dinilai';
                });
            }
            function total() {
                paint();
                let sum = 0;
                document.querySelectorAll('.rating-input').forEach(input => {
                    const value = scale[input.dataset.aspect + ':' + input.value];
                    const weighted = value == null ? 0 : Number(input.dataset.weight) * Number(value) / 100;
                    input.closest('tr').querySelector('.weighted-score').textContent = input.value ? weighted.toFixed(2) : '—';
                    sum += Math.round(weighted * 100) / 100;
                });
                if (editable) {
                    document.getElementById('score-total').textContent = sum.toFixed(2);
                    document.getElementById('score-result').textContent = sum >= 90 ? 'Sangat Baik' : sum >= 80 ? 'Baik' : sum >= 70 ? 'Cukup' : sum >= 60 ? 'Kurang' : 'Sangat Kurang/Perlu Evaluasi Khusus';
                }
            }
            function render(details) {
                body.innerHTML = '';
                let aspect = '';
                details.forEach(detail => {
                    if (aspect !== detail.template_vpe_aspect_id) {
                        aspect = detail.template_vpe_aspect_id;
                        const aspectWeight = details.filter(d => d.template_vpe_aspect_id === aspect).reduce((s, d) => s + Number(d.template_vpe_subaspect_bobot), 0);
                        body.insertAdjacentHTML("beforeend", `<tr class="kpi-aspect" data-aspect="${escape(aspect)}" role="button" tabindex="0" aria-expanded="true"><th colspan="7"><span class="kpi-aspect-title"><svg class="kpi-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>${escape(detail.template_vpe_aspect_descr)}</span><span class="kpi-aspect-weight">Bobot ${aspectWeight.toFixed(2)}%</span><span class="kpi-aspect-progress"></span></th></tr>`);
                    }
                    const id = detail.template_vpe_subaspect_id;
                    const selected = oldRatings[id] ?? detail.template_vpe_subaspect_score ?? '';
                    const labels = {1: 'Sangat Kurang', 2: 'Kurang', 3: 'Cukup', 4: 'Baik', 5: 'Sangat Baik'};
                    const criteriaText = oldCriteria[id] ?? detail.template_vpe_subaspect_kriteria ?? '';
                    const options = [1,2,3,4,5].map(score => `<option value="${score}" ${Number(selected) === score ? 'selected' : ''}>${score} — ${labels[score]}</option>`).join('');
                    body.insertAdjacentHTML('beforeend', `<tr data-group="${escape(detail.template_vpe_aspect_id)}" class="kpi-row border-b border-gray-100 align-top transition-colors hover:bg-indigo-50/30 dark:border-gray-700 dark:hover:bg-indigo-500/5">
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">${escape(detail.template_vpe_subaspect_descr)}</td>
                        <td class="px-3 py-3 text-center"><span class="kpi-weight">${Number(detail.template_vpe_subaspect_bobot).toFixed(2)}</span></td>
                        <td class="px-3 py-3"><textarea name="criteria[${escape(id)}]" rows="3" maxlength="10000" aria-label="Kriteria penilaian ${escape(id)}" class="kpi-text" ${editable ? '' : 'disabled'}>${escape(criteriaText)}</textarea></td>
                        <td class="px-3 py-3"><div class="kpi-score" data-score="${escape(selected)}">${[1,2,3,4,5].map(n => `<button type="button" class="kpi-score-btn" data-value="${n}" title="${n} — ${labels[n]}" aria-label="Skor ${n} — ${labels[n]}" ${editable ? '' : 'disabled'}>${n}</button>`).join('')}</div><div class="kpi-score-label mt-1 text-center text-[11px] font-medium text-gray-500">${selected ? labels[selected] : 'Belum dinilai'}</div><select class="rating-input kpi-select-hidden" tabindex="-1" aria-label="Skor ${escape(id)}" name="ratings[${escape(id)}]" data-aspect="${escape(detail.template_vpe_aspect_id)}" data-weight="${Number(detail.template_vpe_subaspect_bobot)}" required ${editable ? '' : 'disabled'}><option value="">—</option>${options}</select></td>
                        <td class="weighted-score px-3 py-3 text-center text-base font-bold text-indigo-700 dark:text-indigo-300">${detail.template_vpe_subaspect_nilai ?? '—'}</td>
                        <td class="px-3 py-3"><textarea name="notes[${escape(id)}]" rows="3" maxlength="10000" aria-label="Catatan atau bukti lapangan ${escape(id)}" class="kpi-text" ${editable ? '' : 'disabled'}>${escape(oldNotes[id] ?? detail.template_vpe_subaspect_note)}</textarea></td>
                        <td class="px-3 py-3 text-center"><button type="button" class="kpi-attachment-open kpi-clip" data-subaspect="${escape(id)}" data-detail="${escape(detail.id ?? '')}" data-label="${escape(detail.template_vpe_subaspect_descr)}" aria-label="Attachment ${escape(detail.template_vpe_subaspect_descr)}"><svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.5l-8.6 8.6a5.5 5.5 0 01-7.8-7.8l9-9a3.7 3.7 0 015.2 5.2l-9 9a1.8 1.8 0 01-2.6-2.6l8.3-8.3"/></svg><span class="kpi-attachment-count"></span></button></td></tr>`);
                });
                body.querySelectorAll('.rating-input').forEach(input => {
                    requiredMessage(input, 'Skor pada setiap subaspek wajib diisi.');
                    input.addEventListener('change', total);
                });
                updateAttachmentCounts();
                autosizeAll();
                paint();
                if (editable) total();
            }
            function updateAttachmentCounts() {
                body.querySelectorAll('.kpi-attachment-open').forEach(button => {
                    const key = document.getElementById('template-select').value + ':' + button.dataset.subaspect;
                    const saved = (existingAttachments[button.dataset.detail] || []).filter(item => !removedAttachments.has(String(item.id))).length;
                    button.querySelector('.kpi-attachment-count').textContent = saved + (selectedFiles.get(key) || []).length;
                });
            }
            function renderAttachmentList() {
                attachmentList.replaceChildren();
                const saved = existingAttachments[activeAttachment.detail] || [];
                const files = selectedFiles.get(activeAttachment.key) || [];
                const addItem = (name, url, remove) => {
                    const li = document.createElement('li'); li.className = 'flex items-center justify-between gap-3 rounded bg-gray-50 p-2';
                    const label = document.createElement(url ? 'a' : 'span'); label.textContent = name; label.className = 'break-all';
                    if (url) { label.href = url; label.target = '_blank'; label.rel = 'noopener noreferrer'; label.className += ' text-indigo-600'; }
                    const button = document.createElement('button'); button.type = 'button'; button.className = 'kpi-file-remove';
                    button.setAttribute('aria-label', 'Hapus ' + name); button.title = 'Hapus attachment';
                    button.innerHTML = '<svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m5 4v6m4-6v6"/></svg>';
                    button.addEventListener('click', () => { remove(); renderAttachmentList(); updateAttachmentCounts(); });
                    li.append(label, button); attachmentList.append(li);
                };
                saved.filter(item => !removedAttachments.has(String(item.id))).forEach(item => addItem(item.name, item.url, () => removedAttachments.add(String(item.id))));
                files.forEach((file, index) => addItem(file.name, null, () => { files.splice(index, 1); selectedFiles.set(activeAttachment.key, files); }));
                if (!attachmentList.children.length) { const empty = document.createElement('li'); empty.className = 'text-gray-500'; empty.textContent = 'Belum ada attachment.'; attachmentList.append(empty); }
            }
            body.addEventListener('keydown', event => {
                const head = event.target.closest('.kpi-aspect');
                if (head && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); setSection(head, head.getAttribute('aria-expanded') !== 'true'); }
            });
            body.addEventListener('input', event => {
                if (event.target.matches('.kpi-crit-ta')) event.target.closest('.kpi-crit').querySelector('.kpi-crit-view').textContent = event.target.value;
            });
            body.addEventListener('click', event => {
                const head = event.target.closest('.kpi-aspect');
                if (head) { setSection(head, head.getAttribute('aria-expanded') !== 'true'); return; }
                const view = event.target.closest('.kpi-crit-view');
                if (view) { view.classList.toggle('is-open'); return; }
                const toggle = event.target.closest('.kpi-crit-toggle');
                if (toggle) {
                    const box = toggle.closest('.kpi-crit');
                    const area = box.querySelector('.kpi-crit-ta');
                    area.hidden = !area.hidden;
                    box.querySelector('.kpi-crit-view').hidden = !area.hidden;
                    toggle.textContent = area.hidden ? 'Ubah' : 'Selesai';
                    if (!area.hidden) area.focus();
                    return;
                }
                const pick = event.target.closest('.kpi-score-btn');
                if (pick) {
                    const input = pick.closest('td').querySelector('.rating-input');
                    input.value = input.value === pick.dataset.value ? '' : pick.dataset.value;
                    input.dispatchEvent(new Event('change', {bubbles: true}));
                    return;
                }
                const button = event.target.closest('.kpi-attachment-open'); if (!button) return;
                activeAttachment = {key: document.getElementById('template-select').value + ':' + button.dataset.subaspect, detail: button.dataset.detail};
                document.getElementById('kpi-attachment-aspect').textContent = button.dataset.label;
                renderAttachmentList(); attachmentDialog.showModal();
            });
            attachmentInput.addEventListener('change', async () => {
                const files = [...(selectedFiles.get(activeAttachment.key) || []), ...attachmentInput.files];
                attachmentInput.value = '';
                if (files.length > 10 || files.some(file => file.size > 10 * 1024 * 1024)) {
                    await Swal.fire({icon: 'warning', text: 'Maksimal 10 file baru per detail, masing-masing 10 MB.'}); return;
                }
                selectedFiles.set(activeAttachment.key, files); renderAttachmentList(); updateAttachmentCounts();
            });
            document.getElementById('kpi-attachment-close').addEventListener('click', () => attachmentDialog.close());
            document.getElementById('kpi-attachment-done').addEventListener('click', () => attachmentDialog.close());
            async function load(template, preserve) {
                const current = ++requestNumber;
                if (save) save.disabled = true;
                if (!template) {body.innerHTML = ''; message.textContent = 'Pilih template untuk menampilkan kriteria evaluasi.'; if (save) save.disabled = false; return;}
                message.textContent = 'Memuat template evaluasi…';
                try {
                    const url = @json(route('evaluasivendor.template', '__TEMPLATE__')).replace('__TEMPLATE__', encodeURIComponent(template));
                    const response = await fetch(url, {headers: {'Accept': 'application/json'}});
                    if (!response.ok) throw new Error('Template gagal dimuat. Silakan pilih kembali.');
                    const master = await response.json();
                    if (current !== requestNumber) return;
                    scale = master.scale;
                    render(preserve ? recorded : master.details);
                    message.textContent = '';
                    if (save) save.disabled = false;
                } catch (error) {
                    if (current !== requestNumber) return;
                    message.textContent = error.message;
                }
            }
            const select = document.getElementById('template-select');
            $(select).select2({containerCssClass: 'select2-filter', dropdownCssClass: 'select2-filter', width: '100%', placeholder: 'Pilih jenis pekerjaan', disabled: !editable})
                .on('select2:select select2:clear', () => select.dispatchEvent(new Event('change')));
            const originalTemplate = @json($evaluation->template_vpe_id);
            requiredMessage(select, 'Jenis Pekerjaan wajib dipilih.');
            const loadSelection = () => load(select.value, !creating && select.value === originalTemplate);
            select.addEventListener('change', loadSelection);
            if (select.value) loadSelection();
        });
    </script>
</x-app-layout>
