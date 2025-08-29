<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <div class="mb-4 flex items-center justify-end sm:mb-0"></div>
        <div class="mb-2 mt-2 rounded-xl bg-white p-6 dark:bg-gray-800">
            {{-- <div class="max-w-6xl mx-auto bg-white   p-6 rounded-lg"> --}}
            <h2 class="mb-4 text-2xl font-bold">Edit Personnel Requisition - {{ $personnel->docid }}</h2>

            <form id="personnelForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700">Company</label>
                        <select name="cpnyid" class="select2 w-full rounded-lg border p-3">
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpnyid }}"
                                    {{ $p->cpnyid == $personnel->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700">Department</label>
                        <select name="departementid" class="select2 w-full rounded-lg border p-3">
                            @foreach ($userdept as $p)
                                <option value="{{ $p->deptname }}"
                                    {{ $p->deptname == $personnel->departementid ? 'selected' : '' }}>
                                    {{ $p->deptname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700">Job Title</label>
                        <input type="text" name="job_title" class="w-full rounded border p-2"
                            value="{{ $personnel->job_title }}">
                    </div>
                    <div>
                        <label class="block text-gray-700">Job Level</label>
                        <select name="job_level" class="select2 w-full rounded-lg border p-3">
                            @foreach ($joblevel as $p)
                                <option value="{{ $p->title_level }}"
                                    {{ $personnel->job_level == $p->title_level ? 'selected' : '' }}>
                                    {{ $p->title_level }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700">Immediate Superior</label>
                        <input type="text" name="immediate_superior" id="immediate_superior"
                            value={{ $personnel->immediate_superior }} class="w-full rounded border p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700">State Position</label>
                        <input type="text" name="state_position" id="state_position"
                            value={{ $personnel->state_position }} class="w-full rounded border p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700">Job Type</label>
                        <select name="job_type" id="job_type" class="w-full rounded border p-2">
                            <option value="" {{ $personnel->job_type == '' ? 'selected' : '' }}></option>
                            <option value="Replacement" {{ $personnel->job_type == 'Replacement' ? 'selected' : '' }}>
                                Replacement</option>
                            <option value="Temporary" {{ $personnel->job_type == 'Temporary' ? 'selected' : '' }}>
                                Temporary</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700">Reason for Vacancy</label>
                        <textarea name="reason_vacancy" id="reason_vacancy" class="w-full rounded border p-2">{{ $personnel->reason_vacancy }}</textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700">Total Number Required</label>
                        <input type="number" name="required" id="required" value={{ $personnel->required }}
                            class="w-full rounded border p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700">Actual</label>
                        <input type="number" name="actual" id="actual" value={{ $personnel->actual }}
                            class="w-full rounded border p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700">The Actual Number</label>
                        <input type="number" name="total_actual" id="total_actual"
                            value={{ $personnel->total_actual }} class="w-full rounded border p-2">
                    </div>
                </div>

                <!-- Job Responsibilities (Editable) -->
                <div class="mt-6">
                    <label class="block text-lg font-semibold">Job Responsibilities</label>
                    <table class="w-full rounded-lg border border-gray-200">
                        <tbody id="responsibilitiesTable">
                            @foreach ($jobres as $key => $resp)
                                <tr>
                                    <td class="border p-3 text-center">{{ $key + 1 }}</td>
                                    <td class="border p-3">
                                        <input type="text" name="responsibilities[]"
                                            class="w-full rounded border p-2"
                                            value="{{ $resp->job_responsibilities_descr }}">
                                    </td>
                                    <td class="border p-3 text-center">
                                        <button type="button"
                                            class="removeResponsibilities rounded bg-red-500 px-3 py-1 text-white">X</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" id="addResponsibilities"
                        class="mt-3 rounded bg-blue-500 px-4 py-2 text-white">
                        + Add Responsibility
                    </button>
                </div>

                <div class="mt-6">
                    <label class="block text-lg font-semibold">Job Qualification</label>
                    <table class="w-full rounded-lg border border-gray-200">
                        <tbody id="qualificationTable">
                            @foreach ($jobqua as $key => $resp)
                                <tr>
                                    <td class="border p-3 text-center">{{ $key + 1 }}</td>
                                    <td class="border p-3">
                                        <input type="text" name="qualification[]" class="w-full rounded border p-2"
                                            value="{{ $resp->job_qualification_descr }}">
                                    </td>
                                    <td class="border p-3 text-center">
                                        <button type="button"
                                            class="removeQualification rounded bg-red-500 px-3 py-1 text-white">X</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" id="addQualification" class="mt-3 rounded bg-blue-500 px-4 py-2 text-white">
                        + Add Qualification
                    </button>
                </div>

                <!-- Attachments -->
                <div class="mt-6">
                    <label class="block text-lg font-semibold">Attachments</label>
                    <div id="attachmentsContainer">
                        @foreach ($attachment as $attach)
                            <div class="attachment-row flex items-center gap-2" data-attachid="{{ $attach->id }}">
                                <a href="{{ url('/attachments/' . $attach->attachfile) }}" target="_blank"
                                    class="text-blue-600 underline">
                                    📎 {{ $attach->name }}
                                </a>
                                <button type="button"
                                    class="removeAttachment2 rounded bg-red-500 px-3 py-1 text-white"
                                    data-id="{{ $attach->id }}">
                                    X
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="addAttachment" class="mt-3 rounded bg-blue-500 px-4 py-2 text-white">
                        + Add Attachment
                    </button>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" id="submitBtn" class="rounded bg-blue-500 px-4 py-2 text-white">
                        Submit Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#personnelForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let url = "{{ route('tasks.update', $personnel->id) }}";

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        alert("Personnel Requisition Updated Successfully!");
                        window.location.href = "/tasks";
                    },
                    error: function(xhr) {
                        alert("Error! Please check the input.");
                    }
                });
            });
        });
    </script>
    <script>
        // Add Responsibility
        $('#addResponsibilities').click(function() {
            let rowCount = $('#responsibilitiesTable tr').length + 1;
            $('#responsibilitiesTable').append(`
                <tr>
                    <td class="p-3 border text-center">${rowCount}</td>
                    <td class="p-3 border">
                        <input type="text" name="responsibilities[]" class="w-full p-2 border rounded">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeResponsibilities bg-red-500 text-white px-3 py-1 rounded">X</button>
                    </td>
                </tr>
            `);
        });

        // Remove Responsibility
        $(document).on('click', '.removeResponsibilities', function() {
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        // Update row numbers after deleting
        function updateRowNumbers() {
            $('#responsibilitiesTable tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Add Qualification
        $('#addQualification').click(function() {
            let rowCount = $('#qualificationTable tr').length + 1;
            $('#qualificationTable').append(`
                <tr>
                    <td class="p-3 border text-center">${rowCount}</td>
                    <td class="p-3 border">
                        <input type="text" name="qualification[]" class="w-full p-2 border rounded">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeQualification bg-red-500 text-white px-3 py-1 rounded">X</button>
                    </td>
                </tr>
            `);
        });

        // Remove Responsibility
        $(document).on('click', '.removeQualification', function() {
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        // Update row numbers after deleting
        function updateRowNumbers() {
            $('#qualificationTable tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }


        // Add Attachment
        $('#addAttachment').click(function() {
            $('#attachmentsContainer').append(`
                <div class="attachment-row flex items-center gap-2">
                    <input type="file" name="attachments[]" class="w-full p-3 text-lg border rounded">
                    <button type="button" class="removeAttachment bg-red-500 text-white px-3 py-1 rounded">X</button>
                </div>
            `);
        });

        // Remove Attachment
        $(document).on('click', '.removeAttachment', function() {
            $(this).closest('.attachment-row').remove();
        });

        $(document).on('click', '.removeAttachment2', function() {
            let attachmentId = $(this).data('id'); // Ambil ID attachment
            let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

            // Cek konfirmasi pengguna
            let confirmDelete = confirm('Are you sure you want to remove this attachment?');

            if (confirmDelete) {
                $.ajax({
                    url: "/tasks/remove-attachment/" + attachmentId, // Endpoint ke controller
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove(); // Hapus dari tampilan jika berhasil
                            alert("Attachment removed successfully!");
                        } else {
                            alert("Failed to remove attachment.");
                        }
                    },
                    error: function(xhr) {
                        alert("Error! Unable to remove attachment.");
                        console.error(xhr.responseText);
                    }
                });
            } else {
                // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
                return false;
            }
        });
    </script>
</x-app-layout>
