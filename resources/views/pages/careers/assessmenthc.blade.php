<form id="assessmentFormHC" method="POST">
    @csrf

    <input type="hidden" name="totalscore" id="totalScoreHCInput" value="0">
    <input type="hidden" name="result_status" id="resultStatusHC" value="NOT SUITABLE">
    <input type="hidden" name="docid" value="{{ $career->docid }}">

    <table>
        <tr>
            <td>
                <strong>Nama Interview</strong><br>(Interview Name) :
                {{ $tr_assessment && $tr_assessment->user ? $tr_assessment->user : $user->name }}
            </td>
            <td colspan="2"></td>
            <td><strong>Posisi yang Diinginkan</strong><br>(Position Applied For)</td>
            <td><strong>STANDAR SCORING :</strong></td>
            <td colspan="2" id="resultTextHC"><strong>NOT SUITABLE</strong></td>
        </tr>
        <tr>
            <td>
                <strong>Tanggal Wawancara</strong><br>(Date of Interview) :
                <input type="date" name="interview_date"
                    value="{{ $tr_assessment && $tr_assessment->assessment_date ? \Carbon\Carbon::parse($tr_assessment->assessment_date)->format('Y-m-d') : '' }}">
            </td>
            <td></td>
            <td>
                <strong>Jam</strong><br>( Time ) :
                <input type="time" name="interview_time"
                    value="{{ $tr_assessment && $tr_assessment->assessment_date ? \Carbon\Carbon::parse($tr_assessment->assessment_date)->format('H:i') : '' }}">
            </td>
            <td></td>
            <td><strong>Jumlah Nilai</strong><br>(Total Score)</td>
            <td id="totalScoreHC"><strong>0</strong></td>
        </tr>
    </table>

    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr class="bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-700">
                <th class="px-4 py-3 text-left font-semibold">Kriteria</th>
                @php
                    // Ambil skor dari group pertama, diasumsikan semua group punya struktur skor yang sama
                    $scoreHeaders = $assessmentGroups[0]['options'] ?? [];
                @endphp
                @foreach ($scoreHeaders as $option)
                    <th>{{ $option['assessment_score'] }}</th>
                @endforeach
                <th class="px-4 py-3 text-left font-semibold">Nilai</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($assessmentGroups as $groupIndex => $group)
                @php
                    $selectedScore = $group['selected_score'] ?? 0; // nilai default jika tidak ada
                @endphp
                <tr>
                    <td style="vertical-align: middle;"><strong>{{ $group['assessment_group'] }}</strong></td>

                    @foreach ($group['options'] as $score => $option)
                        <td style="text-align: center; vertical-align: middle;">
                            <label
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 11px;">
                                <span style="margin-bottom: 4px;">{{ $option['assessment_descr'] }}</span>
                                <input type="radio" name="scores[{{ $groupIndex }}]" value="{{ $score }}"
                                    {{ $selectedScore == $score ? 'checked' : '' }}
                                    onclick="updateScoreHC({{ $groupIndex }}, {{ $score }})">
                            </label>
                        </td>
                    @endforeach

                    <td id="scoreCellHC-{{ $groupIndex }}" style="text-align: center; vertical-align: middle;">
                        {{ $selectedScore }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

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
                        class="whitespace-nowrap border-r border-gray-200 px-6 py-4 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        SUITABLE</td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                        21–28 FOR LEVEL<br class="md:hidden">MANAGER/SUPERVISOR<br>20–25 FOR STAFF
                    </td>
                </tr>
                <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td
                        class="whitespace-nowrap border-r border-gray-200 px-6 py-4 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        CONSIDER</td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">15–19</td>
                </tr>
                <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td
                        class="whitespace-nowrap border-r border-gray-200 px-6 py-4 text-sm font-medium text-gray-900 dark:border-gray-700 dark:text-gray-100">
                        NOT SUITABLE</td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">0–14</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end"> {{-- Container to align button to the right --}}
        <button type="submit"
            class="hover: inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            Save
        </button>
    </div>
</form>



<script>
    const totalGroups = {{ count($assessmentGroups) }};
    let scoreValues = Array(totalGroups).fill(0);

    function updateScoreHC(groupIndex, value) {
        scoreValues[groupIndex] = value;
        document.getElementById(`scoreCellHC-${groupIndex}`).textContent = value;

        const total = scoreValues.reduce((sum, val) => sum + parseInt(val || 0), 0);
        document.getElementById('totalScoreHC').textContent = total;
        document.getElementById('totalScoreHCInput').value = total;

        const resultTextHC = document.getElementById('resultTextHC');

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

        resultTextHC.textContent = result;
        document.getElementById('resultStatusHC').value = result;
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // const radios = document.querySelectorAll('input[type="radio"]:checked');
        const radios = document.querySelectorAll('#assessmentFormHC input[type="radio"]:checked');
        let total = 0;

        radios.forEach((radio) => {
            const groupIndex = parseInt(radio.name.match(/\d+/)[0]);
            const score = parseInt(radio.value);
            scoreValues[groupIndex] = score;
            document.getElementById(`scoreCellHC-${groupIndex}`).textContent = score;
            total += score;
        });

        document.getElementById('totalScoreHC').textContent = total;
        document.getElementById('totalScoreHCInput').value = total;

        const resultTextHC = document.getElementById('resultTextHC');
        const resultStatusHC = document.getElementById('resultStatusHC');

        if (total < 15) {
            resultTextHC.textContent = 'NOT SUITABLE';
            resultStatusHC.value = 'NOT SUITABLE';
        } else if (total <= 19) {
            resultTextHC.textContent = 'CONSIDER';
            resultStatusHC.value = 'CONSIDER';
        } else if (total <= 28) {
            resultTextHC.textContent = 'SUITABLE';
            resultStatusHC.value = 'SUITABLE';
        } else {
            resultTextHC.textContent = '-';
            resultStatusHC.value = '-';
        }
    });
</script>
<script>
    $('#assessmentFormHC').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: '{{ route('assessment.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                toastr.success("Assessment HC berhasil diperbarui!");
                // alert('Assessment berhasil diperbarui!');
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
