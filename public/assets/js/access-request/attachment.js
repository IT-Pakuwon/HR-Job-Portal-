
function renderExistingAttachments(files = []) {
    let html = "";

    if (!files.length) {
        $("#existingAttachmentContainer").html("");

        return;
    }

    files.forEach((file, index) => {
        html += `
            <div class="
                flex items-center justify-between
                rounded-lg border border-slate-200
                bg-slate-50 px-4 py-3
            ">

                <div class="flex items-center gap-3 min-w-0">

                    <div class="
                        flex h-10 w-10 shrink-0 items-center justify-center
                        rounded-lg bg-white border border-slate-200
                    ">
                        <i class="fa-regular fa-file text-slate-500"></i>
                    </div>

                    <div class="min-w-0">

                        <p class="truncate text-sm font-medium text-slate-700">
                            ${file.display_name ?? file.filename ?? "-"}
                        </p>

                    </div>

                </div>

                <div class="flex items-center gap-2">

                    <a
                        href="${file.url}"
                        target="_blank"
                        class="
                            inline-flex h-9 w-9 items-center justify-center
                            rounded-lg border border-slate-200
                            bg-white text-slate-600
                            hover:bg-slate-100
                        "
                    >
                        <i class="fa-solid fa-eye text-xs"></i>
                    </a>

                    <button
                        type="button"
                        class="
                            btn-remove-existing-file
                            inline-flex h-9 w-9 items-center justify-center
                            rounded-lg border border-red-200
                            bg-white text-red-500 transition
                            hover:bg-red-50
                        "
                        data-index="${index}"
                    >

                        <i class="fa-solid fa-trash text-xs"></i>

                    </button>

                </div>

            </div>
        `;
    });

    $("#existingAttachmentContainer").html(html);
}

function renderNewAttachments() {
    let html = "";

    selectedFiles.forEach((file, index) => {
        html += `
            <div class="
                flex items-center justify-between
                rounded-lg border border-blue-200
                bg-blue-50 px-4 py-3
            ">

                <div class="flex items-center gap-3 min-w-0">

                    <div class="
                        flex h-10 w-10 shrink-0 items-center justify-center
                        rounded-lg bg-white border border-blue-200
                    ">
                        <i class="fa-solid fa-paperclip text-blue-500"></i>
                    </div>

                    <div class="min-w-0">

                        <p class="truncate text-sm font-medium text-slate-700">
                            ${file.name}
                        </p>

                        <p class="text-xs text-slate-400">
                            ${(file.size / 1024 / 1024).toFixed(2)} MB
                        </p>

                    </div>

                </div>

                <button
                    type="button"
                    class="
                        btn-remove-new-file
                        inline-flex h-9 w-9 items-center justify-center
                        rounded-lg border border-red-200
                        bg-white text-red-500 transition
                        hover:bg-red-50
                    "
                    data-index="${index}"
                >

                    <i class="fa-solid fa-xmark text-xs"></i>

                </button>

            </div>
        `;
    });

    $("#newAttachmentContainer").html(html);

    let dt = new DataTransfer();

    selectedFiles.forEach((file) => {
        dt.items.add(file);
    });

    $("#requestAttachment")[0].files = dt.files;
}

function initAttachmentHandlers() {

    $(document)
        .off("click", ".btn-remove-existing-file")
        .on("click", ".btn-remove-existing-file", function () {

            const index = $(this).data("index");

            existingAttachments.splice(index, 1);

            renderExistingAttachments(existingAttachments);

        });

    $(document)
        .off("click", ".btn-remove-new-file")
        .on("click", ".btn-remove-new-file", function () {

            const index = $(this).data("index");

            selectedFiles.splice(index, 1);

            renderNewAttachments();

        });

    $(document)
        .off("change", "#requestAttachment")
        .on("change", "#requestAttachment", function (e) {

            const files = [...e.target.files];

            selectedFiles = [
                ...selectedFiles,
                ...files
            ];

            renderNewAttachments();

        });

}

