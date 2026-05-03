<div id="createModal"
    class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4 pt-10 sm:p-6 sm:pt-16">

    <div
        class="w-full max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#111827]">

        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5 dark:border-white/10">

            <div>

                <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Create IT Recommendation
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create hardware or non-stock recommendation request
                </p>

            </div>

            <button type="button" id="btnCloseCreateModal"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">

                <i class="ri-close-line text-lg"></i>

            </button>

        </div>

        <form id="createForm" enctype="multipart/form-data">

            @csrf

            <div class="space-y-5 bg-gray-50/70 p-6 dark:bg-[#0f172a]">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div class="space-y-2">

                        <label class="req text-sm font-medium text-gray-700 dark:text-gray-200">
                            Company
                        </label>

                        <select name="cpny_id" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-gray-400 focus:ring-0 dark:border-white/10 dark:bg-[#111827] dark:text-gray-200">

                            @foreach ($usercpny as $cpny)
                                <option value="{{ $cpny->cpny_id }}">
                                    {{ $cpny->cpny_id }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="space-y-2">

                        <label class="req text-sm font-medium text-gray-700 dark:text-gray-200">
                            Department
                        </label>

                        <select name="department_id" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-gray-400 focus:ring-0 dark:border-white/10 dark:bg-[#111827] dark:text-gray-200">

                            @foreach ($userdept as $dept)
                                <option value="{{ $dept->department_id }}">
                                    {{ $dept->department_id }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="space-y-2">

                        <label class="req text-sm font-medium text-gray-700 dark:text-gray-200">
                            Ticket Number
                        </label>

                        <select name="ticketnbr" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-gray-400 focus:ring-0 dark:border-white/10 dark:bg-[#111827] dark:text-gray-200">

                            <option value="">Choose Ticket</option>
                            <option value="INC0001">INC0001</option>
                            <option value="INC0002">INC0002</option>
                            <option value="INC0003">INC0003</option>
                            <option value="REQ0001">REQ0001</option>

                        </select>

                    </div>

                    <div class="space-y-2">

                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Asset Number
                        </label>

                        <input type="text" name="assetnbr"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:border-gray-400 focus:ring-0 dark:border-white/10 dark:bg-[#111827] dark:text-gray-200 dark:placeholder:text-gray-500"
                            placeholder="Optional">

                    </div>

                </div>

                <div class="space-y-2">

                    <label class="req text-sm font-medium text-gray-700 dark:text-gray-200">
                        Purpose / Requirement
                    </label>

                    <textarea name="keperluan" rows="5" required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:border-gray-400 focus:ring-0 dark:border-white/10 dark:bg-[#111827] dark:text-gray-200 dark:placeholder:text-gray-500"
                        placeholder="Describe hardware requirement, recommendation reason, issue, or operational need..."></textarea>

                </div>

                <div class="space-y-2">

                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Attachment
                    </label>

                    <div
                        class="rounded-2xl border border-dashed border-gray-300 bg-white p-5 dark:border-white/10 dark:bg-[#111827]">

                        <input type="file" name="attachments[]" multiple
                            class="w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-gray-900 file:px-5 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700 dark:text-gray-400 dark:file:bg-white/10 dark:file:hover:bg-white/20">

                        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                            Upload quotation, screenshot, supporting document, or issue evidence
                        </p>

                    </div>

                </div>

            </div>

            <div
                class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-5 dark:border-white/10 dark:bg-[#111827]">

                <div class="text-xs text-gray-400 dark:text-gray-500">
                    IT Recommendation • ITR Module
                </div>

                <div class="flex items-center gap-3">

                    <button type="button" id="btnCancelCreate"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">

                        Cancel

                    </button>

                    <button type="submit" id="btnSubmitCreate"
                        class="inline-flex items-center rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">

                        <i class="ri-save-line mr-2"></i>
                        Submit

                    </button>

                </div>

            </div>

        </form>

    </div>
</div>
