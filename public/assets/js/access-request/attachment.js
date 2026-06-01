// ─── Shared state ───────────────────────────────────────────────────────────
let selectedFiles = [];
let existingAttachments = [];

// ─── Attachment card (same pattern as ITR) ───────────────────────────────────
function attachmentCard({ name, size = null, url = null, removable = false, index = null, removeClass = "btn-remove-existing-file" }) {
    const removeButton = removable
        ? `<button type="button"
                class="${removeClass} inline-flex h-7 w-7 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 dark:hover:bg-red-500/10"
                data-index="${index}">
                <i class="fa-solid fa-xmark text-xs"></i>
           </button>`
        : "";

    const inner = `
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#0b1220] px-4 py-3 shadow-sm transition-all duration-200 hover:bg-slate-50 dark:hover:bg-white/[0.03]">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05] text-slate-500 dark:text-slate-300">
                <i class="fa-solid fa-paperclip"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">${name}</p>
                ${size ? `<p class="mt-1 text-xs text-slate-400">${size}</p>` : ""}
            </div>
            ${removeButton}
        </div>
    `;

    if (!url) return inner;

    return `<div class="attachment-preview block cursor-pointer" data-url="${url}" data-filename="${name}">${inner}</div>`;
}

// ─── Attachment preview modal ─────────────────────────────────────────────────
function previewAttachment({ filename, signed_url }) {
    const url = signed_url;
    const ext = (filename || "").split(".").pop().toLowerCase();
    const imageTypes = ["jpg", "jpeg", "png", "webp", "gif"];

    if (imageTypes.includes(ext)) {
        $("#attachmentPreviewContent").html(`<img src="${url}" class="max-h-[85vh] mx-auto rounded-lg">`);
        $("#attachmentPreviewModal").removeClass("hidden").addClass("flex");
        return;
    }

    if (ext === "pdf") {
        $("#attachmentPreviewContent").html(`<iframe src="${url}" class="h-[85vh] w-full rounded-lg"></iframe>`);
        $("#attachmentPreviewModal").removeClass("hidden").addClass("flex");
        return;
    }

    const a = document.createElement("a");
    a.href = url;
    a.download = filename || "";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function closeAttachmentPreview() {
    $("#attachmentPreviewModal").removeClass("flex").addClass("hidden");
    $("#attachmentPreviewContent").html("");
}

$(document).on("keydown", function (e) {
    if (e.key === "Escape") closeAttachmentPreview();
});

$(document).on("click", ".attachment-preview", function () {
    previewAttachment({
        filename: $(this).data("filename"),
        signed_url: $(this).data("url"),
    });
});

// ─── Render existing attachments (in the create/edit form) ───────────────────
function renderExistingAttachments(files = []) {
    if (!files.length) {
        $("#existingAttachmentContainer").html("");
        return;
    }

    let html = "";
    files.forEach((file, index) => {
        html += attachmentCard({
            name: file.display_name ?? file.filename ?? "-",
            url: file.url ?? "#",
            removable: true,
            index,
            removeClass: "btn-remove-existing-file",
        });
    });

    $("#existingAttachmentContainer").html(html);
}

// ─── Render new (staged) attachments ─────────────────────────────────────────
function renderNewAttachments() {
    let html = "";

    selectedFiles.forEach((file, index) => {
        html += attachmentCard({
            name: file.name,
            size: formatFileSize(file.size),
            removable: true,
            index,
            removeClass: "btn-remove-new-file",
        });
    });

    $("#newAttachmentContainer").html(html);

    const dt = new DataTransfer();
    selectedFiles.forEach((f) => dt.items.add(f));
    $("#requestAttachment")[0].files = dt.files;
}

// ─── Event handlers ───────────────────────────────────────────────────────────
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
            const maxSize = 5 * 1024 * 1024;
            const files = [...e.target.files];
            const validFiles = [];

            files.forEach((file) => {
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: "warning",
                        title: "File Too Large",
                        text: `${file.name} exceeds the maximum size of 5 MB.`,
                        confirmButtonColor: "#2563eb",
                    });
                    return;
                }
                validFiles.push(file);
            });

            selectedFiles = [...selectedFiles, ...validFiles];
            renderNewAttachments();
            $(this).val("");
        });
}
