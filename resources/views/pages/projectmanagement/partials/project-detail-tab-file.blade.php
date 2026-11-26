{{-- Project Detail Modal — File tab: file cards (attachmentRowHtml() in
     projects.blade.php), then a drop zone. Picked files wait in the strip
     below it until Upload is pressed. --}}
<div x-show="shows('attachments')" :class="fullscreen ? 'border-t border-gray-100 pt-8 dark:border-white/[0.06]' : ''">
    <div x-show="fullscreen" class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-indigo-500 dark:text-indigo-300">
        <i class="fas fa-paperclip text-[10px]"></i> File
    </div>
    <div id="projectAttachmentList" class="mb-3 grid grid-cols-1 gap-2 empty:hidden sm:grid-cols-2"></div>

    <form id="projectAttachmentUploadForm" enctype="multipart/form-data">
        @csrf
        <label id="projectAttachDrop" for="projectAttachFiles"
            class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60 px-4 py-3 transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-indigo-500/50 dark:hover:bg-indigo-900/10">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                <i class="fas fa-cloud-arrow-up"></i>
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Drop files here or <span class="text-indigo-600 dark:text-indigo-300">browse</span></span>
                <span class="block text-xs text-gray-400">Photos, videos, PDFs and office files · max 5MB each</span>
            </span>
            <input type="file" id="projectAttachFiles" name="attachments[]" multiple
                accept=".png,.jpg,.jpeg,.gif,.webp,.heic,.mp4,.mov,.webm,.avi,.pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx,.csv,.txt,.zip"
                class="sr-only">
        </label>

        <div id="projectAttachStaged" class="mt-2 hidden flex-wrap items-center justify-between gap-2 rounded-xl border border-indigo-100 bg-indigo-50/60 px-3 py-2 dark:border-indigo-500/20 dark:bg-indigo-900/10">
            <div id="projectAttachStagedNames" class="flex min-w-0 flex-1 flex-wrap gap-1.5"></div>
            <div class="flex shrink-0 items-center gap-1">
                <button type="button" id="projectAttachClear" class="h-8 rounded-lg px-3 text-xs font-semibold text-gray-500 transition hover:bg-white dark:text-gray-300 dark:hover:bg-white/10">Clear</button>
                <button type="button" id="btnUploadProjectAttachment" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-wait disabled:opacity-60">
                    <i class="fas fa-arrow-up text-[10px]"></i> Upload
                </button>
            </div>
        </div>
    </form>
</div>
