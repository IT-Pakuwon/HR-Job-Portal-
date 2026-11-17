<x-app-layout>
    @php($creating = !$evaluation->exists)
    <div class="mx-auto w-full space-y-3 p-2 md:px-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><a href="{{ route('evaluasivendor') }}" class="text-sm text-indigo-600">&larr; Penilaian Vendor</a>
                <h1 class="mt-1 text-xl font-bold">{{ $creating ? 'Input Penilaian Vendor' : $evaluation->vendor_perfomance_id }}</h1>
            </div>
        </div>
        @if(session('success'))<div role="status" class="rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div role="alert" class="rounded-lg bg-red-50 p-3 text-red-700"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="grid grid-cols-1 gap-x-4 gap-y-2 rounded-xl border border-gray-200 bg-white p-3 text-sm dark:border-gray-700 dark:bg-gray-900 md:grid-cols-3 xl:grid-cols-4">
            <div><span class="text-xs text-gray-500">Sumber / No. Dokumen</span><p class="mt-1 font-semibold">{{ $evaluation->vendor_perfomance_type }} / {{ $evaluation->ponbr }}</p></div>
            <div><span class="text-xs text-gray-500">Company / Department</span><p class="mt-1">{{ $evaluation->cpny_id }} / {{ $evaluation->department_id ?: '—' }}</p></div>
            <div><span class="text-xs text-gray-500">Vendor</span><p class="mt-1 font-semibold">{{ $evaluation->vendorname }}</p></div>
            <div class="md:col-span-3 xl:col-span-1"><span class="text-xs text-gray-500">Pekerjaan</span><p class="mt-1 whitespace-pre-line">{{ $evaluation->keperluan }}</p></div>
        </section>
        <form method="POST" id="evaluation-form" action="{{ $creating ? route('evaluasivendor.store') : route('evaluasivendor.update', $evaluation->id) }}" class="space-y-4">
            @csrf
            @unless($creating) @method('PUT') @endunless
            @if($creating)
                <input type="hidden" name="source_type" value="{{ $evaluation->vendor_perfomance_type }}">
                <input type="hidden" name="source_id" value="{{ $sourceId }}">
            @endif
            <fieldset @disabled(!$editable) class="space-y-3">
                <div class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-white p-3 text-sm dark:border-gray-700 dark:bg-gray-900 md:grid-cols-3 xl:grid-cols-6">
                    <label>Tanggal Evaluasi<input type="date" name="vendor_perfomance_date" required value="{{ old('vendor_perfomance_date', $evaluation->vendor_perfomance_date) }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800"></label>
                    <label>Periode Mulai<input type="date" name="vendor_perfomance_start_date" required value="{{ old('vendor_perfomance_start_date', $evaluation->vendor_perfomance_start_date) }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800"></label>
                    <label>Periode Selesai<input type="date" name="vendor_perfomance_end_date" required value="{{ old('vendor_perfomance_end_date', $evaluation->vendor_perfomance_end_date) }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800"></label>
                    <label class="md:col-span-3 xl:col-span-3">Jenis Pekerjaan / Template Evaluasi <span class="text-red-600">*</span>
                        @if($creating)
                            <select name="template_vpe_id" id="template-select" required class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800">
                                <option value="">Pilih jenis pekerjaan</option>
                                @foreach($templates as $template)<option value="{{ $template->template_vpe_id }}" @selected(old('template_vpe_id') === $template->template_vpe_id)>{{ $template->template_vpe_descr }}</option>@endforeach
                            </select>
                        @else <p class="mt-1 font-semibold">{{ $evaluation->template_vpe_descr }}</p> @endif
                    </label>
                </div>
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                    <div class="border-b p-4"><h2 class="font-bold">Aspek Penilaian (KPI)</h2><p class="mt-1 text-xs text-gray-500">Skor 1–5 dikonversi ke nilai 40, 60, 80, 100, 120. Nilai terbobot = nilai × bobot / 100.</p></div>
                    <p id="template-error" class="p-4 text-sm text-gray-500" role="status">{{ $creating ? 'Pilih template untuk menampilkan kriteria evaluasi.' : '' }}</p>
                    <div class="overflow-x-auto"><table class="w-full text-left text-sm" id="rating-table" style="table-layout:fixed;min-width:1100px;overflow-wrap:anywhere">
                        <colgroup>
                            <col style="width:17%">
                            <col style="width:5%">
                            <col style="width:32%">
                            <col style="width:12%">
                            <col style="width:8%">
                            <col style="width:26%">
                        </colgroup>
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800"><tr><th class="p-3">Aspek / Subaspek</th><th class="p-3">Bobot %</th><th class="p-3">Kriteria Penilaian</th><th class="p-3">Skor</th><th class="p-3">Nilai Terbobot</th><th class="p-3">Catatan / Bukti Lapangan</th></tr></thead>
                        <tbody id="rating-body"></tbody>
                    </table></div>
                    <div class="flex items-center justify-between gap-4 border-t bg-indigo-50 p-4 text-indigo-900">
                        <strong>Total Nilai KPI</strong><div><strong id="score-total">{{ $evaluation->template_vpe_aspect_grandtotal_score ?? '0.00' }}</strong> <span id="score-result" class="ml-3 text-sm">{{ $evaluation->template_vpe_aspect_result }}</span></div>
                    </div>
                </section>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <label class="block text-sm">Catatan Evaluasi<textarea name="vendor_perfomance_note" rows="3" maxlength="10000" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800">{{ old('vendor_perfomance_note', $evaluation->vendor_perfomance_note) }}</textarea></label>
                    <label class="block text-sm">Komentar / Rekomendasi<textarea name="template_vpe_aspect_comment" rows="3" maxlength="10000" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800">{{ old('template_vpe_aspect_comment', $evaluation->template_vpe_aspect_comment) }}</textarea></label>
                </div>
            </fieldset>
            @if($editable)<div class="flex justify-end"><button id="save-evaluation" type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white disabled:opacity-50">Submit Approval</button></div>@endif
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editable = @json($editable);
            const creating = @json($creating);
            const oldRatings = @json(old('ratings', []));
            const oldNotes = @json(old('notes', []));
            const recorded = @json($details);
            const escape = value => {const el = document.createElement('span'); el.textContent = value ?? ''; return el.innerHTML.replaceAll('"', '&quot;').replaceAll("'", '&#39;');};
            let scale = {}, requestNumber = 0;
            const body = document.getElementById('rating-body');
            const message = document.getElementById('template-error');
            const save = document.getElementById('save-evaluation');
            const form = document.getElementById('evaluation-form');
            let submitting = false;
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (submitting || !form.reportValidity() || !save || save.disabled) return;
                const payload = new FormData(form);
                submitting = true;
                save.disabled = true;
                const label = save.textContent;
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
                    save.textContent = label;
                    fields.disabled = !editable;
                }
            });
            function requiredMessage(field, text) {
                field.addEventListener('invalid', () => {
                    if (field.validity.valueMissing) field.setCustomValidity(text);
                });
                field.addEventListener('change', () => field.setCustomValidity(''));
            }
            function total() {
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
                        body.insertAdjacentHTML('beforeend', `<tr class="bg-indigo-50 text-indigo-800"><th colspan="6" class="p-3">${escape(detail.template_vpe_aspect_descr)}</th></tr>`);
                    }
                    const id = detail.template_vpe_subaspect_id;
                    const selected = oldRatings[id] ?? detail.template_vpe_subaspect_score ?? '';
                    const labels = {1: 'Sangat Kurang', 2: 'Kurang', 3: 'Cukup', 4: 'Baik', 5: 'Sangat Baik'};
                    const options = [1,2,3,4,5].map(score => `<option value="${score}" ${Number(selected) === score ? 'selected' : ''}>${score} — ${labels[score]}</option>`).join('');
                    body.insertAdjacentHTML('beforeend', `<tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="p-3">${escape(detail.template_vpe_subaspect_descr)}</td>
                        <td class="p-3">${Number(detail.template_vpe_subaspect_bobot).toFixed(2)}</td>
                        <td class="whitespace-pre-line p-3">${escape(detail.template_vpe_subaspect_kriteria)}</td>
                        <td class="p-3"><select class="rating-input rounded border-gray-300 text-sm dark:bg-gray-800" name="ratings[${escape(id)}]" data-aspect="${escape(detail.template_vpe_aspect_id)}" data-weight="${Number(detail.template_vpe_subaspect_bobot)}" required ${editable ? '' : 'disabled'}><option value="">—</option>${options}</select></td>
                        <td class="weighted-score p-3">${detail.template_vpe_subaspect_nilai ?? '—'}</td>
                        <td class="p-3"><textarea name="notes[${escape(id)}]" rows="3" maxlength="10000" aria-label="Catatan atau bukti lapangan ${escape(id)}" class="w-full rounded border-gray-300 text-sm dark:bg-gray-800" ${editable ? '' : 'disabled'}>${escape(oldNotes[id] ?? detail.template_vpe_subaspect_note)}</textarea></td></tr>`);
                });
                body.querySelectorAll('.rating-input').forEach(input => {
                    requiredMessage(input, 'Skor pada setiap subaspek wajib diisi.');
                    input.addEventListener('change', total);
                });
                if (editable) total();
            }
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
            if (creating) {
                const select = document.getElementById('template-select');
                requiredMessage(select, 'Jenis Pekerjaan wajib dipilih.');
                select.addEventListener('change', () => load(select.value, false));
                if (select.value) load(select.value, false);
            } else if (editable) {
                load(@json($evaluation->template_vpe_id), true);
            } else {render(recorded); message.textContent = '';}
        });
    </script>
</x-app-layout>
