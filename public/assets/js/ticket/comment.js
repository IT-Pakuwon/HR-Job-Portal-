// assets/js/ticket/comment.js

window.Ticket = window.Ticket || {};

function initTicketComment() {

    bindSubmitTicketComment();

    bindCommentAttachmentLabel();

}

function bindCommentAttachmentLabel() {

    $(document).on(
        'change',
        '#comment_attachments',
        function () {

            const files =
                Array.from(this.files || []);

            if (!files.length) {

                $('#comment_attachment_label')
                    .text('No file selected');

                return;

            }

            if (files.length === 1) {

                $('#comment_attachment_label')
                    .text(files[0].name);

                return;

            }

            $('#comment_attachment_label')
                .text(
                    `${files.length} files selected`
                );

        }
    );

}

function bindSubmitTicketComment() {

    $(document).on(
        'submit',
        '#form_ticket_comment',
        function (e) {

            e.preventDefault();

            submitTicketComment();

        }
    );

}

function submitTicketComment() {

    const eid =
        String(
            $('#comment_ticket_id').val() || ''
        ).trim();

    if (!eid) {

        showWarning(
            'Ticket not found'
        );

        return;

    }

    const message =
        $('#comment_message')
            .val()
            .trim();

    if (!message) {

        showWarning(
            'Comment message is required'
        );

        return;

    }

    const formData =
        new FormData();

    formData.append(
        'message',
        message
    );

    const files =
        $('#comment_attachments')[0]
            ?.files || [];

    Array.from(files).forEach(
        function (file) {

            formData.append(
                'attachments[]',
                file
            );

        }
    );

    const submitButton =
        $('#form_ticket_comment')
            .find('button[type="submit"]');

    setButtonLoading(
        submitButton,
        true,
        'Sending...'
    );

    $.ajax({

        url:
            `/ticket/comment/${eid}`,

        type:
            'POST',

        data:
            formData,

        processData:
            false,

        contentType:
            false,

        success: function (response) {

            $('#comment_message')
                .val('');

            $('#comment_attachments')
                .val('');

            $('#comment_attachment_label')
                .text('No file selected');

            reloadTicketComments(
                eid
            );

            showSuccess(
                response.message ||
                'Comment sent'
            );

        },

        error: function (xhr) {

            showError(
                xhr.responseJSON?.message ||
                'Failed send comment'
            );

        },

        complete: function () {

            setButtonLoading(
                submitButton,
                false
            );

        },

    });

}

function reloadTicketComments(eid) {

    $.ajax({

        url:
            `/ticket/detail/${eid}`,

        type:
            'GET',

        success: function (response) {

            renderTicketComments(
                response.data.comments || []
            );

            renderTicketTimeline(
                response.data.tracking || []
            );

        },

    });

}
