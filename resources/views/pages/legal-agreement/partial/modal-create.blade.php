<div id="createAgreementModal" class="agr-modal fixed inset-0 z-[9999] hidden items-center justify-center p-4" data-form-modal="true">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200"></div>

    <div class="modal-panel modal-scroll relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">

        <div class="mb-6 flex items-start justify-between gap-4 border-b border-slate-100 pb-5 dark:border-white/[0.06]">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <i class="fa-solid fa-file-signature text-lg"></i>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">New Legal Agreement</h3>
                    <p class="text-xs text-slate-400">Fill in the tenant and document details below</p>
                </div>
            </div>
            <button type="button" class="btn-close-form-modal flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.08] dark:hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Step indicator --}}
        <div class="agr-steps mb-7">
            <div class="agr-step-item" data-step-indicator="1">
                <span class="agr-step-circle"><span class="agr-step-num">1</span><i class="fa-solid fa-check"></i></span>
                <span class="agr-step-label">Tenant</span>
            </div>
            <span class="agr-step-line"></span>
            <div class="agr-step-item" data-step-indicator="2">
                <span class="agr-step-circle"><span class="agr-step-num">2</span><i class="fa-solid fa-check"></i></span>
                <span class="agr-step-label">Document</span>
            </div>
            <span class="agr-step-line"></span>
            <div class="agr-step-item" data-step-indicator="3">
                <span class="agr-step-circle"><span class="agr-step-num">3</span><i class="fa-solid fa-check"></i></span>
                <span class="agr-step-label">Leasing</span>
            </div>
            <span class="agr-step-line"></span>
            <div class="agr-step-item" data-step-indicator="4">
                <span class="agr-step-circle"><span class="agr-step-num">4</span><i class="fa-solid fa-check"></i></span>
                <span class="agr-step-label">Review</span>
            </div>
        </div>

        <form id="createAgreementForm">
            <input type="hidden" name="business_id" />
            <input type="hidden" name="tenant_no" />

            {{-- Step 1: Tenant Information --}}
            <div class="agr-step" data-step="1">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head">
                        <span class="agr-section-badge">1</span>
                        <p class="agr-section-title">Tenant Information</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <div class="mb-2.5 flex items-center justify-between">
                                <label class="agr-label mb-0!">Company <span class="text-red-500">*</span></label>
                                <span id="create_cpny_lock_note" class="hidden flex items-center gap-1 text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-lock"></i> Set from selected data
                                </span>
                            </div>
                            <select name="cpny_id" id="create_cpny_id" class="agr-input agr-select2" required>
                                <option value="">Select company</option>
                                @foreach ($allCompanies as $c)
                                    <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="agr-label">Tenant Name   <span class="text-red-500">*</span></label>
                            <input type="text" name="business_name" class="agr-input" required placeholder="Tenant name  " />
                        </div>

                        <div id="create_trade_name_wrap" class="hidden sm:col-span-2">
                            <label class="agr-label">Trade Name</label>
                            <input type="text" name="trade_name" class="agr-input" placeholder="Trade name" />
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:col-span-2 sm:grid-cols-3">
                            <div>
                                <label class="agr-label">Property Type <span class="text-red-500">*</span></label>
                                @php
                                    $propertyTypeLabels = ['OFF' => 'Office', 'MALL' => 'Mall', 'APT' => 'Apartment', 'HOTEL' => 'Hotel'];
                                @endphp
                                <select name="property_cd" id="create_property_cd" class="agr-input" required>
                                    <option value="">Select property type</option>
                                    @foreach ($propertyTypes as $code)
                                        <option value="{{ $code }}">{{ $propertyTypeLabels[$code] ?? $code }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="agr-label">Floor</label>
                                <input type="text" name="floor_id" class="agr-input" placeholder="Floor" />
                            </div>

                            <div>
                                <label class="agr-label">Unit</label>
                                <input type="text" name="unit_id" class="agr-input" placeholder="Unit" />
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="agr-label">Tenant Correspondence Address</label>
                            <textarea name="business_address" rows="2" class="agr-textarea" placeholder="Correspondence address"></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:col-span-2 sm:grid-cols-3">
                            <div>
                                <label class="agr-label">Tenant PIC Name   <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_penyewa" class="agr-input" required placeholder="Tenant PIC name  " />
                            </div>

                            <div>
                                <label class="agr-label">Tenant Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_phonenumber_penyewa" class="agr-input" required placeholder="Phone number" />
                            </div>

                            <div>
                                <label class="agr-label">Tenant Email <span class="text-red-500">*</span></label>
                                <input type="email" name="pic_email_penyewa" class="agr-input" required placeholder="email@example.com" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Step 2: Document Information --}}
            <div class="agr-step hidden" data-step="2">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head">
                        <span class="agr-section-badge">2</span>
                        <p class="agr-section-title">Document Information</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label class="agr-label">PSM / Addendum Number <span class="text-red-500">*</span></label>
                            <input type="text" name="no_psm_or_addendum" class="agr-input" required placeholder="Document number" />
                        </div>

                        <div>
                            <label class="agr-label">PSM / Addendum Date <span class="text-red-500">*</span></label>
                            <input type="date" name="psm_or_addendum_date" class="agr-input" required />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="agr-label">Hardcopy Delivery Date (PSM / Addendum) <span class="text-red-500">*</span></label>
                            <input type="date" name="psm_or_addendum_delivery_date" class="agr-input" required />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="agr-label">PIC Legal <span class="text-red-500">*</span></label>
                            <select name="pic_legal[]" id="create_pic_legal" class="agr-input agr-select2" multiple required></select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="agr-label">Proof of Delivery <span class="text-red-500">*</span></label>
                            <div class="agr-file">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <input type="file" name="bukti_pengiriman[]" id="create_bukti_pengiriman" multiple required />
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Step 3: Leasing Information --}}
            <div class="agr-step hidden" data-step="3">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head">
                        <span class="agr-section-badge">3</span>
                        <p class="agr-section-title">Leasing Information</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="agr-label">PIC Leasing</label>
                            <select name="pic_leasing[]" id="create_pic_leasing" class="agr-input agr-select2" multiple></select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Review --}}
            <div class="agr-step hidden" data-step="4">
                <div class="mb-4 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <i class="fa-solid fa-circle-info text-blue-500"></i>
                    Please review the details below before submitting.
                </div>
                <div id="create_review_content" class="space-y-4"></div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5 dark:border-white/[0.06]">
                <button type="button" class="btn-close-form-modal rounded-lg border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                    Cancel
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" id="btnCreateStepBack" class="hidden flex items-center gap-2 rounded-lg border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="button" id="btnCreateStepNext" class="flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button type="submit" id="btnSubmitCreateAgreement" class="hidden flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-paper-plane"></i> Submit Agreement
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
