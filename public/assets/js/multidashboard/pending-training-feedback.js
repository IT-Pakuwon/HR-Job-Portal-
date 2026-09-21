(function () {
    'use strict';

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    var currentRows = [];

    function init() {
        var container = document.getElementById('pendingFeedbackReminders');
        if (!container) return;

        fetch('/training-list/pending-feedback', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (res) {
                currentRows = res.data || [];
                render(container);
            })
            .catch(function () {});
    }

    function render(container) {
        if (!currentRows.length) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = currentRows.map(function (row, idx) {
            var name = esc(row.training_name || '-');
            var bySuffix = row.speaker_name ? ' by ' + esc(row.speaker_name) : '';
            var href = '/training-list/feedback/' + encodeURIComponent(row.eid);

            return '<a href="' + href + '" data-feedback-idx="' + idx + '" class="pendingFeedbackLink mb-2 flex items-center justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 shadow-sm transition hover:bg-amber-100 dark:border-amber-700/60 dark:bg-amber-900/20 dark:text-amber-300 dark:hover:bg-amber-900/30">'
                + '<span>📝 Please fill the feedback for training "' + name + '"' + bySuffix + '</span>'
                + '<span aria-hidden="true">&rarr;</span>'
                + '</a>';
        }).join('');
    }

    // Plain left-click opens the modal in place (no page navigation); a
    // modifier click (middle-click, ctrl/cmd/shift+click) is left alone so
    // "open in new tab" still works via the anchor's real href.
    document.addEventListener('click', function (e) {
        var link = e.target.closest('.pendingFeedbackLink');
        if (!link || e.defaultPrevented) return;
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var row = currentRows[Number(link.getAttribute('data-feedback-idx'))];
        if (!row) return;

        e.preventDefault();
        openInlineFeedbackModal(row);
    });

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function toast(icon, title) {
        window.Swal.fire({ toast: true, position: 'top-end', icon: icon, title: title, showConfirmButton: false, timer: 2500, timerProgressBar: true });
    }

    var stylesInjected = false;
    function injectStyles() {
        if (stylesInjected || document.getElementById('pendingFeedbackModalStyles')) return;
        stylesInjected = true;

        var style = document.createElement('style');
        style.id = 'pendingFeedbackModalStyles';
        style.textContent = [
            '.pendingFeedbackModalPopup{padding:0!important;border-radius:16px!important;width:560px!important;max-width:calc(100vw - 32px)!important;}',
            '.pendingFeedbackModalPopup .swal2-html-container{margin:0!important;padding:0!important;max-height:none!important;}',
            '.pendingFeedbackModalPopup .swal2-actions{flex-direction:column;width:100%;gap:4px;margin:0!important;padding:16px 24px 20px!important;border-top:1px solid #f0f1f3;background:#fafafa;border-radius:0 0 16px 16px;}',
            '.feedbackModal-header{display:flex;align-items:center;gap:12px;padding:20px 24px;border-bottom:1px solid #f0f1f3;}',
            '.feedbackModal-icon{width:44px;height:44px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,#374151,#111827);display:flex;align-items:center;justify-content:center;font-size:20px;}',
            '.feedbackModal-title{font-size:15px;font-weight:700;color:#111827;margin:0;text-align:left;overflow-wrap:break-word;word-break:break-word;}',
            '.feedbackModal-subtitle{font-size:11px;color:#6b7280;margin:3px 0 0;}',
            '.feedbackModal-body{padding:20px 24px 24px;text-align:left;max-height:58vh;overflow-y:auto;}',
            '.feedbackModal-notice{display:flex;gap:8px;align-items:flex-start;padding:10px 12px;border-radius:8px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:12px;margin-bottom:16px;}',
            '.feedbackModal-question{padding:16px;border-radius:12px;background:#f9fafb;border:1px solid #f0f1f3;}',
            '.feedbackModal-question + .feedbackModal-question{margin-top:12px;}',
            '.feedbackModal-qHead{display:flex;align-items:flex-start;gap:8px;margin-bottom:12px;}',
            '.feedbackModal-qNum{display:flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#111827;color:#fff;font-size:10.5px;font-weight:700;flex-shrink:0;margin-top:1px;}',
            '.feedbackModal-qText{font-size:13px;font-weight:600;color:#111827;line-height:1.4;}',
            '.feedbackModal-choiceGroup{display:flex;gap:8px;}',
            '.feedbackModal-choice{position:relative;flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#fff;font-size:13px;font-weight:600;color:#374151;cursor:pointer;transition:border-color .15s,background .15s,color .15s;}',
            '.feedbackModal-choice input{position:absolute;opacity:0;width:0;height:0;}',
            '.feedbackModal-choice:has(input:checked){border-color:#111827;background:#111827;color:#fff;}',
            '.feedbackModal-choice:has(input:disabled){cursor:not-allowed;}',
            '.feedbackModal-choice:has(input:disabled):not(:has(input:checked)){opacity:.5;}',
            '.feedbackModal-ratingGroup{display:flex;gap:8px;}',
            '.feedbackModal-ratingItem{position:relative;flex:1;display:flex;align-items:center;justify-content:center;padding:10px 0;border-radius:8px;border:1px solid #d1d5db;background:#fff;font-size:13px;font-weight:700;color:#374151;cursor:pointer;transition:border-color .15s,background .15s,color .15s;}',
            '.feedbackModal-ratingItem input{position:absolute;opacity:0;width:0;height:0;}',
            '.feedbackModal-ratingItem:has(input:checked){border-color:#f59e0b;background:#fffbeb;color:#b45309;}',
            '.feedbackModal-ratingItem:has(input:disabled){cursor:not-allowed;}',
            '.feedbackModal-ratingItem:has(input:disabled):not(:has(input:checked)){opacity:.5;}',
            '.feedbackModal-ratingScale{display:flex;justify-content:space-between;margin-top:6px;font-size:10.5px;color:#9ca3af;}',
            '.feedbackModal-textarea{display:block;width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;color:#111827;background:#fff;resize:vertical;min-height:60px;font-family:inherit;}',
            '.feedbackModal-textarea:focus{outline:none;border-color:#111827;box-shadow:0 0 0 3px rgba(17,24,39,.08);}',
            '.feedbackModal-textarea:disabled{background:#f9fafb;color:#6b7280;cursor:not-allowed;}',
            '.pendingFeedbackConfirmBtn{background:#111827!important;color:#fff!important;border:none!important;padding:11px 0!important;width:100%;font-size:13px!important;font-weight:600!important;border-radius:8px!important;box-shadow:none!important;}',
            '.pendingFeedbackConfirmBtn:hover{background:#374151!important;}',
            '.pendingFeedbackCancelBtn{background:transparent!important;color:#6b7280!important;box-shadow:none!important;font-weight:500!important;font-size:12.5px!important;padding:4px 0!important;margin:0!important;}',
            '.pendingFeedbackCancelBtn:hover{color:#111827!important;text-decoration:underline;}',
        ].join('\n');
        document.head.appendChild(style);
    }

    function renderQuestion(q, readOnly) {
        var name = 'pending_feedback_q_' + q.question_order;
        var inputHtml = '';

        if (q.question_type === 'Single Choice') {
            inputHtml = '<div class="feedbackModal-choiceGroup">' + (q.options || []).map(function (opt) {
                return '<label class="feedbackModal-choice"><input type="radio" name="' + name + '" value="' + esc(opt) + '" '
                    + (q.answer_text === opt ? 'checked' : '') + ' ' + (readOnly ? 'disabled' : '') + '>' + esc(opt) + '</label>';
            }).join('') + '</div>';
        } else if (q.question_type === 'Rating') {
            var options = q.options || [];
            inputHtml = '<div class="feedbackModal-ratingGroup">' + options.map(function (opt) {
                return '<label class="feedbackModal-ratingItem"><input type="radio" name="' + name + '" value="' + esc(opt) + '" '
                    + (String(q.answer_number != null ? q.answer_number : '') === String(opt) ? 'checked' : '') + ' ' + (readOnly ? 'disabled' : '') + '>' + esc(opt) + '</label>';
            }).join('') + '</div>'
                + '<div class="feedbackModal-ratingScale"><span>Sangat tidak puas</span><span>Sangat puas</span></div>';
        } else {
            inputHtml = '<textarea class="feedbackModal-textarea" name="' + name + '" rows="2" ' + (readOnly ? 'disabled' : '') + '>' + esc(q.answer_text || '') + '</textarea>';
        }

        return '<div class="feedbackModal-question">'
            + '<div class="feedbackModal-qHead"><span class="feedbackModal-qNum">' + q.question_order + '</span>'
            + '<span class="feedbackModal-qText">' + esc(q.question_text) + (readOnly ? '' : ' <span style="color:#ef4444;">*</span>') + '</span></div>'
            + inputHtml
            + '</div>';
    }

    function openInlineFeedbackModal(row) {
        injectStyles();

        var originalPath = location.pathname + location.search;
        var targetPath = '/training-list/feedback/' + encodeURIComponent(row.eid);
        if (location.pathname !== targetPath) {
            history.pushState({ pendingFeedbackModal: true }, '', targetPath);
        }

        var restorePath = function () {
            if (location.pathname + location.search !== originalPath) {
                history.pushState({}, '', originalPath);
            }
        };

        fetch('/training-list/my/' + row.id + '/feedback', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) {
                if (!r.ok) throw new Error('failed');
                return r.json();
            })
            .then(function (res) {
                var readOnly = !res.is_open;
                var questions = res.questions || [];
                var questionsHtml = questions.map(function (q) { return renderQuestion(q, readOnly); }).join('');
                var notice = readOnly
                    ? '<div class="feedbackModal-notice">⚠️ Feedback window is currently closed — showing your submitted answers (read only).</div>'
                    : '';

                window.Swal.fire({
                    html: '<div class="feedbackModal-header"><div class="feedbackModal-icon">📝</div><div>'
                        + '<h3 class="feedbackModal-title">' + (readOnly ? 'Feedback' : 'Fill Feedback') + '</h3>'
                        + '<p class="feedbackModal-subtitle">' + esc(row.training_name || '') + '</p>'
                        + '</div></div>'
                        + '<div class="feedbackModal-body">' + notice + '<div>' + questionsHtml + '</div></div>',
                    showCancelButton: !readOnly,
                    buttonsStyling: false,
                    confirmButtonText: readOnly ? 'Close' : 'Submit',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'pendingFeedbackModalPopup',
                        confirmButton: 'pendingFeedbackConfirmBtn',
                        cancelButton: 'pendingFeedbackCancelBtn',
                    },
                    preConfirm: function () {
                        if (readOnly) return true;

                        var answers = questions.map(function (q) {
                            var name = 'pending_feedback_q_' + q.question_order;
                            var el = document.querySelector('input[name="' + name + '"]:checked, textarea[name="' + name + '"]');
                            return { question_order: q.question_order, value: el ? el.value : null };
                        });

                        var unanswered = answers.some(function (a) { return a.value === null || String(a.value).trim() === ''; });
                        if (unanswered) {
                            window.Swal.showValidationMessage('Mohon jawab semua pertanyaan sebelum submit');
                            return false;
                        }

                        return answers;
                    },
                }).then(function (result) {
                    restorePath();

                    if (readOnly || !result.isConfirmed) return;

                    fetch('/training-list/my/' + row.id + '/feedback', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken(),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ answers: result.value }),
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (subRes) {
                            toast(subRes.success ? 'success' : 'error', subRes.message);
                            if (subRes.success) init();
                        })
                        .catch(function () {
                            toast('error', 'Gagal menyimpan feedback');
                        });
                });
            })
            .catch(function () {
                restorePath();
                toast('error', 'Gagal memuat feedback');
            });
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
