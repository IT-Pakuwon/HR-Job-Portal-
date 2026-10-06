{{-- Modal body for creating a PSM/OLA "Pembuatan" (or an Addendum, see $kind) from a pending job.
     Fetched over ajax into #newAgrModalBody on the PSM/OLA page. --}}
<div class="mb-6 flex items-start justify-between gap-4 border-b border-slate-100 pb-5 dark:border-white/[0.06]">
    <div class="flex items-center gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
            <i class="fa-solid fa-file-signature text-lg"></i>
        </span>
        <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $isEdit ? 'Edit' : 'New' }} {{ $kind['label'] }}</h3>
            <p class="text-xs text-slate-400">{{ $subtitle }}</p>
        </div>
    </div>
    <button type="button" class="btn-close-new-agr flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.08] dark:hover:text-white">
        <i class="fa-solid fa-xmark text-lg"></i>
    </button>
</div>

{{-- Stepper --}}
<ol class="mb-6 flex items-center gap-3 text-sm font-medium" id="newAgrStepper">
    <li class="step-pill flex items-center gap-2" data-step="1">
        <span class="step-num flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white">1</span>
        <span>Tenant Information</span>
    </li>
    {{-- Addendum has no Documents step: panels stay numbered 1, 3, 4 and the pills just read 1, 2, 3. --}}
    @if ($kind['docs'])
        <li class="h-px flex-1 bg-slate-200 dark:bg-white/10"></li>
        <li class="step-pill flex items-center gap-2" data-step="2">
            <span class="step-num flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-slate-600">2</span>
            <span>Documents</span>
        </li>
    @endif
    <li class="h-px flex-1 bg-slate-200 dark:bg-white/10"></li>
    <li class="step-pill flex items-center gap-2" data-step="3">
        <span class="step-num flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-slate-600">{{ $kind['docs'] ? 3 : 2 }}</span>
        <span>{{ $kind['label'] }} &amp; PIC</span>
    </li>
    <li class="h-px flex-1 bg-slate-200 dark:bg-white/10"></li>
    <li class="step-pill flex items-center gap-2" data-step="4">
        <span class="step-num flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-slate-600">{{ $kind['docs'] ? 4 : 3 }}</span>
        <span>Review</span>
    </li>
</ol>

