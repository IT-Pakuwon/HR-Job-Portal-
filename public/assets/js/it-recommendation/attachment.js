$("#create_attachments").on("change", function () {
    const files = Array.from(this.files || []);

    let html = "";

    files.forEach((file) => {
        const size = (file.size / 1024 / 1024).toFixed(2);

        html += `
                    <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">

                        <i class="fa-solid fa-paperclip"></i>

                        <span class="max-w-[200px] truncate">
                            ${file.name}
                        </span>

                        <span class="text-gray-400">
                            ${size} MB
                        </span>

                    </div>
                `;
    });

    $("#createAttachmentPreview").html(html);
});

function renderAttachments(files) {
    let html = "";

    if (files.length === 0) {
        html = `
            <div class="w-full rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                No attachments
            </div>
        `;
    } else {
        files.forEach((file) => {
            html += `
                <a
                    href="${file.signed_url ?? "#"}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/[0.05]">

                    <i class="fa-solid fa-paperclip text-gray-400"></i>

                    <div class="max-w-[220px] truncate">
                        ${file.filename ?? "Attachment"}
                    </div>

                </a>
            `;
        });
    }

    $("#show_attachments").html(html);
}
