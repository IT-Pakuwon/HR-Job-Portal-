let createSelectedFiles = [];
let deletedAttachmentIds = [];
let existingAttachments = [];
let attachmentFiles = [];

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
    removeClass = "btn-remove-create-attachment",
}) {
    const removeButton = removable
        ? `
        <button
            type="button"

            class="
                ${removeClass}

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
    <button
        type="button"
        class="attachment-preview block"

        data-url="${url}"
        data-filename="${name}"
    >
        ${content}
    </button>
`;
}

$(document).on("keydown", function (e) {
    if (e.key === "Escape") {
        closeAttachmentPreview();
    }
});
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
                    removeClass:
                        mode === "upload"
                            ? "btn-remove-upload-attachment"
                            : "btn-remove-create-attachment",
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

function renderCreateAttachmentPreview() {
    let html = "";

    existingAttachments.forEach((file) => {
        html += attachmentCard({
            name: file.filename || "Attachment",
            url: file.signed_url || "#",
        });
    });

    createSelectedFiles.forEach((file, index) => {
        const size = (file.size / 1024 / 1024).toFixed(2);

        html += attachmentCard({
            name: file.name,
            size: `${size} MB`,
            removable: true,
            index,
        });
    });

    $("#createAttachmentPreview").html(html || attachmentEmptyState());
}
function renderAttachments(files = []) {
    renderAttachmentList("#show_attachments", files);
}

function previewAttachment(file) {
    const url = file.signed_url;

    const ext = (file.filename || "").split(".").pop().toLowerCase();

    const imageTypes = ["jpg", "jpeg", "png", "webp", "gif"];

    if (imageTypes.includes(ext)) {
        $("#attachmentPreviewContent").html(`
            <img
                src="${url}"
                class="max-h-[85vh] mx-auto rounded-lg"
            >
        `);

        $("#attachmentPreviewModal").removeClass("hidden").addClass("flex");

        return;
    }

    if (ext === "pdf") {
        $("#attachmentPreviewContent").html(`
            <iframe
                src="${url}"
                class="h-[85vh] w-full rounded-lg"
            ></iframe>
        `);

        $("#attachmentPreviewModal").removeClass("hidden").addClass("flex");

        return;
    }

    const a = document.createElement("a");

    a.href = url;
    a.download = file.filename || "";

    document.body.appendChild(a);

    a.click();

    document.body.removeChild(a);
}
function closeAttachmentPreview() {
    $("#attachmentPreviewModal").removeClass("flex").addClass("hidden");

    $("#attachmentPreviewContent").html("");
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

    renderCreateAttachmentPreview();
});

$(document).on("click", ".btn-remove-create-attachment", function () {
    const index = $(this).data("index");

    createSelectedFiles.splice(index, 1);

    syncCreateAttachmentInput();

    renderCreateAttachmentPreview();
});

function resetCreateAttachments() {
    createSelectedFiles = [];
    existingAttachments = [];
    deletedAttachmentIds = [];

    $("#create_attachments").val("");

    renderCreateAttachmentPreview();
}

$(document).on("click", ".attachment-preview", function () {
    previewAttachment({
        filename: $(this).data("filename"),
        signed_url: $(this).data("url"),
    });
});
$(document).on("click", ".attachment-btn", function () {
    const hash = $(this).data("id");

    attachmentFiles = [];

    $("#attachment_hash").val(hash);

    $("#attachment_files").val("");

    $("#attachmentPreview").html(attachmentEmptyState());

    $("#attachmentModal").removeClass("hidden").addClass("flex");
});
$("#btnUploadAttachment").on("click", async function () {
    const hash = $("#attachment_hash").val();

    if (!attachmentFiles.length) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Please select attachment",
        });

        return;
    }

    const formData = new FormData();

    attachmentFiles.forEach((file) => {
        formData.append("attachments[]", file);
    });

    try {
        await $.ajax({
            url: window.ITRecommendationRoutes.uploadAttachment.replace(
                "__HASH__",
                hash,
            ),

            type: "POST",

            data: formData,

            processData: false,

            contentType: false,

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Attachment uploaded successfully",
        });

        attachmentFiles = [];

        $("#attachment_hash").val("");

        $("#attachment_files").val("");

        $("#attachmentPreview").html(
            attachmentEmptyState()
        );

        $("#attachmentModal")
            .removeClass("flex")
            .addClass("hidden");

        table.ajax.reload(null, false);

    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Upload failed",
        });
    }
});
$("#attachment_files").on("change", function () {

    const files = Array.from(this.files || []);

    files.forEach(file => {

        if (file.size > 5 * 1024 * 1024) {

            Swal.fire({
                icon: "warning",
                title: "File too large",
                text: `${file.name} exceeds 5 MB`
            });

            return;
        }

        attachmentFiles.push(file);

    });

    renderAttachmentUploadPreview();

    $(this).val("");
});
$(document).on("click", ".btn-remove-upload-attachment", function () {
    const index = $(this).data("index");

    attachmentFiles.splice(index, 1);

    renderAttachmentUploadPreview();
});
function renderAttachmentUploadPreview() {
    renderAttachmentList("#attachmentPreview", attachmentFiles, "upload");
}
$(document).on(
    "click",
    ".btn-close-attachment-modal",
    function ()
    {
        attachmentFiles = [];

        $("#attachment_hash").val("");

        $("#attachment_files").val("");

        $("#attachmentPreview").html(
            attachmentEmptyState()
        );

        $("#attachmentModal")
            .removeClass("flex")
            .addClass("hidden");
    }
);
