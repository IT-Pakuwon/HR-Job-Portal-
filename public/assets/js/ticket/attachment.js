// assets/js/ticket/attachment.js

window.Ticket = window.Ticket || {};

function initTicketAttachment() {

    bindTicketAttachmentInput();

    bindTicketAttachmentRemove();

}

function bindTicketAttachmentInput() {

    $(document).on(
        'change',
        Ticket.selectors.attachmentInput,
        function (e) {

            const files =
                Array.from(e.target.files || []);

            if (!files.length) {
                return;
            }

            files.forEach(function (file) {

                if (!validateTicketAttachment(file)) {
                    return;
                }

                Ticket.state.createAttachments.push(file);

            });

            renderTicketAttachment();

            $(this).val('');

        }
    );

}

function validateTicketAttachment(file) {

    const extension =
        getFileExtension(file.name);

    if (
        !Ticket.upload.allowedExtensions.includes(
            extension
        )
    ) {

        showWarning(
            `File format .${extension} is not supported`
        );

        return false;

    }

    if (
        file.size >
        Ticket.upload.maxFileSize
    ) {

        showWarning(
            `Maximum file size is ${Ticket.upload.maxFileSizeKB / 1024} MB`
        );

        return false;

    }

    return true;

}

function renderTicketAttachment() {

    const container =
        $(Ticket.selectors.attachmentList);

    container.empty();

    const existingFiles =
        Ticket.state.existingAttachments || [];

    const newFiles =
        Ticket.state.createAttachments || [];

    if (
        !existingFiles.length &&
        !newFiles.length
    ) {

        container.html(`

            <div class="
                rounded-lg
                border border-dashed border-slate-200

                bg-slate-50

                px-4 py-5

                text-center

                text-xs
                text-slate-400

                dark:border-white/10
                dark:bg-white/[0.03]
                dark:text-slate-500
            ">

                No attachment selected

            </div>

        `);

        return;

    }

    existingFiles.forEach(function (file, index) {

        const extension =
            getFileExtension(file.filename || '');

        container.append(`

            <div class="
                flex
                items-center
                justify-between
                gap-4

                rounded-lg

                border border-slate-200

                bg-white

                px-4 py-3

                dark:border-white/10
                dark:bg-[#0b1220]
            ">

                <div class="
                    flex
                    min-w-0
                    items-center
                    gap-3
                ">

                    <div class="
                        flex
                        h-10 w-10
                        shrink-0
                        items-center
                        justify-center

                        rounded-lg

                        bg-slate-100

                        text-slate-500

                        dark:bg-white/[0.05]
                        dark:text-slate-300
                    ">

                        <i class="fa-solid fa-file"></i>

                    </div>

                    <div class="min-w-0">

                        <a
                            href="${file.url}"
                            target="_blank"
                            class="
                                block
                                truncate

                                text-sm
                                font-medium

                                text-blue-600
                                hover:underline

                                dark:text-blue-400
                            "
                        >
                            ${file.filename}
                        </a>

                        <div class="
                            mt-1

                            text-xs

                            text-slate-400
                            dark:text-slate-500
                        ">

                            ${extension.toUpperCase()}

                        </div>

                    </div>

                </div>

                <button
                    type="button"
                    data-index="${index}"
                    class="
                        btn-remove-existing-ticket-attachment

                        inline-flex
                        h-9 w-9
                        shrink-0
                        items-center
                        justify-center

                        rounded-lg

                        border border-red-200

                        bg-red-50

                        text-red-500

                        transition

                        hover:bg-red-100

                        dark:border-red-500/20
                        dark:bg-red-500/10
                    "
                >

                    <i class="fa-solid fa-trash text-xs"></i>

                </button>

            </div>

        `);

    });

    newFiles.forEach(function (file, index) {

        const extension =
            getFileExtension(file.name);

        container.append(`

            <div class="
                flex
                items-center
                justify-between
                gap-4

                rounded-lg

                border border-slate-200

                bg-white

                px-4 py-3

                dark:border-white/10
                dark:bg-[#0b1220]
            ">

                <div class="
                    flex
                    min-w-0
                    items-center
                    gap-3
                ">

                    <div class="
                        flex
                        h-10 w-10
                        shrink-0
                        items-center
                        justify-center

                        rounded-lg

                        bg-slate-100

                        text-slate-500

                        dark:bg-white/[0.05]
                        dark:text-slate-300
                    ">

                        <i class="fa-solid fa-file"></i>

                    </div>

                    <div class="min-w-0">

                        <div class="
                            truncate

                            text-sm
                            font-medium

                            text-slate-700
                            dark:text-slate-200
                        ">
                            ${file.name}
                        </div>

                        <div class="
                            mt-1

                            text-xs

                            text-slate-400
                            dark:text-slate-500
                        ">

                            ${extension.toUpperCase()}
                            •
                            ${formatFileSize(file.size)}

                        </div>

                    </div>

                </div>

                <button
                    type="button"
                    data-index="${index}"
                    class="
                        btn-remove-ticket-attachment

                        inline-flex
                        h-9 w-9
                        shrink-0
                        items-center
                        justify-center

                        rounded-lg

                        border border-slate-200

                        bg-white

                        text-slate-400

                        transition

                        hover:bg-red-50
                        hover:text-red-500

                        dark:border-white/10
                        dark:bg-white/[0.03]
                        dark:hover:bg-red-500/10
                    "
                >

                    <i class="fa-solid fa-trash text-xs"></i>

                </button>

            </div>

        `);

    });

}
function bindTicketAttachmentRemove() {

    $(document).on(
        'click',
        '.btn-remove-ticket-attachment',
        function () {

            const index =
                $(this).data('index');

            Ticket.state.createAttachments.splice(
                index,
                1
            );

            renderTicketAttachment();

        }
    );

}

function getTicketAttachmentsFormData() {

    const formData =
        new FormData();

    Ticket.state.createAttachments.forEach(
        function (file) {

            formData.append(
                'attachments[]',
                file
            );

        }
    );

    return formData;

}
