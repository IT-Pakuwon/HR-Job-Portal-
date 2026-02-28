<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">

        <form method="POST" action="{{ route('mastertraining.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div id="toastError"
                    class="fixed right-6 top-6 z-50 rounded-lg bg-red-600 px-6 py-3 text-white shadow-lg">
                    Please fill all required fields.
                </div>
            @endif
            <div class="grid grid-rows-1 gap-4 lg:grid-rows-2 lg:grid-rows-[minmax(0,auto)_1fr]">
                <!-- HEADER CONTAINER -->

                <div class="space-y-2 rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">

                    <div class="border-b pb-4">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                            Training Header
                        </h2>
                        <p class="text-sm text-gray-500">
                            General training information
                        </p>
                    </div>

                    <!-- ================= HEADER GRID A (Dynamic) ================= -->
                    <div id="headerGridDynamic"
                        class="grid grid-cols-1 gap-6 transition-all duration-300 md:grid-cols-2">

                        <!-- Training Name -->
                        <div>
                            <label class="req text-sm text-gray-500">Training Name</label>
                            <input type="text" name="name" required
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0 dark:border-gray-600">
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="req text-sm text-gray-500">Training Type</label>
                            <select name="type" id="trainingType" required
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0 dark:border-gray-600">
                                <option value="">Select Type</option>
                                <option value="MANDATORY">Mandatory</option>
                                <option value="NON_MANDATORY">Non Mandatory</option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div id="categoryField" class="hidden">
                            <label class="req text-sm text-gray-500">Category</label>
                            <select name="category"
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0 dark:border-gray-600">
                                <option value="">Select Category</option>
                                <option value="COMPETENCY">Competency</option>
                                <option value="SOFT">Soft Skill</option>
                                <option value="TECHNICAL">Technical</option>
                            </select>
                        </div>

                    </div>


                    <!-- ================= HEADER GRID B (Always 2 Columns) ================= -->
                    <div class="mt-2 grid grid-cols-1 items-end gap-6 md:grid-cols-2">

                        <!-- Trainer -->
                        <div>
                            <label class="req text-sm text-gray-500">Trainer Name</label>
                            <input type="text" name="trainer"
                                class="w-full border-0 border-b border-gray-200 bg-transparent py-2 focus:border-black focus:ring-0 dark:border-gray-600"
                                required>
                        </div>


                        <!-- Applies To -->
                        <div>
                            <div class="flex items-center gap-2">
                                <label class="req text-sm text-gray-500">Applies To</label>

                                <!-- Tooltip -->
                                <div class="group relative cursor-pointer">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                                    </svg>

                                    <div
                                        class="absolute left-6 top-0 hidden w-64 rounded-md bg-black p-3 text-xs text-white shadow-lg group-hover:block">
                                        <p>
                                            <strong>Unchecked:</strong> Session applies to ALL levels.<br><br>
                                            <strong>Checked:</strong> You must select a level in each session.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 flex items-center gap-3 rounded-lg border p-3">
                                <input type="checkbox" id="specificLevelToggle" name="applies_to_specific"
                                    value="1" class="h-5 w-5">

                                <label for="specificLevelToggle" class="text-sm text-gray-700 dark:text-gray-200">
                                    Restrict to Specific Levels
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- Description -->
                    <div>
                        <label class="text-sm text-gray-500">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-md border border-gray-200 p-3 text-sm focus:border-black focus:ring-0 dark:border-gray-600"></textarea>
                    </div>

                    <!-- ================= POSTER + STATUS (COMPACT ROW) ================= -->
                    <div class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2">

                        <!-- Poster -->
                        <div class="flex items-center justify-between rounded-lg border p-4">

                            <div>
                                <label class="req text-sm font-medium text-gray-800 dark:text-white">
                                    Training Poster
                                </label>
                                <p class="text-xs text-gray-500">
                                    PNG or JPG (Max 2MB)
                                </p>
                            </div>

                            <div class="flex items-center gap-4">

                                <!-- Preview Thumbnail -->
                                <div id="posterPreviewContainer" class="hidden">
                                    <img id="posterPreview"
                                        class="h-14 w-14 rounded-lg border border-gray-200 object-cover dark:border-gray-600">
                                </div>

                                <!-- Upload Button -->
                                <label for="posterInput"
                                    class="cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                                    Upload
                                </label>

                                <input id="posterInput" type="file" name="poster" accept="image/*" required
                                    class="hidden">

                            </div>

                        </div>

                        <!-- Activate -->
                        <div class="flex items-center justify-between rounded-lg border p-4">

                            <div>
                                <h4 class="text-sm font-medium text-gray-800 dark:text-white">
                                    Activate Training
                                </h4>
                                <p class="text-xs text-gray-500">
                                    Only active training will be visible to users.
                                </p>
                            </div>

                            <input type="checkbox" name="is_active" value="1" class="h-5 w-5">
                        </div>

                    </div>

                </div>

                <!-- DETAIL CONTAINER -->
                <div class="space-y-2 rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">

                    <div class="flex items-center justify-between border-b pb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">
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
                        <table class="min-w-full border border-gray-200 text-sm dark:border-gray-700">

                            <thead class="bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-200">
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

                                <!-- Default First Row -->
                                <tr class="border-b">

                                    <td class="levelColumn hidden px-4 py-2">
                                        <select name="sessions[0][level]" class="w-full border-b bg-transparent">
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
                                        <input type="date" name="sessions[0][start_date]" required
                                            class="w-full border-b bg-transparent">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="time" name="sessions[0][start_time]" required
                                            class="w-full border-b bg-transparent">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="time" name="sessions[0][end_time]" required
                                            class="w-full border-b bg-transparent">
                                    </td>

                                    <!-- MODE -->
                                    <td class="px-4 py-2">
                                        <select name="sessions[0][mode]"
                                            class="modeSelect w-full border-b bg-transparent" required>
                                            <option value="">Select Mode</option>
                                            <option value="ONLINE">Online</option>
                                            <option value="OFFLINE">Offline</option>
                                            <option value="HYBRID">Hybrid</option>
                                        </select>
                                    </td>

                                    <!-- LOCATION -->
                                    <td class="px-4 py-2">
                                        <input type="text" name="sessions[0][location]"
                                            class="locationInput hidden w-full border-b bg-transparent">
                                    </td>

                                    <!-- PLATFORM -->
                                    <td class="px-4 py-2">
                                        <select name="sessions[0][platform]"
                                            class="platformInput hidden w-full border-b bg-transparent">
                                            <option value="">Platform</option>
                                            <option value="ZOOM">Zoom</option>
                                            <option value="TEAMS">Teams</option>
                                            <option value="GOOGLE_MEET">Google Meet</option>
                                        </select>
                                    </td>

                                    <!-- LINK -->
                                    <td class="px-4 py-2">
                                        <input type="url" name="sessions[0][meeting_link]"
                                            class="linkInput hidden w-full border-b bg-transparent">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number" name="sessions[0][quota]" min="1" required
                                            class="w-full border-b bg-transparent">
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" name="sessions[0][is_active]" value="1" checked>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <button type="button" onclick="removeRow(this)"
                                            class="text-xs text-red-500">
                                            Remove
                                        </button>
                                    </td>

                                </tr>

                            </tbody>

                        </table>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="mt-6 flex justify-end gap-4 border-t pt-6">

                        <!-- Cancel -->
                        <button type="button" onclick="history.back()"
                            class="rounded-md border border-gray-300 px-5 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </button>

                        <!-- Save -->
                        <button type="submit"
                            class="rounded-md bg-black px-6 py-2 text-sm text-white hover:opacity-90">
                            Save Training
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
