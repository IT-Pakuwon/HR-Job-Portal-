<form id="assessmentFormUSER" method="POST">
    @csrf

    <input type="hidden" name="totalscore" id="totalScoreUSERInput" value="0">
    <input type="hidden" name="result_status" id="resultStatusUSER" value="NOT SUITABLE">
    <input type="hidden" name="docid" value="{{ $career->docid }}">

    {{-- ── Info header ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-2 border-b border-gray-100 px-4 py-2.5 dark:border-gray-700/60 lg:grid-cols-4">
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Nama Interview</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                {{ $tr_assessment_user && $tr_assessment_user->user ? $tr_assessment_user->user : $user->name }}
            </p>
        </div>
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tanggal &amp; Jam</p>
            <div class="mt-0.5 flex items-center gap-2">
                <input type="date" name="interview_date"
                    value="{{ $tr_assessment_user && $tr_assessment_user->assessment_date ? \Carbon\Carbon::parse($tr_assessment_user->assessment_date)->format('Y-m-d') : '' }}"
                    required
                    class="rounded border border-gray-200 bg-white px-2 py-0.5 text-xs text-gray-700 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                <input type="time" name="interview_time"
                    value="{{ $tr_assessment_user && $tr_assessment_user->assessment_date ? \Carbon\Carbon::parse($tr_assessment_user->assessment_date)->format('H:i') : '' }}"
                    required
                    class="rounded border border-gray-200 bg-white px-2 py-0.5 text-xs text-gray-700 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            </div>
        </div>
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Standar Scoring</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100" id="resultTextUSER">NOT SUITABLE</p>
        </div>
        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/30">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Jumlah Nilai</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalScoreUSER">0</p>
        </div>
    </div>

    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700/60">
                <th class="w-1/5 py-2.5 pl-5 pr-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Kriteria</th>
                <th class="px-2 py-2.5 text-center text-[10px] font-bold text-gray-400">0</th>
                <th class="px-2 py-2.5 text-center text-[10px] font-bold text-gray-400">1</th>
                <th class="px-2 py-2.5 text-center text-[10px] font-bold text-gray-400">2</th>
                <th class="px-2 py-2.5 text-center text-[10px] font-bold text-gray-400">3</th>
                <th class="px-2 py-2.5 text-center text-[10px] font-bold text-gray-400">4</th>
                <th class="w-12 py-2.5 pl-2 pr-5 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Nilai</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
            @foreach ($assessmentGroupsUser as $groupIndex => $group)
                @php $selectedScore = $group['selected_score'] ?? 0; @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                    <td class="py-3 pl-5 pr-3 align-middle text-xs font-semibold text-gray-800 dark:text-gray-100">
                        {{ $group['assessment_group'] }}
                    </td>
                    @foreach ($group['options'] as $score => $option)
                        <td class="px-2 py-3 text-center align-middle">
                            <label class="flex cursor-pointer flex-col items-center gap-1 text-[10px] leading-tight text-gray-500 dark:text-gray-400">
                                <span>{{ $option['assessment_descr'] }}</span>
                                <input type="radio" name="scores[{{ $groupIndex }}]" value="{{ $score }}"
                                    {{ $selectedScore == $score ? 'checked' : '' }}
                                    onclick="updateScoreUSER({{ $groupIndex }}, {{ $score }})">
                            </label>
                        </td>
                    @endforeach
                    <td id="scoreCellUSER-{{ $groupIndex }}" class="py-3 pl-2 pr-5 text-center align-middle text-sm font-bold text-gray-800 dark:text-gray-100">
                        {{ $selectedScore }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button> --}}

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr class="bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-700">
                    <th colspan="2"
                        class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-700">
                        STANDARD SCORING
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td
                        class="whitespace-nowrap border-r border-gray-200 px-6 py-2 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        SUITABLE</td>
                    <td class="px-6 py-2 text-sm text-gray-700 dark:text-gray-300">
                        21–28 FOR LEVEL<br class="md:hidden">MANAGER/SUPERVISOR<br>20–25 FOR STAFF
                    </td>
                </tr>
                <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td
                        class="whitespace-nowrap border-r border-gray-200 px-6 py-2 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        CONSIDER</td>
                    <td class="px-6 py-2 text-sm text-gray-700 dark:text-gray-300">15–19</td>
                </tr>
                <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td
                        class="whitespace-nowrap border-r border-gray-200 px-6 py-2 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        NOT SUITABLE</td>
                    <td class="px-6 py-2 text-sm text-gray-700 dark:text-gray-300">0–14</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end"> {{-- Container to align button to the right --}}
        <button type="submit"
            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
            Save
        </button>
    </div>
</form>



<script>
    const totalGroupsUSER = {{ count($assessmentGroupsUser) }};
    let scoreValuesUSER = Array(totalGroupsUSER).fill(0);

    function updateScoreUSER(groupIndex, value) {
        scoreValuesUSER[groupIndex] = value;
        document.getElementById(`scoreCellUSER-${groupIndex}`).textContent = value;

        const total = scoreValuesUSER.reduce((sum, val) => sum + parseInt(val || 0), 0);
        document.getElementById('totalScoreUSER').textContent = total;
        document.getElementById('totalScoreUSERInput').value = total;

        const resultTextUSER = document.getElementById('resultTextUSER');

        let result = 'NOT SUITABLE';
        if (total < 15) {
            result = 'NOT SUITABLE';
        } else if (total >= 15 && total <= 19) {
            result = 'CONSIDER';
        } else if (total >= 20 && total <= 28) {
            result = 'SUITABLE';
        } else {
            result = '-';
        }

        resultTextUSER.textContent = result;
        document.getElementById('resultStatusUSER').value = result;
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const radios = document.querySelectorAll('#assessmentFormUSER input[type="radio"]:checked');
        let total = 0;

        radios.forEach((radio) => {
            const groupIndex = parseInt(radio.name.match(/\d+/)[0]);
            const score = parseInt(radio.value);
            scoreValuesUSER[groupIndex] = score;
            document.getElementById(`scoreCellUSER-${groupIndex}`).textContent = score;
            total += score;
        });

        document.getElementById('totalScoreUSER').textContent = total;
        document.getElementById('totalScoreUSERInput').value = total;

        const resultTextUSER = document.getElementById('resultTextUSER');
        const resultStatusUSER = document.getElementById('resultStatusUSER');

        if (total < 15) {
            resultTextUSER.textContent = 'NOT SUITABLE';
            resultStatusUSER.value = 'NOT SUITABLE';
        } else if (total <= 19) {
            resultTextUSER.textContent = 'CONSIDER';
            resultStatusUSER.value = 'CONSIDER';
        } else if (total <= 28) {
            resultTextUSER.textContent = 'SUITABLE';
            resultStatusUSER.value = 'SUITABLE';
        } else {
            resultTextUSER.textContent = '-';
            resultStatusUSER.value = '-';
        }
    });
</script>
<script>
    $('#assessmentFormUSER').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: '{{ route('assessmentuser.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                toastr.success("Assessment User berhasil diperbarui!");
                //   alert('Assessment berhasil diperbarui!');
                // Atau pakai toastr.success jika pakai toastr
            },
            error: function(xhr) {
                alert('Gagal menyimpan assessment: ' + xhr.responseText);
            }
        });
    });
</script>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }

    th,
    td {
        border: 1px solid #333;
        padding: 8px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #f0f0f0;
        text-align: center;
        /* <== Ini penting untuk center */
    }
</style>
