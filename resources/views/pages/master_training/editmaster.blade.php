<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        <form method="POST" action="{{ route('mastertraining.update', $training['id']) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- ================= TOAST ================= --}}
            @if ($errors->any())
                <div id="toastError"
                    class="fixed right-6 top-6 z-50 rounded-lg bg-red-600 px-6 py-3 text-white shadow-lg">
                    Please fill all required fields.
                </div>
            @endif

            @if (session('success'))
                <div id="toastSuccess"
                    class="fixed right-6 top-6 z-50 rounded-lg bg-green-600 px-6 py-3 text-white shadow-lg">
                    {{ session('success') }}
                </div>
            @endif


            <div class="grid grid-rows-1 gap-4 lg:grid-rows-[minmax(0,auto)_1fr]">

                {{-- ================= HEADER ================= --}}
                <div class="space-y-2 rounded-xl bg-white p-6 shadow-sm">

                    <div class="border-b pb-4">
                        <h2 class="text-lg font-bold text-gray-800">
                            Edit Training
                        </h2>
                        <p class="text-sm text-gray-500">
                            Update training information
                        </p>
                    </div>

                    <div id="headerGridDynamic"
                        class="grid grid-cols-1 gap-6 transition-all duration-300 md:grid-cols-2">

                        {{-- Name --}}
                        <div>
                            <label class="req text-sm text-gray-500">Training Name</label>
                            <input type="text" name="name" value="{{ old('name', $training['name']) }}" required
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0">
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="req text-sm text-gray-500">Training Type</label>
                            <select name="type" id="trainingType" required
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0">
                                <option value="">Select Type</option>
                                <option value="MANDATORY"
                                    {{ old('type', $training['type']) == 'MANDATORY' ? 'selected' : '' }}>
                                    Mandatory
                                </option>
                                <option value="NON_MANDATORY"
                                    {{ old('type', $training['type']) == 'NON_MANDATORY' ? 'selected' : '' }}>
                                    Non Mandatory
                                </option>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div id="categoryField"
                            class="{{ old('type', $training['type']) == 'NON_MANDATORY' ? '' : 'hidden' }}">
                            <label class="req text-sm text-gray-500">Category</label>
                            <select name="category"
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0">
                                <option value="">Select Category</option>
                                <option value="COMPETENCY"
                                    {{ ($training['category'] ?? '') == 'COMPETENCY' ? 'selected' : '' }}>
                                    Competency
                                </option>
                                <option value="SOFT" {{ ($training['category'] ?? '') == 'SOFT' ? 'selected' : '' }}>
                                    Soft Skill
                                </option>
                                <option value="TECHNICAL"
                                    {{ ($training['category'] ?? '') == 'TECHNICAL' ? 'selected' : '' }}>
                                    Technical
                                </option>
                            </select>
                        </div>

                    </div>

                    {{-- Trainer + Applies --}}
                    <div class="mt-2 grid grid-cols-1 items-end gap-6 md:grid-cols-2">

                        <div>
                            <label class="req text-sm text-gray-500">Trainer Name</label>
                            <input type="text" name="trainer" value="{{ old('trainer', $training['trainer']) }}"
                                required
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0">
                        </div>

                        <div>
                            <label class="text-sm text-gray-500">Applies To</label>
                            <div class="mt-2 flex items-center gap-3 rounded-lg border p-3">
                                <input type="checkbox" id="specificLevelToggle" name="applies_to_specific"
                                    value="1"
                                    {{ old('applies_to_specific', $training['applies_to_specific']) ? 'checked' : '' }}>
                                <label class="text-sm">
                                    Restrict to Specific Levels
                                </label>
                            </div>
                        </div>

                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="text-sm text-gray-500">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-md border border-gray-200 p-3 text-sm focus:border-black focus:ring-0">
{{ old('description', $training['description']) }}
</textarea>
                    </div>

                    {{-- Poster + Activate --}}
                    <div class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2">

                        <div class="flex items-center justify-between rounded-lg border p-4">

                            <div>
                                <label class="text-sm font-medium text-gray-800">
                                    Training Poster
                                </label>
                                <p class="text-xs text-gray-500">PNG or JPG</p>
                            </div>

                            <div class="flex items-center gap-4">

                                @if (!empty($training['poster']))
                                    <img src="{{ asset('storage/' . $training['poster']) }}"
                                        class="h-14 w-14 rounded-lg border object-cover">
                                @endif

                                <label for="posterInput"
                                    class="cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100">
                                    Change
                                </label>

                                <input type="file" name="poster" id="posterInput" class="hidden">

                            </div>

                        </div>

                        <div class="flex items-center justify-between rounded-lg border p-4">

                            <div>
                                <h4 class="text-sm font-medium text-gray-800">
                                    Activate Training
                                </h4>
                                <p class="text-xs text-gray-500">
                                    Only active training visible to users.
                                </p>
                            </div>

                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $training['is_active']) ? 'checked' : '' }}>

                        </div>

                    </div>

                </div>

                {{-- ================= SESSIONS ================= --}}
                <div class="space-y-2 rounded-xl bg-white p-6 shadow-sm">

                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">
                                Training Sessions
                            </h2>
                            <p class="text-sm text-gray-500">
                                At least one session is required
                            </p>
                        </div>

                        <button type="button" onclick="addSessionRow()"
                            class="rounded-md bg-black px-4 py-2 text-sm text-white hover:opacity-90">
                            + Add Session
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-sm">

                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th id="levelHeader" class="hidden border-b px-4 py-3 text-left">Level</th>
                                    <th class="border-b px-4 py-3 text-left">Start Date</th>
                                    <th class="border-b px-4 py-3 text-left">Start Time</th>
                                    <th class="border-b px-4 py-3 text-left">End Time</th>
                                    <th class="border-b px-4 py-3 text-left">Mode</th>
                                    <th class="border-b px-4 py-3 text-left">Location</th>
                                    <th class="border-b px-4 py-3 text-left">Platform</th>
                                    <th class="border-b px-4 py-3 text-left">Link</th>
                                    <th class="border-b px-4 py-3 text-left">Quota</th>
                                    <th class="border-b px-4 py-3 text-center">Active</th>
                                    <th class="border-b px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="sessionTableBody">
                                @foreach ($training['sessions'] as $i => $session)
                                    <tr class="border-b">

                                        <td class="levelColumn hidden px-4 py-2">
                                            <select name="sessions[{{ $i }}][level]"
                                                class="w-full border-b bg-transparent">
                                                <option value="">Select Level</option>
                                                <option value="EXECUTIVE"
                                                    {{ $session['level'] == 'EXECUTIVE' ? 'selected' : '' }}>Executive
                                                </option>
                                                <option value="SR_MANAGER"
                                                    {{ $session['level'] == 'SR_MANAGER' ? 'selected' : '' }}>Sr
                                                    Manager
                                                </option>
                                                <option value="AST_MANAGER"
                                                    {{ $session['level'] == 'AST_MANAGER' ? 'selected' : '' }}>Ast
                                                    Manager
                                                </option>
                                                <option value="SUPERVISOR"
                                                    {{ $session['level'] == 'SUPERVISOR' ? 'selected' : '' }}>
                                                    Supervisor
                                                </option>
                                                <option value="SR_OFFICER"
                                                    {{ $session['level'] == 'SR_OFFICER' ? 'selected' : '' }}>Sr
                                                    Officer
                                                </option>
                                                <option value="OFFICER"
                                                    {{ $session['level'] == 'OFFICER' ? 'selected' : '' }}>Officer
                                                </option>
                                            </select>
                                        </td>

                                        <td class="px-4 py-2">
                                            <input type="date" name="sessions[{{ $i }}][start_date]"
                                                value="{{ $session['start_date'] }}" required
                                                class="w-full border-b bg-transparent">
                                        </td>

                                        <td class="px-4 py-2">
                                            <input type="time" name="sessions[{{ $i }}][start_time]"
                                                value="{{ $session['start_time'] }}" required
                                                class="w-full border-b bg-transparent">
                                        </td>

                                        <td class="px-4 py-2">
                                            <input type="time" name="sessions[{{ $i }}][end_time]"
                                                value="{{ $session['end_time'] }}" required
                                                class="w-full border-b bg-transparent">
                                        </td>

                                        <td class="px-4 py-2">
                                            <select name="sessions[{{ $i }}][mode]"
                                                class="modeSelect w-full border-b bg-transparent" required>
                                                <option value="">Select Mode</option>
                                                <option value="ONLINE"
                                                    {{ $session['mode'] == 'ONLINE' ? 'selected' : '' }}>
                                                    Online</option>
                                                <option value="OFFLINE"
                                                    {{ $session['mode'] == 'OFFLINE' ? 'selected' : '' }}>Offline
                                                </option>
                                                <option value="HYBRID"
                                                    {{ $session['mode'] == 'HYBRID' ? 'selected' : '' }}>
                                                    Hybrid</option>
                                            </select>
                                        </td>

                                        <td class="px-4 py-2">
                                            <input type="text" name="sessions[{{ $i }}][location]"
                                                value="{{ $session['location'] ?? '' }}"
                                                class="locationInput hidden w-full border-b bg-transparent">
                                        </td>

                                        <td class="px-4 py-2">
                                            <select name="sessions[{{ $i }}][platform]"
                                                class="platformInput hidden w-full border-b bg-transparent">
                                                <option value="">Platform</option>
                                                <option value="ZOOM"
                                                    {{ $session['platform'] == 'ZOOM' ? 'selected' : '' }}>Zoom
                                                </option>
                                                <option value="TEAMS"
                                                    {{ $session['platform'] == 'TEAMS' ? 'selected' : '' }}>Teams
                                                </option>
                                                <option value="GOOGLE_MEET"
                                                    {{ $session['platform'] == 'GOOGLE_MEET' ? 'selected' : '' }}>
                                                    Google Meet
                                                </option>
                                            </select>
                                        </td>

                                        <td class="px-4 py-2">
                                            <input type="url" name="sessions[{{ $i }}][meeting_link]"
                                                value="{{ $session['meeting_link'] ?? '' }}"
                                                class="linkInput hidden w-full border-b bg-transparent">
                                        </td>

                                        <td class="px-4 py-2">
                                            <input type="number" name="sessions[{{ $i }}][quota]"
                                                value="{{ $session['quota'] }}" min="1" required
                                                class="w-full border-b bg-transparent">
                                        </td>

                                        <td class="px-4 py-2 text-center">
                                            <input type="checkbox" name="sessions[{{ $i }}][is_active]"
                                                value="1" {{ $session['is_active'] ? 'checked' : '' }}>
                                        </td>

                                        <td class="px-4 py-2 text-center">
                                            <button type="button" onclick="removeRow(this)"
                                                class="text-xs text-red-500">
                                                Remove
                                            </button>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end gap-4 border-t pt-6">
                        <a href="{{ route('mastertraining.index') }}"
                            class="rounded-md border border-gray-300 px-5 py-2 text-sm text-gray-600 hover:bg-gray-100">
                            Cancel
                        </a>

                        <button type="submit"
                            class="rounded-md bg-black px-6 py-2 text-sm text-white hover:opacity-90">
                            Update Training
                        </button>
                    </div>

                </div>

            </div>
        </form>
    </div>


    @if (session('success'))
        <div id="toastSuccess"
            class="fixed right-6 top-6 z-50 rounded-lg bg-green-600 px-6 py-3 text-white shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let sessionIndex = document.querySelectorAll('#sessionTableBody tr').length || 1;

            const typeSelect = document.getElementById('trainingType');
            const categoryField = document.getElementById('categoryField');
            const headerGrid = document.getElementById('headerGridDynamic');
            const specificToggle = document.getElementById('specificLevelToggle');
            const posterInput = document.getElementById('posterInput');
            const posterPreview = document.getElementById('posterPreview');
            const levelHeader = document.getElementById('levelHeader');
            const posterPreviewContainer = document.getElementById('posterPreviewContainer');


            /* ===============================
               TRAINING TYPE TOGGLE
            =============================== */
            function handleTrainingType() {

                if (!typeSelect) return;

                if (typeSelect.value === 'NON_MANDATORY') {

                    categoryField.classList.remove('hidden');
                    headerGrid.classList.remove('md:grid-cols-2');
                    headerGrid.classList.add('md:grid-cols-3');

                } else {

                    categoryField.classList.add('hidden');
                    headerGrid.classList.remove('md:grid-cols-3');
                    headerGrid.classList.add('md:grid-cols-2');
                }
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', handleTrainingType);
                handleTrainingType();
            }


            /* ===============================
               LEVEL COLUMN TOGGLE
            =============================== */
            function toggleLevelColumn(isSpecific) {
                const levelCells = document.querySelectorAll('.levelColumn');

                if (isSpecific) {
                    levelHeader.classList.remove('hidden');
                    levelCells.forEach(cell => cell.classList.remove('hidden'));
                } else {
                    levelHeader.classList.add('hidden');
                    levelCells.forEach(cell => cell.classList.add('hidden'));
                }

                applyLevelRequired(isSpecific);
            }

            if (specificToggle) {
                specificToggle.addEventListener('change', function() {
                    toggleLevelColumn(this.checked);
                });

                toggleLevelColumn(specificToggle.checked);
            }


            /* ===============================
               POSTER PREVIEW
            =============================== */
            if (posterInput) {

                posterInput.addEventListener('change', function(event) {

                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();

                    reader.onload = function(e) {
                        posterPreview.src = e.target.result;
                        posterPreviewContainer.classList.remove('hidden');
                    };

                    reader.readAsDataURL(file);
                });
            }


            /* ===============================
               ADD SESSION ROW
            =============================== */
            window.addSessionRow = function() {

                const tbody = document.getElementById('sessionTableBody');

                const row = `
            <tr class="border-b">

                <td class="levelColumn hidden px-4 py-2">
                    <select name="sessions[${sessionIndex}][level]"
                        class="w-full border-0 border-b border-gray-200 bg-transparent py-1 focus:border-black focus:ring-0 dark:border-gray-600">
                        <option value="">Select Level</option>
                        <option value="EXECUTIVE">Executive</option>
                        <option value="SR_MANAGER">Sr Manager</option>
                        <option value="AST_MANAGER">Ast Manager</option>
                        <option value="SUPERVISOR">Supervisor</option>
                        <option value="SR_OFFICER">Sr Officer</option>
                        <option value="OFFICER">Officer</option>
                    </select>
                </td>

                <td class="px-4 py-2">
                    <input type="date" name="sessions[${sessionIndex}][start_date]" required
                        class="w-full border-0 border-b border-gray-200 bg-transparent py-1 focus:border-black focus:ring-0 dark:border-gray-600">
                </td>

                <td class="px-4 py-2">
                    <input type="time" name="sessions[${sessionIndex}][start_time]" required
                        class="w-full border-0 border-b border-gray-200 bg-transparent py-1 focus:border-black focus:ring-0 dark:border-gray-600">
                </td>

                <td class="px-4 py-2">
                    <input type="time" name="sessions[${sessionIndex}][end_time]"
                        class="w-full border-0 border-b border-gray-200 bg-transparent py-1 focus:border-black focus:ring-0 dark:border-gray-600">
                </td>

                <td class="px-4 py-2">
                    <select name="sessions[${sessionIndex}][mode]" class="modeSelect w-full border-b bg-transparent" required>
                        <option value="">Select Mode</option>
                        <option value="ONLINE">Online</option>
                        <option value="OFFLINE">Offline</option>
                        <option value="HYBRID">Hybrid</option>
                    </select>
                </td>

                <td class="px-4 py-2">
                    <input type="text" name="sessions[${sessionIndex}][location]" class="locationInput hidden w-full border-b bg-transparent">
                </td>

                <td class="px-4 py-2">
                    <select name="sessions[${sessionIndex}][platform]" class="platformInput hidden w-full border-b bg-transparent">
                        <option value="">Platform</option>
                        <option value="ZOOM">Zoom</option>
                        <option value="TEAMS">Teams</option>
                        <option value="GOOGLE_MEET">Google Meet</option>
                    </select>
                </td>

                <td class="px-4 py-2">
                    <input type="url" name="sessions[${sessionIndex}][meeting_link]" class="linkInput hidden w-full border-b bg-transparent">
                </td>

                <td class="px-4 py-2">
                    <input type="number" name="sessions[${sessionIndex}][quota]" min="1" required
                        class="w-full border-0 border-b border-gray-200 bg-transparent py-1 focus:border-black focus:ring-0 dark:border-gray-600">
                </td>

                <td class="px-4 py-2 text-center">
                    <input type="checkbox" name="sessions[${sessionIndex}][is_active]" value="1" checked>
                </td>

                <td class="px-4 py-2 text-center">
                    <button type="button"
                        onclick="removeRow(this)"
                        class="text-xs text-red-500 hover:text-red-700">
                        Remove
                    </button>
                </td>

            </tr>
        `;

                tbody.insertAdjacentHTML('beforeend', row);
                sessionIndex++;

                if (specificToggle) {
                    toggleLevelColumn(specificToggle.checked);
                }
            };


            /* ===============================
               REMOVE SESSION ROW
            =============================== */
            window.removeRow = function(button) {

                const tbody = document.getElementById('sessionTableBody');

                if (tbody.rows.length <= 1) {
                    alert("At least one session is required.");
                    return;
                }

                button.closest('tr').remove();
            };

        });

        if (posterInput) {
            posterInput.addEventListener('change', function(event) {

                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();

                reader.onload = function(e) {
                    posterPreview.src = e.target.result;
                    posterPreviewContainer.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            });
        }

        function applyLevelRequired(isSpecific) {
            const levelSelects = document.querySelectorAll('.levelColumn select');

            levelSelects.forEach(select => {
                if (isSpecific) {
                    select.setAttribute('required', 'required');
                } else {
                    select.removeAttribute('required');
                }
            });
        }

        function autoHideToast(id) {
            const el = document.getElementById(id);
            if (!el) return;

            setTimeout(() => {
                el.style.transition = "opacity 0.5s ease";
                el.style.opacity = "0";
                setTimeout(() => el.remove(), 500);
            }, 3000);
        }

        autoHideToast('toastSuccess');
        autoHideToast('toastError');

        document.querySelector('form').addEventListener('submit', function(e) {

            const rows = document.querySelectorAll('#sessionTableBody tr');
            if (rows.length === 0) {
                e.preventDefault();
                alert('At least one session is required.');
            }

        });

        document.addEventListener('change', function(e) {

            if (!e.target.classList.contains('modeSelect')) return;

            const row = e.target.closest('tr');
            const mode = e.target.value;

            const locationInput = row.querySelector('.locationInput');
            const platformInput = row.querySelector('.platformInput');
            const linkInput = row.querySelector('.linkInput');

            locationInput.classList.add('hidden');
            platformInput.classList.add('hidden');
            linkInput.classList.add('hidden');

            locationInput.removeAttribute('required');
            platformInput.removeAttribute('required');
            linkInput.removeAttribute('required');

            if (mode === 'ONLINE') {

                platformInput.classList.remove('hidden');
                linkInput.classList.remove('hidden');

                platformInput.setAttribute('required', 'required');
                linkInput.setAttribute('required', 'required');

            } else if (mode === 'OFFLINE') {

                locationInput.classList.remove('hidden');
                locationInput.setAttribute('required', 'required');

            } else if (mode === 'HYBRID') {

                locationInput.classList.remove('hidden');
                platformInput.classList.remove('hidden');
                linkInput.classList.remove('hidden');

                locationInput.setAttribute('required', 'required');
                platformInput.setAttribute('required', 'required');
                linkInput.setAttribute('required', 'required');
            }
        });
    </script>
</x-app-layout>
