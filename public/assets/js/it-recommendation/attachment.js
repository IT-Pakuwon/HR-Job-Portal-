let createSelectedFiles = [];

function attachmentEmptyState() {
    return `
        <div class="
            w-full

            rounded-lg

            border border-dashed border-slate-200
            dark:border-white/10

            px-4 py-6

            text-center
            text-sm

            text-slate-400
        ">
            No attachments
        </div>
    `;
}

function attachmentCard({
    name,
    size = null,
    url = null,
    removable = false,
    index = null,
}) {
    const removeButton = removable
        ? `
            <button
                type="button"

                class="
                    btn-remove-create-attachment

                    inline-flex
                    h-7 w-7

                    items-center
                    justify-center

                    rounded-lg

                    text-red-500

                    transition
                    hover:bg-red-50
                    dark:hover:bg-red-500/10
                "

                data-index="${index}"
            >

                <i class="fa-solid fa-xmark text-xs"></i>

            </button>
        `
        : "";

    const content = `

        <div class="
            flex items-center gap-3

            rounded-lg

            border border-slate-200
            dark:border-white/10

            bg-white
            dark:bg-[#0b1220]

            px-4 py-3

            shadow-sm

            transition-all
            duration-200

            hover:bg-slate-50
            dark:hover:bg-white/[0.03]
        ">

            <div class="
                flex h-10 w-10 items-center justify-center

                rounded-lg

                bg-slate-100
                dark:bg-white/[0.05]

                text-slate-500
                dark:text-slate-300
            ">

                <i class="fa-solid fa-paperclip"></i>

            </div>

            <div class="min-w-0 flex-1">

                <p class="
                    truncate

                    text-sm
                    font-medium

                    text-slate-700
                    dark:text-slate-200
                ">
                    ${name}
                </p>

                ${
                    size
                        ? `
                            <p class="
                                mt-1

                                text-xs

                                text-slate-400
                            ">
                                ${size}
                            </p>
                        `
                        : ""
                }

            </div>

            ${removeButton}

        </div>

    `;

    if (!url) {
        return content;
    }

    return `
        <a
            href="${url}"
            target="_blank"

            class="block"
        >
            ${content}
        </a>
    `;
}

function renderAttachmentList(selector, files = [], mode = "view") {
    let html = "";

    if (files.length === 0) {
        html = attachmentEmptyState();
    } else {
        files.forEach((file, index) => {
            if (mode === "upload") {
                const size = (file.size / 1024 / 1024).toFixed(2);

                html += attachmentCard({
                    name: file.name,

                    size: `${size} MB`,

                    removable: true,

                    index,
                });
            } else {
                html += attachmentCard({
                    name: file.filename || "Attachment",

                    url: file.signed_url || "#",
                });
            }
        });
    }

    $(selector).html(html);
}

function renderAttachments(files = []) {
    renderAttachmentList("#show_attachments", files);
}

function syncCreateAttachmentInput() {
    const dt = new DataTransfer();

    createSelectedFiles.forEach((file) => {
        dt.items.add(file);
    });

    $("#create_attachments")[0].files = dt.files;
}

$("#create_attachments").on("change", function () {
    const files = Array.from(this.files || []);

    createSelectedFiles = [...createSelectedFiles, ...files];

    syncCreateAttachmentInput();

    renderAttachmentList(
        "#createAttachmentPreview",
        createSelectedFiles,
        "upload",
    );
});

$(document).on("click", ".btn-remove-create-attachment", function () {
    const index = $(this).data("index");

    createSelectedFiles.splice(index, 1);

    syncCreateAttachmentInput();

    renderAttachmentList(
        "#createAttachmentPreview",
        createSelectedFiles,
        "upload",
    );
});

function resetCreateAttachments() {
    createSelectedFiles = [];

    $("#create_attachments").val("");

    renderAttachmentList("#createAttachmentPreview", [], "upload");
}
