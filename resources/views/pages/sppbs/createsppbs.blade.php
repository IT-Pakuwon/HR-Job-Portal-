<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="sppbForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Create SPPB</h2>
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>                                
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" name="departementid" required>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->deptname }}"
                                            {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>
                                            {{ $p->deptname }}</option>
                                    @endforeach
                                </select>
                            </div>           
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sub Departement</label>
                                <input type="text" name="departement_name" id="departement_name" class="w-full rounded-lg border border-white-300 bg-white-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" required>
                            </div>             
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wo ID</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" name="subgrade_name" required>
                                    <option value="" disabled selected>Select Wo ID</option>
                                    @foreach ($subgrading as $p)
                                        <option value="{{ $p->subgrade_name }}"> {{ $p->subgrade_name }}</option>
                                    @endforeach
                                </select>
                            </div>   
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">                           
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">Descr</label>
                            <textarea name="keperluan" id="keperluan" class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800" required></textarea>
                        </div>
                    </div>    
                    <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-2xl p-4">
                            <details class="group" open>
                            <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                <span class="text-lg font-semibold">SPPB Detail</span>
                                <span class="transition-all group-open:hidden">See details</span>
                                <span class="hidden transition-all group-open:inline">Hide details</span>
                            </summary>
                            <div class="flex h-auto flex-col justify-start">
                                <div class="overflow-x-auto">
                                <table class="mb-4 mt-3 w-full">
                                    <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="w-12 border p-3 text-center">No</th>
                                        <th class="border p-3">Product Type</th>
                                        <th class="border p-3">Product Name</th>
                                        <th class="w-28 border p-3 text-center">Qty</th>
                                        <th class="w-28 border p-3">UoM</th>
                                        <th class="border p-3">COA</th>
                                        <th class="border p-3">Sub COA</th>
                                        <th class="border p-3">Location</th>
                                        <th class="border p-3">Sub Location</th>
                                        <th class="border p-3">Note</th>
                                        <th class="w-16 border p-3 text-center"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="sppbTable">
                                    <tr class="sppb-row">
                                        <td class="border p-3 text-center">1</td>
                                        <td class="border p-3">
                                        <input type="text" name="item_type[]" placeholder="Product Type"
                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                        </td>

                                        <!-- Product Name (will be populated by JS) -->
                                        <td class="border p-3">
                                        <select name="product_id[]" class="productSelect w-full bg-transparent p-2 focus:outline-none">
                                            <option value="">-- Select Product --</option>
                                        </select>
                                        </td>

                                        <!-- Qty -->
                                        <td class="border p-3 text-center">
                                        <input type="number" step="0.01" min="0" name="qty[]"
                                                class="w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0"
                                                placeholder="0.00">
                                        </td>

                                        <!-- UoM auto-filled -->
                                        <td class="border p-3">
                                        <input type="text" name="uom[]" readonly
                                                class="uomField w-full cursor-not-allowed border-none bg-gray-50 p-2 text-gray-600 focus:outline-none"
                                                placeholder="-">
                                        </td>

                                        <!-- COA & Sub COA -->
                                        <td class="border p-3">
                                        <input type="text" name="coa[]" placeholder="COA"
                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                        </td>
                                        <td class="border p-3">
                                        <input type="text" name="sub_coa[]" placeholder="Sub COA"
                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                        </td>

                                        <!-- Location & Sub Location (optional dropdowns, boleh teks) -->
                                        <td class="border p-3">
                                        <input type="text" name="location[]" placeholder="Location"
                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                        </td>
                                        <td class="border p-3">
                                        <input type="text" name="sub_location[]" placeholder="Sub Location"
                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                        </td>

                                        <!-- Note -->
                                        <td class="border p-3">
                                        <input type="text" name="note[]" placeholder="Note"
                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                        </td>

                                        <td class="border p-3 text-center">
                                        <button type="button"
                                                class="removeSppb hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                </div>

                                <button type="button" id="addSppb"
                                        class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Row
                                </button>
                            </div>
                            </details>
                        </div>
                    </div>


                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex max-h-[125px] flex-col overflow-y-auto pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg> Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <button type="button" id="cancelBtn"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <span id="cancelText">Cancel</span>
                                <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                            <button type="submit" id="submitBtn"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span id="btnText">Submit Approval</span>
                                <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                Sppb Created Successfully!
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#sppbForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: "{{ route('sppbs.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#sppbForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Sppb Requisition Submit Successfully!");
                        window.location.href = "/sppbs";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                    }
                });
            });

            $('#cancelBtn').click(function() {
                const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");

                if (confirmed) {
                    $('#cancelBtn').attr('disabled', true);
                    $('#cancelText').text('Cancelling...');
                    $('#cancelSpinner').removeClass('hidden');

                    // Redirect to /news
                    window.location.href = "{{ route('sppbs') }}";
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
        let sppbcount = 1;
        let INVENTORY_CACHE = []; // [{id,name,uom}, ...]

        // 2.1 Load inventory once (adjust URL to your route)
        function loadInventory() {
            return $.getJSON('/api/inventory/options') // <-- Ganti sesuai route kamu
            .then(function (res) {
                // Expected: res = [{id:1,name:"Item A",uom:"PCS"}, ...]
                INVENTORY_CACHE = Array.isArray(res) ? res : (res.data || []);
            })
            .catch(function () {
                console.error('Failed to load inventory');
                INVENTORY_CACHE = [];
            });
        }

        // 2.2 Build <option> list from cache
        function buildProductOptions() {
            let opts = '<option value="">-- Select Product --</option>';
            INVENTORY_CACHE.forEach(item => {
            const u = item.uom || '';
            opts += `<option value="${item.id}" data-uom="${u}">${item.name}</option>`;
            });
            return opts;
        }

        // 2.3 Apply options into a <select>
        function fillSelectWithInventory($select) {
            $select.html(buildProductOptions());
        }

        // 2.4 When product changes, set UOM
        $(document).on('change', '.productSelect', function () {
            const uom = $(this).find('option:selected').data('uom') || '';
            const $row = $(this).closest('tr');
            $row.find('.uomField').val(uom || '-');
        });

        // 2.5 Add new row
        $('#addSppb').on('click', function () {
            sppbcount++;
            const newRow = `
            <tr class="sppb-row">
                <td class="p-3 border text-center">${sppbcount}</td>

                <td class="p-3 border">
                <input type="text" name="item_type[]" placeholder="Product Type"
                        class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                </td>

                <td class="p-3 border">
                <select name="product_id[]" class="productSelect w-full bg-transparent p-2 focus:outline-none">
                    ${buildProductOptions()}
                </select>
                </td>

                <td class="p-3 border text-center">
                <input type="number" step="0.01" min="0" name="qty[]"
                        class="w-full border-none bg-transparent p-2 text-right focus:outline-none focus:ring-0" placeholder="0.00">
                </td>

                <td class="p-3 border">
                <input type="text" name="uom[]" readonly
                        class="uomField w-full cursor-not-allowed border-none bg-gray-50 p-2 text-gray-600 focus:outline-none" placeholder="-">
                </td>

                <td class="p-3 border">
                <input type="text" name="coa[]" placeholder="COA"
                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                </td>
                <td class="p-3 border">
                <input type="text" name="sub_coa[]" placeholder="Sub COA"
                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                </td>

                <td class="p-3 border">
                <input type="text" name="location[]" placeholder="Location"
                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                </td>
                <td class="p-3 border">
                <input type="text" name="sub_location[]" placeholder="Sub Location"
                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                </td>

                <td class="p-3 border">
                <input type="text" name="note[]" placeholder="Note"
                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                </td>

                <td class="p-3 border text-center">
                <button type="button" class="removeSppb bg-red-200/10 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                </td>
            </tr>`;
            $('#sppbTable').append(newRow);
            updateRemoveButtons();
        });

        // 2.6 Remove row
        $(document).on('click', '.removeSppb', function () {
            $(this).closest('.sppb-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Helpers
        function updateRowNumbers() {
            sppbcount = 0;
            $('#sppbTable tr').each(function () {
            sppbcount++;
            $(this).find('td:first').text(sppbcount);
            });
        }
        function updateRemoveButtons() {
            if ($('.sppb-row').length > 1) {
            $('.removeSppb').removeClass('hidden');
            } else {
            $('.removeSppb').addClass('hidden');
            }
        }

        // 2.7 Init: load inventory, then fill the first row’s select
        loadInventory().then(function () {
            fillSelectWithInventory($('#sppbTable .sppb-row:first .productSelect'));
        });

        updateRemoveButtons();
        });
    </script>


    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }
        });
    </script>   
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>        

</x-app-layout>
