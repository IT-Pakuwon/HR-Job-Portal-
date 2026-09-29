{{-- Project Detail Modal — File tab (was a plain table; now card rows,
     rendered by refreshProjectAttachments() in projects.blade.php). --}}
<div x-show="tab === 'attachments'">
    <div id="projectAttachmentList" class="space-y-2"></div>

    <form id="projectAttachmentUploadForm" enctype="multipart/form-data" class="mt-4 flex items-center gap-3">
        @csrf
        <input type="file" id="projectAttachFiles" name="attachments[]" multiple
            accept=".png,.jpg,.jpeg,.gif,.webp,.heic,.mp4,.mov,.webm,.avi,.pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx,.csv,.txt,.zip"
            class="block flex-1 cursor-pointer rounded-lg border border-gray-200 bg-white px-2 py-[7px] text-sm dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-100">
        <button type="button" id="btnUploadProjectAttachment" class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">Upload</button>
    </form>
    <p class="mt-1.5 text-xs text-gray-400">Photos, videos, PDFs and common office files — max 5MB per file. Click a file to preview it.</p>
</div>
