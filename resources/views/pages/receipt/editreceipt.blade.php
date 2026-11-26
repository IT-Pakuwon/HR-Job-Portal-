 <x-app-layout>
    <x-wh-form-styles />

     <div class="max-w-9xl mx-auto w-full p-2">
         <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
             <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                 {{-- ====== FORM EDIT RECEIPT ====== --}}
                 <form id="receiptEditForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                     @csrf
                     @method('PUT')
                     <input type="hidden" name="receiptnbr" value="{{ $rcp->receiptnbr }}">
                     <input type="hidden" name="ponbr" value="{{ $rcp->ponbr }}">
                     <input type="hidden" name="receipttype" value="{{ $rcp->receipttype }}">

                     {{-- ===== Header (readonly) ===== --}}
                     <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                         <div class="border-b border-gray-100 pb-4 dark:border-gray-700">
                             <div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></span><h2 class="text-lg font-bold text-gray-800 dark:text-white">
                                 Edit Receipt
                                 <span
                                     class="ml-2 rounded bg-gray-100 px-2 py-0.5 text-sm font-medium dark:bg-gray-700">
                                     ({{ strtoupper($rcp->receipttype) }})
                                 </span>
                             </h2></div>
                             <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hanya Qty & Attachment yang dapat
                                 diubah saat status Revise.</p>
                         </div>

                         <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                             <div class="flex flex-col gap-1.5">
                                 <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt
                                     Nbr</label>
                                 <input type="text" value="{{ $rcp->receiptnbr }}" readonly
                                     class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                             </div>
                             <div class="flex flex-col gap-1.5">
                                 <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipt
                                     Date</label>
                                 <input type="text"
                                     value="{{ \Carbon\Carbon::parse($rcp->receiptdate)->format('Y-m-d') }}" readonly
                                     class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                             </div>
                             <div class="flex flex-col gap-1.5">
                                 <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO
                                     Nbr</label>
                                 <input type="text" value="{{ $rcp->ponbr }}" readonly
                                     class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                             </div>
                             <div class="flex flex-col gap-1.5">
                                 <label
                                     class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                 <input type="text" value="{{ $rcp->cpny_id }}" readonly
                                     class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                             </div>
                         </div>
                     </div>

                     {{-- ===== Detail (Qty editable) ===== --}}
                     @php $isReturn = strtolower((string)$rcp->receipttype) === 'return'; @endphp
                     <div class="flex w-full flex-col gap-2 rounded-xl border-b">
                         <div class="flex w-full flex-col rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                             <details class="group" open>
                                 <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span><span>Receipt Detail</span></span>
                                     <span
                                         class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                         details &rarr;</span>
                                     <span
                                         class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                         details &darr;</span>
                                 </summary>

                                 <div class="mt-4 overflow-x-auto rounded-xl border border-gray-100 dark:border-white/[0.06]">
                                     <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                         <thead class="bg-gray-50/70 dark:bg-white/[0.02]">
                                             <tr>
                                                 <th
                                                     class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     Line</th>
                                                 <th
                                                     class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     Inventory ID</th>
                                                 <th
                                                     class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     Description</th>
                                                 <th
                                                     class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     {{ $isReturn ? 'Qty Return (Current)' : 'Qty Received (Current)' }}
                                                 </th>
                                                 <th
                                                     class="px-4 py-3 text-center text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     UoM</th>
                                                 <th
                                                     class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     {{ $isReturn ? 'Qty Return Edit' : 'Qty Received Edit' }}</th>
                                                 <th
                                                     class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                                                     Site</th>
                                             </tr>
                                         </thead>
                                         <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                             @forelse($details as $d)
                                                 @php
                                                     $currentQty = $isReturn
                                                         ? (float) ($d->qty_return ?? 0)
                                                         : (float) ($d->qty_received ?? 0);
                                                     $currentQtyStr = rtrim(
                                                         rtrim(number_format($currentQty, 2, '.', ''), '0'),
                                                         '.',
                                                     );
                                                 @endphp
                                                 <tr>
                                                     <td class="px-4 py-3">{{ $d->receipt_no ?? $loop->iteration }}
                                                     </td>
                                                     <td class="px-4 py-3">{{ $d->inventoryid }}</td>
                                                     <td class="px-4 py-3">{{ $d->inventory_descr }}</td>
                                                     <td class="px-4 py-3 text-right">
                                                         {{ number_format($currentQty, 2) }}</td>
                                                     <td class="px-4 py-3 text-center">{{ $d->uom }}</td>
                                                     <td class="px-4 py-3 text-right">
                                                         <input type="hidden" name="detail_id[]"
                                                             value="{{ $d->id }}">
                                                         <input type="text"
                                                             name="{{ $isReturn ? "qty_return[$d->id]" : "qty_received[$d->id]" }}"
                                                             value="{{ $currentQtyStr }}"
                                                             class="qtyEdit wh-qty"
                                                             inputmode="decimal" autocomplete="off"
                                                             placeholder="0.00" />
                                                     </td>
                                                     <td class="px-4 py-3">
                                                         <select name="siteid[{{ $d->id }}]"
                                                             class="siteSelect wh-site"
                                                             data-cpny-id="{{ $rcp->cpny_id }}"
                                                             data-current-site="{{ $d->siteid }}" data-loaded="0"
                                                             aria-label="Select site for {{ $d->inventoryid }}">
                                                             @if ($d->siteid)
                                                                 <option value="{{ $d->siteid }}" selected>
                                                                     {{ $d->siteid }}</option>
                                                             @else
                                                                 <option value="" selected disabled>Select site…
                                                                 </option>
                                                             @endif
                                                         </select>
                                                     </td>
                                                 </tr>
                                             @empty
                                                 <tr>
                                                     <td colspan="7" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No
                                                         receipt detail</td>
                                                 </tr>
                                             @endforelse
                                         </tbody>
                                     </table>
                                 </div>
                             </details>
                         </div>
                     </div>

                     {{-- ===== Attachments (show existing + add new) ===== --}}
                     <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                         <details class="group" open>
                             <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                    <span>
                                        <span class="block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                            <div class="flex flex-col gap-3 px-6 py-5">

                             {{-- Existing attachments (signed URL) --}}
                             <div id="attachmentsList" class="flex flex-col gap-3">
                                 @forelse ($attachments as $att)
                                     <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                         data-id="{{ $att->id }}">
                                         <div class="flex min-w-0 items-center gap-3">
                                             <div
                                                 class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300">
                                                 📎</div>
                                             <div class="min-w-0">
                                                 @if ($att->url)
                                                     <a href="{{ $att->url }}" target="_blank"
                                                         class="block truncate font-medium text-blue-700 hover:underline dark:text-indigo-300">
                                                         {{ $att->display_name }}
                                                     </a>
                                                 @else
                                                     <span
                                                         class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                         {{ $att->display_name }} (no link)
                                                     </span>
                                                 @endif
                                                 <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                     {{ strtoupper($att->extention ?? '-') }}
                                                     @if (!empty($att->size))
                                                         • {{ number_format($att->size / 1024, 0) }} KB
                                                     @endif
                                                     @if (!empty($att->created_at))
                                                         •
                                                         {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                                     @endif
                                                 </div>
                                             </div>
                                         </div>

                                         <button type="button"
                                             class="removeAttachment2 h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"
                                             aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                     </div>
                                 @empty
                                     <div
                                         class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                         No existing attachments.
                                     </div>
                                 @endforelse
                             </div>

                             {{-- Upload baru --}}
                             <div id="attachmentsContainer" class="flex flex-col gap-3">
                                 <div class="attachment-row flex items-center gap-2">
                                     <input type="file" name="attachments[]"
                                         class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                     <button type="button"
                                         class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                 </div>
                             </div>

                             <button type="button" id="addAttachment"
                                 class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Add Attachment
                                </button>
                            </div>
                        </details>

                         <div class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                             <a href="{{ route('receiptlist') }}"
                                 class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">Cancel</a>
                             <button type="submit" id="submitBtn"
                                 class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                 <span id="btnText">Submit Approval</span>
                             </button>
                         </div>
                     </div>
                 </form>

             </div>
         </div>
     </div>

     {{-- ===== Overlay HTML ===== --}}
     <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
         <div class="loading-card">
             <div class="loading-spinner"></div>
             <div class="loading-text">
                 Processing
                 <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
             </div>
         </div>
     </div>

     {{-- ===== Overlay helpers ===== --}}
     <script>
         function showOverlay(text = 'Processing') {
             const $ov = $('#loadingSpinnerContainer');
             $ov.find('.loading-text').html(
                 (text || 'Processing') +
                 '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
             );
             $ov.stop(true, true).fadeIn(120);
         }

         function hideOverlay() {
             $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
         }
     </script>

     {{-- ===== Submit + Validasi Qty ===== --}}
     <script>
         $(function() {
             const updateUrl = @json(route('receipt.update', $hash));
             const isReturn = @json(strtolower((string) $rcp->receipttype) === 'return');

             function clearFormErrors() {
                 $('#receiptEditForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                 $('#receiptEditForm .error-feedback').remove();
             }

             function addError($el, msg) {
                 if (!$el || !$el.length) return;
                 $el.addClass('is-invalid').attr('aria-invalid', 'true');
                 if ($el.next('.error-feedback').length === 0) {
                     $el.after('<small class="error-feedback">' + msg + '</small>');
                 }
             }

             // bersihkan error on change
             $(document).on('input change', '#receiptEditForm input, #receiptEditForm select', function() {
                 $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                 $(this).next('.error-feedback').remove();
             });

             // allow digit + . ,
             $(document).on('keypress', '.qtyEdit', function(e) {
                 const code = e.which || e.keyCode;
                 const ch = String.fromCharCode(code);
                 if ([8, 9, 13, 27, 37, 38, 39, 40, 46].includes(code)) return;
                 if (!/[0-9.,]/.test(ch)) e.preventDefault();
                 const v = this.value;
                 if ((ch === '.' && v.includes('.')) || (ch === ',' && v.includes(','))) e.preventDefault();
             });
             $(document).on('input', '.qtyEdit', function() {
                 this.value = this.value.replace(/[^0-9.,]/g, '');
             });

             function hasAtLeastOneQty() {
                 let ok = false;
                 $('.qtyEdit').each(function() {
                     const raw = (this.value || '').replace(',', '.');
                     const n = parseFloat(raw);
                     if (!isNaN(n)) {
                         ok = true;
                         return false;
                     } // boleh 0
                 });
                 return ok;
             }

             $('#receiptEditForm').on('submit', function(e) {
                 e.preventDefault();
                 clearFormErrors();

                 if (!hasAtLeastOneQty()) {
                     const $first = $('.qtyEdit').first();
                     addError($first, 'Isi Qty (boleh 0) pada minimal satu baris.');
                     $first.focus();
                     if (window.toastr) toastr.error('Minimal satu baris Qty harus diisi.');
                     return;
                 }

                 // normalisasi qty ke titik
                 $('.qtyEdit').each(function() {
                     this.value = (this.value || '').replace(/,/g, '.');
                 });

                 $('#submitBtn').prop('disabled', true);
                 $('#btnText').text('Saving...');
                 showOverlay('Saving');

                 const formData = new FormData(document.getElementById('receiptEditForm'));
                 formData.set('_method', 'PUT');

                 $.ajax({
                         url: updateUrl,
                         type: "POST",
                         data: formData,
                         processData: false,
                         contentType: false
                     })
                     .done(function(res) {
                         if (window.toastr) toastr.success(res.message || 'Receipt updated.');
                         window.location.href = "{{ route('receiptlist') }}";
                     })
                     .fail(function(xhr) {
                         if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                             let msg = 'Mohon periksa input:<br>';
                             Object.keys(xhr.responseJSON.errors).forEach(k => {
                                 msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                             });
                             if (window.toastr) toastr.error(msg);
                         } else if (xhr.responseJSON && xhr.responseJSON.message) {
                             if (window.toastr) toastr.error(xhr.responseJSON.message);
                         } else {
                             if (window.toastr) toastr.error('Update failed.');
                         }
                     })
                     .always(function() {
                         $('#submitBtn').prop('disabled', false);
                         $('#btnText').text('Submit Approval');
                         hideOverlay();
                     });
             });
         });
     </script>

     <script>
         // Tambah / Hapus attachment (baru)
         $(document).ready(function() {
             $('#addAttachment').click(function() {
                 $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2">
                        <input type="file" name="attachments[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                        <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    </div>
                `);
                 toggleDeleteButton();
             });

             $(document).on('click', '.removeAttachment', function() {
                 $(this).closest('.attachment-row').remove();
                 toggleDeleteButton();
             });

             function toggleDeleteButton() {
                 if ($('.attachment-row').length > 1) {
                     $('.removeAttachment').removeClass('hidden');
                 } else {
                     $('.removeAttachment').addClass('hidden');
                 }
             }
         });
     </script>

     <script>
         // Hapus attachment existing (server endpoint silakan sesuaikan)
         $(document).on('click', '.removeAttachment2', function() {
             const $btn = $(this);
             const $row = $btn.closest('.attachment-row');
             const attachmentId = $row.data('id');

             if (!attachmentId) {
                 toastr.error('Attachment ID tidak ditemukan.');
                 return;
             }
             if (!confirm('Are you sure you want to remove this attachment?')) return;

             const originalHtml = $btn.html();
             $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Removing...
            `);

             $.ajax({
                     url: "/remove-attachment/" + attachmentId, // <— sesuaikan route mu
                     type: "POST",
                     data: {
                         _method: "PUT",
                         _token: "{{ csrf_token() }}"
                     }
                 })
                 .done(function(res) {
                     if (res && res.success) {
                         $row.slideUp(180, function() {
                             $(this).remove();
                         });
                         toastr.success('Attachment removed.');
                     } else {
                         toastr.error(res?.message || 'Failed to remove attachment.');
                         $btn.prop('disabled', false).html(originalHtml);
                     }
                 })
                 .fail(function(xhr) {
                     toastr.error('Error! Unable to remove attachment.');
                     console.error(xhr.responseText);
                     $btn.prop('disabled', false).html(originalHtml);
                 });
         });
     </script>

     <script>
         // Lazy load site select (sama seperti view create)
         $(function() {
             const siteCacheByCpny = {};

             async function fetchSites(cpnyId) {
                 if (!cpnyId) return [];
                 if (siteCacheByCpny[cpnyId]) return siteCacheByCpny[cpnyId];

                 try {
                     const url = @json(route('sites.index'));
                     const res = await $.ajax({
                         url,
                         method: 'GET',
                         data: {
                             cpny_id: cpnyId
                         },
                         dataType: 'json'
                     });
                     if (!res || res.ok === false) throw new Error(res?.message || 'Failed to load sites.');
                     const data = Array.isArray(res.data) ? res.data : [];
                     siteCacheByCpny[cpnyId] = data;
                     return data;
                 } catch (err) {
                     if (window.toastr) toastr.error(err.message || 'Gagal mengambil data site.');
                     return [];
                 }
             }

             function populateSelectOptions($sel, sites, currentValue) {
                 const hasCurrent = currentValue && sites.some(s => String(s.siteid) === String(currentValue));
                 const opts = [];
                 if (!hasCurrent) opts.push(new Option('Select site…', '', true, true));
                 sites.forEach(s => {
                     const val = s.siteid;
                     const text = s.siteid;
                     const selected = String(val) === String(currentValue);
                     opts.push(new Option(text, val, selected, selected));
                 });
                 $sel.empty();
                 opts.forEach(o => $sel.append(o));
             }

             $(document).on('focus click', '.siteSelect', async function() {
                 const $sel = $(this);
                 if ($sel.data('loaded') === 1) return;

                 const cpnyId = $sel.data('cpny-id');
                 const current = $sel.data('current-site') || $sel.val() || '';

                 $sel.html('<option disabled selected>Loading…</option>');

                 const sites = await fetchSites(cpnyId);
                 populateSelectOptions($sel, sites, current);

                 $sel.data('loaded', 1);
             });

             // OPTIONAL: Prefill semua select sekali
             (async function prefillAllSiteSelects() {
                 const $all = $('.siteSelect');
                 if ($all.length === 0) return;
                 const cpnyId = $all.first().data('cpny-id');
                 const sites = await fetchSites(cpnyId);
                 $all.each(function() {
                     const $sel = $(this);
                     if ($sel.data('loaded') === 1) return;
                     const current = $sel.data('current-site') || $sel.val() || '';
                     populateSelectOptions($sel, sites, current);
                     $sel.data('loaded', 1);
                 });
             })();
         });
     </script>

     {{-- Toastr CDN --}}
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
     <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
 </x-app-layout>
