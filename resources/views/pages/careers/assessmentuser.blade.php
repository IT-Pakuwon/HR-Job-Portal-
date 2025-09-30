<form id="assessmentFormUSER" method="POST">
    @csrf

    <input type="hidden" name="totalscore" id="totalScoreUSERInput" value="0">
    <input type="hidden" name="result_status" id="resultStatusUSER" value="NOT SUITABLE">
    <input type="hidden" name="docid" value="{{ $career->docid }}">

    <table>
        <tr>
            {{-- <td><strong>Nama Interview</strong><br>(Interview Name) : {{ $user->name }}</td> --}}
            <td>
                <strong>Nama Interview</strong><br>(Interview Name) :
                {{ $tr_assessment_user && $tr_assessment_user->user ? $tr_assessment_user->user : $user->name }}
            </td>
            <td colspan="2"></td>
            <td><strong>Posisi yang Diinginkan</strong><br>(Position Applied For)</td>
            <td><strong>STANDAR SCORING :</strong></td>
            <td colspan="2" id="resultTextUSER"><strong>NOT SUITABLE</strong></td>
        </tr>
        <tr>
            <td>
                <strong>Tanggal Wawancara</strong><br>(Date of Interview) :
                <input type="date" name="interview_date"
                    value="{{ $tr_assessment_user && $tr_assessment_user->assessment_date ? \Carbon\Carbon::parse($tr_assessment_user->assessment_date)->format('Y-m-d') : '' }}" required>
            </td>
            <td></td>
            <td>
                <strong>Jam</strong><br>( Time ) :
                <input type="time" name="interview_time"
                    value="{{ $tr_assessment_user && $tr_assessment_user->assessment_date ? \Carbon\Carbon::parse($tr_assessment_user->assessment_date)->format('H:i') : '' }}" required>
            </td>
            <td></td>
            <td><strong>Jumlah Nilai</strong><br>(Total Score)</td>
            <td id="totalScoreUSER"><strong>0</strong></td>
        </tr>
    </table>

    <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr class="text-gray-600 dark:text-gray-700">
                <th>Kriteria</th>
                <th>0</th>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assessmentGroupsUser as $groupIndex => $group)
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
                                    onclick="updateScoreUSER({{ $groupIndex }}, {{ $score }})">
                            </label>
                        </td>
                    @endforeach

                    <td id="scoreCellUSER-{{ $groupIndex }}" style="text-align: center; vertical-align: middle;">
                        {{ $selectedScore }}</td>
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
            class="hover: inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
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
