// assets/js/ticket/modal.js

window.Ticket = window.Ticket || {};

let currentModal = null;
let modalAnimating = false;

function resetTicketUrl() {

    const cleanUrl =
        `${window.location.origin}/ticket`;

    window.history.pushState(
        {},
        '',
        cleanUrl
    );

}

function openModal(selector) {

    const modal =
        $(selector);

    if (!modal.length) {
        return;
    }

    if (
        modal.hasClass('flex') ||
        modalAnimating
    ) {
        return;
    }

    modalAnimating = true;

    currentModal = selector;

    modal
        .removeClass('hidden')
        .addClass('flex');

    $('body')
        .addClass('overflow-hidden');

    requestAnimationFrame(() => {

        modal
            .find('.modal-panel')
            .removeClass(
                'opacity-0 translate-y-4 scale-[0.98]'
            )
            .addClass(
                'opacity-100 translate-y-0 scale-100'
            );

        modal
            .find('.modal-backdrop')
            .removeClass('opacity-0')
            .addClass('opacity-100');

        setTimeout(() => {

            modalAnimating = false;

        }, 220);

    });

}

function closeModal(modalSelector) {

    const modal =
        $(modalSelector);

    if (!modal.length) {
        return;
    }

    if (modalAnimating) {
        return;
    }

    modalAnimating = true;

    if (currentModal === modalSelector) {

        currentModal = null;

    }

    modal
        .find('.modal-backdrop')
        .removeClass('opacity-100')
        .addClass('opacity-0');

    modal
        .find('.modal-panel')
        .removeClass(
            'opacity-100 translate-y-0 scale-100'
        )
        .addClass(
            'opacity-0 translate-y-6 scale-[0.98]'
        );

    setTimeout(() => {

        modal
            .removeClass('flex')
            .addClass('hidden');

        if ($('.ticket-modal.flex').length <= 1) {

            $('body')
                .removeClass('overflow-hidden');

        }

        modalAnimating = false;

    }, 220);

}

function closeAllModal() {

    $('.ticket-modal').each(function () {

        closeModal(
            `#${$(this).attr('id')}`
        );

    });

}
function initModal() {

    $(document).off('click.ticketFormModalClose');
    $(document).off('click.ticketModalClose');
    $(document).off('click.ticketModalBackdrop');
    $(document).off('keydown.ticketModalEscape');

    /*
    |--------------------------------------------------------------------------
    | Form Modal Close (Create / Edit)
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click.ticketFormModalClose',
        '.btn-close-form-modal',
        function () {

            const $btn   = $(this);
            const $modal = $btn.closest('.ticket-modal');
            const modalId = '#' + $modal.attr('id');

            const isDark =
                $('html').hasClass('dark');

            Swal.fire({

                title: 'Close Form?',

                text:
                    'Unsaved changes will be lost.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonText:
                    'Yes, Close',

                cancelButtonText:
                    'Cancel',

                reverseButtons: true,

                confirmButtonColor:
                    '#dc2626',

                cancelButtonColor:
                    isDark
                        ? '#334155'
                        : '#e2e8f0',

                background:
                    isDark
                        ? '#111c2d'
                        : '#ffffff',

                color:
                    isDark
                        ? '#ffffff'
                        : '#0f172a',

                customClass: {

                    popup: `
                        rounded-lg
                        border border-white/[0.06]
                        shadow-[0_25px_80px_rgba(0,0,0,.35)]
                    `,

                    title: `
                        text-lg font-bold
                    `,

                    htmlContainer: `
                        text-sm text-slate-500
                    `,

                    confirmButton: `
                        rounded-lg
                        px-5 py-3
                        text-sm font-semibold
                    `,

                    cancelButton: `
                        rounded-lg
                        px-5 py-3
                        text-sm font-semibold
                    `,

                },

                didOpen: () => {

                    $('.swal2-container')
                        .css('z-index', 20000);

                },

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                closeModal(modalId);

                resetTicketUrl();

                if (modalId === '#createTicketModal') {

                    setTimeout(function () {

                        resetCreateTicketForm();

                    }, 240);

                }

            });

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Instant Modal Close
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click.ticketModalClose',
        '.btn-close-modal',
        function () {

            const modal =
                $(this)
                    .closest('.ticket-modal');

            closeModal(
                `#${modal.attr('id')}`
            );

            resetTicketUrl();

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Backdrop Close
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click.ticketModalBackdrop',
        '.modal-backdrop',
        function () {

            const $modal = $(this).closest('.ticket-modal');

            if ($modal.data('form-modal')) {
                return;
            }

            $modal
                .find('.btn-close-modal')
                .first()
                .trigger('click');

        }
    );

    /*
    |--------------------------------------------------------------------------
    | ESC Close
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'keydown.ticketModalEscape',
        function (e) {

            if (
                e.key !== 'Escape' ||
                !currentModal ||
                modalAnimating
            ) {
                return;
            }

            if ($(currentModal).data('form-modal')) {
                return;
            }

            $(currentModal)
                .find('.btn-close-modal')
                .first()
                .trigger('click');

        }
    );

}
