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
                rounded-xl
                border border-slate-200
                dark:border-white/10
                bg-slate-50
                dark:bg-white/[0.03]
                px-4 py-3
                transition
            ">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="
                        flex h-10 w-10 shrink-0 items-center justify-center
                        rounded-lg
                        border border-slate-200
                        dark:border-white/10
                        bg-white
                        dark:bg-white/[0.04]
                    ">
                        <i class="
                            fa-regular fa-file
                            text-slate-500
                            dark:text-slate-300
                        "></i>
                    </div>

                    <div class="min-w-0">

                        <p class="
                            truncate text-sm font-medium
                            text-slate-700
                            dark:text-slate-100
                        ">
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
                            rounded-lg
                            border border-slate-200
                            dark:border-white/10
                            bg-white
                            dark:bg-white/[0.04]
                            text-slate-600
                            dark:text-slate-200
                            transition
                            hover:bg-slate-100
                            dark:hover:bg-white/[0.08]
                        "
                    >
                        <i class="fa-solid fa-eye text-xs"></i>
                    </a>

                    <button
                        type="button"
                        class="
                            btn-remove-existing-file
                            inline-flex h-9 w-9 items-center justify-center
                            rounded-lg
                            border border-red-200
                            dark:border-red-500/20
                            bg-white
                            dark:bg-red-500/10
                            text-red-500
                            dark:text-red-300
                            transition
                            hover:bg-red-50
                            dark:hover:bg-red-500/20
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
                rounded-xl
                border border-blue-200
                dark:border-blue-500/20
                bg-blue-50
                dark:bg-blue-500/10
                px-4 py-3
                transition
            ">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="
                        flex h-10 w-10 shrink-0 items-center justify-center
                        rounded-lg
                        border border-blue-200
                        dark:border-blue-500/20
                        bg-white
                        dark:bg-blue-500/10
                    ">
                        <i class="
                            fa-solid fa-paperclip
                            text-blue-500
                            dark:text-blue-300
                        "></i>
                    </div>

                    <div class="min-w-0">

                        <p class="
                            truncate text-sm font-medium
                            text-slate-700
                            dark:text-slate-100
                        ">
                            ${file.name}
                        </p>

                        <p class="
                            text-xs
                            text-slate-400
                            dark:text-slate-500
                        ">
                            ${(file.size / 1024 / 1024).toFixed(2)} MB
                        </p>

                    </div>

                </div>

                <button
                    type="button"
                    class="
                        btn-remove-new-file
                        inline-flex h-9 w-9 items-center justify-center
                        rounded-lg
                        border border-red-200
                        dark:border-red-500/20
                        bg-white
                        dark:bg-red-500/10
                        text-red-500
                        dark:text-red-300
                        transition
                        hover:bg-red-50
                        dark:hover:bg-red-500/20
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