<form id="psmOlaForm" data-store-url="{{ $actionUrl }}" autocomplete="off">

    {{-- Step 1 --}}
    <div data-step-panel="1">
        {{-- Fields start locked; Edit unlocks them, Save keeps the changes (they
             are stored on the agreement when the whole form is saved at Review),
             Cancel puts back what was there before Edit. --}}
        <div class="mb-4 flex items-center justify-between gap-3">
            <p id="newAgrEditHint" class="text-xs text-slate-400">Click Edit to change the tenant details.</p>
            <div class="flex items-center gap-2">
                <button type="button" id="btnNewAgrEdit" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-900/20 dark:text-blue-300">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
                <button type="button" id="btnNewAgrEditCancel" class="hidden h-9 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                    Cancel
                </button>
                <button type="button" id="btnNewAgrEditSave" class="hidden h-9 items-center gap-2 rounded-lg bg-green-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    <i class="fa-solid fa-floppy-disk"></i> Save
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <label class="agr-label">Company</label>
                <input type="text" data-fixed class="agr-input agr-locked" value="{{ $companyName }}" readonly tabindex="-1">
            </div>

            <div>
                <label class="agr-label" for="business_id">Business ID <span class="text-red-500">*</span></label>
                <input type="number" id="business_id" name="business_id" class="agr-input" value="{{ $values['business_id'] }}">
            </div>

            <div>
                <label class="agr-label" for="tenant_no">Tenant Number</label>
                <input type="text" id="tenant_no" name="tenant_no" maxlength="20" class="agr-input" value="{{ $values['tenant_no'] }}">
            </div>

            <div>
                <label class="agr-label" for="trade_name">Trade Name</label>
                <input type="text" id="trade_name" name="trade_name" maxlength="255" class="agr-input" value="{{ $values['trade_name'] }}">
            </div>

            <div>
                <label class="agr-label" for="business_name">Business Name <span class="text-red-500">*</span></label>
                <input type="text" id="business_name" name="business_name" maxlength="255" class="agr-input" value="{{ $values['business_name'] }}">
            </div>

            <div>
                <label class="agr-label" for="property_cd">Property Type</label>
                {{-- Never editable, even in Edit mode (locked by CSS, not disabled, so it still posts). --}}
                <select id="property_cd" name="property_cd" class="agr-input agr-locked" data-always-locked tabindex="-1" style="pointer-events:none">
                    <option value="">-</option>
                    @foreach ($propertyTypes as $code => $label)
                        <option value="{{ $code }}" @selected(strtoupper((string) $values['property_cd']) === $code)>{{ $label }}</option>
                    @endforeach
                    @if ($values['property_cd'] && ! isset($propertyTypes[strtoupper($values['property_cd'])]))
                        <option value="{{ $values['property_cd'] }}" selected>{{ $values['property_cd'] }}</option>
                    @endif
                </select>
            </div>

            <div>
                <label class="agr-label" for="floor_id">Floor</label>
                <input type="text" id="floor_id" name="floor_id" maxlength="20" class="agr-input" value="{{ $values['floor_id'] }}">
            </div>

            <div>
                <label class="agr-label" for="unit_id">Unit</label>
                <input type="text" id="unit_id" name="unit_id" maxlength="20" class="agr-input" value="{{ $values['unit_id'] }}">
            </div>

            <div class="sm:col-span-2">
                <label class="agr-label" for="business_address">Address</label>
                <textarea id="business_address" name="business_address" rows="3" maxlength="500" class="agr-textarea">{{ $values['business_address'] }}</textarea>
            </div>

            <div>
                <label class="agr-label" for="pic_penyewa">PIC Name</label>
                <input type="text" id="pic_penyewa" name="pic_penyewa" maxlength="255" class="agr-input" value="{{ $values['pic_penyewa'] }}">
            </div>

            <div>
                <label class="agr-label" for="pic_phonenumber_penyewa">PIC Phone Number</label>
                <input type="text" id="pic_phonenumber_penyewa" name="pic_phonenumber_penyewa" maxlength="50" class="agr-input" value="{{ $values['pic_phonenumber_penyewa'] }}">
            </div>

            <div class="sm:col-span-2">
                <label class="agr-label" for="pic_email_penyewa">Email</label>
                <input type="email" id="pic_email_penyewa" name="pic_email_penyewa" maxlength="255" class="agr-input" value="{{ $values['pic_email_penyewa'] }}">
            </div>

        </div>

        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-white/[0.06]">
            <button type="button" class="btn-close-new-agr inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                Close
            </button>
            <button type="button" id="btnNewAgrNext" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Next <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

    {{-- Step 2 (PSM / OLA only) --}}
    @if ($kind['docs'])
    <div data-step-panel="2" class="hidden">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Document checklist</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tick the documents the tenant has already submitted, and add a note where needed.</p>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-white/[0.03]">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left" style="width:90px;">Received</th>
                        <th class="px-4 py-3 text-left">Document</th>
                        <th class="px-4 py-3 text-left" style="width:38%;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $doc)
                        <tr class="border-t border-slate-100 dark:border-white/[0.06]">
                            <td class="px-4 py-3">
                                <input type="hidden" name="documents[{{ $doc->agreementdocument_id }}][received]" value="0">
                                <input type="checkbox" class="h-4 w-4 rounded border-slate-300"
                                    name="documents[{{ $doc->agreementdocument_id }}][received]" value="1"
                                    @checked($docState[$doc->agreementdocument_id]['received'] ?? false)>
                            </td>
                            <td class="px-4 py-3">{{ $doc->agreementdocument_descr }}</td>
                            <td class="px-4 py-3">
                                <input type="text" class="agr-input" style="height:40px;" maxlength="2000"
                                    name="documents[{{ $doc->agreementdocument_id }}][note]" placeholder="Notes" value="{{ $docState[$doc->agreementdocument_id]['note'] ?? '' }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-white/[0.06]">
            <button type="button" id="btnNewAgrBack" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <button type="button" id="btnNewAgrNext2" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Next <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- Step 3: agreement number + who is responsible. All three are required.
         The PIC pickers search users holding LEGALACCESS / LEASINGACCESS. --}}
    <div data-step-panel="3" class="hidden">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $kind['label'] }} &amp; PIC</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enter the agreement number and assign the people in charge.</p>

        <div class="mt-4 grid grid-cols-1 gap-4">
            {{-- Addendum made from a PSM / OLA: that agreement's number, for reference (not posted). --}}
            @if (! $kind['docs'] && ! empty($fromPsmOla))
                <div>
                    <label class="agr-label" for="psm_ola_no">No. PSM / OLA</label>
                    <input type="text" id="psm_ola_no" class="agr-input agr-locked" value="{{ $psmOlaNo }}" placeholder="-" readonly tabindex="-1" data-fixed-psm-ola>
                </div>
            @endif

            <div>
                <label class="agr-label" for="no_psm_or_addendum">{{ $kind['docs'] ? 'No. PSM / Addendum' : 'No. Addendum' }} <span class="text-red-500">*</span></label>
                <input type="text" id="no_psm_or_addendum" name="no_psm_or_addendum" maxlength="150" class="agr-input" value="{{ $values['no_psm_or_addendum'] ?? '' }}">
            </div>

            <div>
                <label class="agr-label" for="pic_legal">PIC Legal <span class="text-red-500">*</span></label>
                <select id="pic_legal" name="pic_legal[]" multiple class="agr-input agr-select2 agr-pic-select"
                    data-url="{{ $picSearchUrl }}" data-role="LEGALACCESS" data-placeholder="Search PIC Legal">
                    @foreach ($picLegalSelected as $username => $label)
                        <option value="{{ $username }}" selected>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="agr-label" for="pic_leasing">PIC Leasing <span class="text-red-500">*</span></label>
                <select id="pic_leasing" name="pic_leasing[]" multiple class="agr-input agr-select2 agr-pic-select"
                    data-url="{{ $picSearchUrl }}" data-role="LEASINGACCESS" data-cpny="{{ $cpnyId }}" data-placeholder="Search PIC Leasing">
                    @foreach ($picLeasingSelected as $username => $label)
                        <option value="{{ $username }}" selected>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-white/[0.06]">
            <button type="button" id="btnNewAgrBack3" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <button type="button" id="btnNewAgrNext3" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Next <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>
    {{-- Step 4: read-only summary, filled from the form by the page script --}}
    <div data-step-panel="4" class="hidden">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Review</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Check everything below, then save. Use Back to change anything.</p>

        <div id="newAgrReview" class="mt-4 space-y-5"></div>

        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-white/[0.06]">
            <button type="button" id="btnNewAgrBack2" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-lg bg-green-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                <i class="fa-solid fa-floppy-disk"></i> Save
            </button>
        </div>
    </div>

</form>
