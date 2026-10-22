(function () {
    'use strict';

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    var currentRows = [];

    // afterLoad (optional) replaces the default popup, e.g. after a submit.
    function init(afterLoad) {
        var container = document.getElementById('pendingFeedbackReminders');
        if (!container) return;

        fetch('/training-list/pending-feedback', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (res) {
                currentRows = res.data || [];
                render(container);
                if (typeof afterLoad === 'function') afterLoad(); else promptPending();
            })
            .catch(function () {});
    }

    // Shown on every dashboard load while feedback is still pending, so the
    // user keeps getting a one-click path into the form until they fill it.
    function promptPending() {
        if (!currentRows.length || !window.Swal || window.Swal.isVisible()) return;

        var first = currentRows[0];
        var more = currentRows.length > 1 ? ' (+' + (currentRows.length - 1) + ' more)' : '';

        injectStyles();

        var speaker = first.speaker_name ? '<div class="feedbackPrompt-speaker">by ' + esc(first.speaker_name) + '</div>' : '';
        var badge = more ? '<span class="feedbackPrompt-badge">' + esc(more.trim()) + '</span>' : '';

        window.Swal.fire({
            html: '<div class="feedbackPrompt-icon">📝</div>'
                + '<h3 class="feedbackPrompt-title">Your feedback is needed</h3>'
                + '<p class="feedbackPrompt-text">Share how the training went — it only takes a minute.</p>'
                + '<div class="feedbackPrompt-card">'
                + '<div class="feedbackPrompt-name">' + esc(first.training_name || '-') + '</div>'
                + speaker + badge
                + '</div>',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Fill feedback now',
            cancelButtonText: 'Remind me later',
            customClass: {
                popup: 'pendingFeedbackModalPopup feedbackPrompt-popup',
                confirmButton: 'pendingFeedbackConfirmBtn',
                cancelButton: 'pendingFeedbackCancelBtn',
            },
        }).then(function (result) {
            if (result.isConfirmed) openInlineFeedbackModal(first);
        });
    }

    function render(container) {
        if (!currentRows.length) {
            container.innerHTML = '';
            // an empty grid item still takes a row (+ the grid's row gap), so keep it out of layout
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
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

    // Success popup after a submit; if more trainings are still pending, offer
    // to continue with the next one.
    function showSubmitted() {
        injectStyles();

        var next = currentRows[0];
        var left = currentRows.length;

        window.Swal.fire({
            html: '<div class="feedbackPrompt-icon feedbackPrompt-iconOk">✓</div>'
                + '<h3 class="feedbackPrompt-title">Thank you!</h3>'
                + '<p class="feedbackPrompt-text">Your feedback has been submitted.'
                + (left ? '<br>You still have <b>' + left + '</b> training' + (left > 1 ? 's' : '') + ' waiting for feedback.' : '') + '</p>',
            showCancelButton: !!left,
            buttonsStyling: false,
            confirmButtonText: left ? 'Fill next feedback' : 'Close',
            cancelButtonText: 'Later',
            customClass: {
                popup: 'pendingFeedbackModalPopup feedbackPrompt-popup',
                confirmButton: 'pendingFeedbackConfirmBtn',
                cancelButton: 'pendingFeedbackCancelBtn',
            },
        }).then(function (result) {
            if (result.isConfirmed && next) openInlineFeedbackModal(next);
        });
    }

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
            '.feedbackPrompt-popup{width:420px!important;}',
            '.feedbackPrompt-popup .swal2-html-container{padding:28px 28px 20px!important;text-align:center;}',
            '.feedbackPrompt-icon{width:64px;height:64px;margin:0 auto 14px;border-radius:18px;background:linear-gradient(135deg,#fbbf24,#f59e0b);display:flex;align-items:center;justify-content:center;font-size:30px;box-shadow:0 8px 20px rgba(245,158,11,.35);}',
            '.feedbackPrompt-popup .swal2-actions{gap:8px;}',
            '.feedbackPrompt-popup .pendingFeedbackCancelBtn{width:100%;padding:11px 0!important;font-size:13px!important;font-weight:600!important;border:1px solid #d1d5db!important;border-radius:8px!important;background:#fff!important;color:#374151!important;}',
            '.feedbackPrompt-popup .pendingFeedbackCancelBtn:hover{background:#f3f4f6!important;color:#111827!important;text-decoration:none;}',
            '.feedbackPrompt-iconOk{background:linear-gradient(135deg,#34d399,#059669);color:#fff;font-weight:700;box-shadow:0 8px 20px rgba(5,150,105,.35);}',
            '.feedbackPrompt-title{font-size:18px;font-weight:700;color:#111827;margin:0 0 6px;}',
            '.feedbackPrompt-text{font-size:13px;color:#6b7280;margin:0 0 18px;line-height:1.5;}',
            '.feedbackPrompt-card{padding:12px 14px;border-radius:12px;background:#fffbeb;border:1px solid #fde68a;}',
            '.feedbackPrompt-name{font-size:13.5px;font-weight:600;color:#92400e;line-height:1.4;}',
            '.feedbackPrompt-speaker{font-size:11.5px;color:#b45309;margin-top:2px;}',
            '.feedbackPrompt-badge{display:inline-block;margin-top:8px;padding:2px 10px;border-radius:999px;background:#111827;color:#fff;font-size:11px;font-weight:600;}',
            '.pendingFeedbackConfirmBtn{background:#111827!important;color:#fff!important;border:none!important;padding:11px 0!important;width:100%;font-size:13px!important;font-weight:600!important;border-radius:8px!important;box-shadow:none!important;}',
            '.pendingFeedbackConfirmBtn:hover{background:#374151!important;}',
            '.pendingFeedbackCancelBtn{background:#fef2f2!important;color:#b91c1c!important;border:1px solid #fecaca!important;}',
            '.pendingFeedbackCancelBtn:hover{background:#fee2e2!important;}',
            '.pendingFeedbackNeutralBtn{background:#f3f4f6!important;color:#374151!important;border:1px solid #e5e7eb!important;}',
            '.pendingFeedbackNeutralBtn:hover{background:#e5e7eb!important;}',
            '.pendingFeedbackModalPopup .swal2-actions{gap:8px!important;}',
            // confirmation popups
            '.pendingFeedbackAskPopup{width:380px!important;max-width:calc(100vw - 32px)!important;border-radius:16px!important;padding:20px 20px 18px!important;}',
            '.pendingFeedbackAskPopup .swal2-title{font-size:18px!important;padding:0!important;margin:4px 0 0!important;}',
            '.pendingFeedbackAskPopup .swal2-html-container{font-size:13.5px!important;margin:8px 0 0!important;padding:0!important;}',
            '.pendingFeedbackAskActions{flex-direction:column;width:100%;gap:8px;margin:18px 0 0!important;padding:0!important;}',
            // dark mode
            '.dark .pendingFeedbackModalPopup{background:#1e293b!important;color:#e2e8f0!important;}',
            '.dark .pendingFeedbackModalPopup .swal2-actions{border-top-color:#334155;background:#0f172a;}',
            '.dark .feedbackModal-header{border-bottom-color:#334155;}',
            '.dark .feedbackModal-icon{background:linear-gradient(135deg,#0e7490,#2563eb);}',
            '.dark .feedbackModal-title,.dark .feedbackModal-qText{color:#f1f5f9;}',
            '.dark .feedbackModal-subtitle{color:#94a3b8;}',
            '.dark .feedbackModal-notice{background:#422006;border-color:#854d0e;color:#fde68a;}',
            '.dark .feedbackModal-question{background:#0f172a;border-color:#334155;}',
            '.dark .feedbackModal-qNum{background:#0e7490;}',
            '.dark .feedbackModal-choice,.dark .feedbackModal-ratingItem{background:#1e293b;border-color:#475569;color:#cbd5e1;}',
            '.dark .feedbackModal-choice:has(input:checked){border-color:#22d3ee;background:#0e7490;color:#fff;}',
            '.dark .feedbackModal-ratingItem:has(input:checked){border-color:#f59e0b;background:#78350f;color:#fde68a;}',
            '.dark .feedbackModal-ratingScale{color:#94a3b8;}',
            '.dark .feedbackModal-textarea{background:#1e293b;border-color:#475569;color:#f1f5f9;}',
            '.dark .feedbackModal-textarea:focus{border-color:#22d3ee;box-shadow:0 0 0 3px rgba(34,211,238,.18);}',
            '.dark .feedbackModal-textarea:disabled{background:#0f172a;color:#94a3b8;}',
            '.dark .pendingFeedbackConfirmBtn{background:#0e7490!important;border-color:#0e7490!important;}',
            '.dark .pendingFeedbackConfirmBtn:hover{background:#0891b2!important;}',
            '.dark .pendingFeedbackCancelBtn{background:rgba(127,29,29,.35)!important;color:#fca5a5!important;border-color:rgba(248,113,113,.4)!important;}',
            '.dark .pendingFeedbackCancelBtn:hover{background:rgba(127,29,29,.55)!important;}',
            '.dark .pendingFeedbackNeutralBtn{background:#334155!important;color:#e2e8f0!important;border-color:#475569!important;}',
            '.dark .pendingFeedbackNeutralBtn:hover{background:#475569!important;}',
            '.dark .pendingFeedbackAskPopup{background:#1e293b!important;color:#e2e8f0!important;}',
            '.dark .pendingFeedbackAskPopup .swal2-title,.dark .pendingFeedbackAskPopup .swal2-html-container{color:#e2e8f0!important;}',
            '.dark .pendingFeedbackModalPopup .swal2-validation-message{background:#0f172a;color:#fca5a5;}',
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

    function formHtml(row, questions, readOnly) {
        var questionsHtml = questions.map(function (q) { return renderQuestion(q, readOnly); }).join('');
        var notice = readOnly
            ? '<div class="feedbackModal-notice">⚠️ Feedback window is currently closed — showing your submitted answers (read only).</div>'
            : '';

        return '<div class="feedbackModal-header"><div class="feedbackModal-icon">📝</div><div>'
            + '<h3 class="feedbackModal-title">' + (readOnly ? 'Feedback' : 'Fill Feedback') + '</h3>'
            + '<p class="feedbackModal-subtitle">' + esc(row.training_name || '') + '</p>'
            + '</div></div>'
            + '<div class="feedbackModal-body">' + notice + '<div>' + questionsHtml + '</div></div>';
    }

    // What is currently filled in (null = not answered yet)
    function collectAnswers(questions) {
        return questions.map(function (q) {
            var name = 'pending_feedback_q_' + q.question_order;
            var el = document.querySelector('input[name="' + name + '"]:checked, textarea[name="' + name + '"]');
            return { question_order: q.question_order, value: el ? el.value : null };
        });
    }

    function savedMap(answers) {
        var map = {};
        (answers || []).forEach(function (a) {
            if (a.value !== null && String(a.value).trim() !== '') map[a.question_order] = a.value;
        });
        return map;
    }

    // Questions with the user's unsaved answers put back in, so "Keep editing" / "Review again" loses nothing
    function withSaved(questions, saved) {
        return questions.map(function (q) {
            var copy = Object.assign({}, q);
            if (saved[q.question_order] !== undefined) {
                if (q.question_type === 'Rating') copy.answer_number = saved[q.question_order];
                else copy.answer_text = saved[q.question_order];
            }
            return copy;
        });
    }

    // Small confirmation popup; resolves true for the main button. `danger` makes the main button red.
    function confirmAsk(opts) {
        return window.Swal.fire({
            icon: opts.danger ? 'warning' : 'question',
            title: opts.title,
            text: opts.text,
            showCancelButton: true,
            reverseButtons: false,
            focusCancel: !!opts.danger,
            allowOutsideClick: false,
            buttonsStyling: false,
            confirmButtonText: opts.yes,
            cancelButtonText: opts.no,
            customClass: {
                popup: 'pendingFeedbackAskPopup',
                actions: 'pendingFeedbackAskActions',
                confirmButton: opts.danger ? 'pendingFeedbackCancelBtn' : 'pendingFeedbackConfirmBtn',
                cancelButton: 'pendingFeedbackNeutralBtn',
            },
        }).then(function (r) { return !!r.isConfirmed; });
    }

    function submitFeedback(row, answers) {
        fetch('/training-list/my/' + row.id + '/feedback', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ answers: answers }),
        })
            .then(function (r) { return r.json(); })
            .then(function (subRes) {
                toast(subRes.success ? 'success' : 'error', subRes.message);
                if (subRes.success) init();
            })
            .catch(function () {
                toast('error', 'Gagal menyimpan feedback');
            });
    }

    // The form. Submit and Cancel are the same size; each asks "are you sure?" before doing anything, and
    // answering "no" brings the form back with everything still filled in.
    function showForm(row, questions, saved, restorePath) {
        window.Swal.fire({
            html: formHtml(row, withSaved(questions, saved), false),
            showCancelButton: false,
            showDenyButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            buttonsStyling: false,
            confirmButtonText: 'Submit',
            denyButtonText: 'Cancel',
            customClass: {
                popup: 'pendingFeedbackModalPopup',
                confirmButton: 'pendingFeedbackConfirmBtn',
                denyButton: 'pendingFeedbackCancelBtn',
            },
            preConfirm: function () {
                var answers = collectAnswers(questions);
                var unanswered = answers.some(function (a) { return a.value === null || String(a.value).trim() === ''; });
                if (unanswered) {
                    window.Swal.showValidationMessage('Mohon jawab semua pertanyaan sebelum submit');
                    return false;
                }
                return answers;
            },
            preDeny: function () { return collectAnswers(questions); },
        }).then(function (result) {
            if (result.isConfirmed) {
                confirmAsk({
                    title: 'Submit your feedback?',
                    text: 'Please make sure your answers are correct before sending.',
                    yes: 'Yes, submit',
                    no: 'Review again',
                }).then(function (ok) {
                    if (!ok) { showForm(row, questions, savedMap(result.value), restorePath); return; }
                    restorePath();
                    submitFeedback(row, result.value);
                });
            } else if (result.isDenied) {
                confirmAsk({
                    title: 'Discard this feedback?',
                    text: 'Your answers will not be saved.',
                    yes: 'Yes, discard',
                    no: 'Keep editing',
                    danger: true,
                }).then(function (ok) {
                    if (!ok) { showForm(row, questions, savedMap(result.value), restorePath); return; }
                    restorePath();
                    toast('info', 'Feedback discarded');
                });
            } else {
                restorePath();
            }
        });
    }

    function openInlineFeedbackModal(row) {
        injectStyles();

        var originalPath = location.pathname + location.search;
        var targetPath = '/training-list/feedback/' + encodeURIComponent(row.eid);
        // The installed My APP must never leave /my-page: moving the address outside its scope makes the phone
        // wrap the app in a browser bar (✕ / URL). So there the address stays as it is.
        var inMyApp = location.pathname.indexOf('/my-page') === 0;
        if (!inMyApp && location.pathname !== targetPath) {
            history.pushState({ pendingFeedbackModal: true }, '', targetPath);
        }

        var restorePath = function () {
            if (!inMyApp && location.pathname + location.search !== originalPath) {
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
                if (readOnly) {
                    window.Swal.fire({
                        html: formHtml(row, questions, true),
                        buttonsStyling: false,
                        confirmButtonText: 'Close',
                        customClass: { popup: 'pendingFeedbackModalPopup', confirmButton: 'pendingFeedbackConfirmBtn' },
                    }).then(restorePath);
                    return;
                }

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
                            if (!subRes.success) {
                                toast('error', subRes.message);
                                return;
                            }
                            init(function () { showSubmitted(); });
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

    // Used by My APP when a notification / link points at a feedback form: refresh the pending list, then open that form.
    window.openPendingFeedback = function (eid) {
        var find = function () {
            return currentRows.filter(function (r) { return String(r.eid) === String(eid); })[0];
        };

        fetch('/training-list/pending-feedback', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (res) {
                currentRows = res.data || [];
                var container = document.getElementById('pendingFeedbackReminders');
                if (container) render(container);

                var row = find();
                if (row) openInlineFeedbackModal(row);
                else toast('info', 'This feedback is no longer pending');
            })
            .catch(function () { toast('error', 'Gagal memuat feedback'); });
    };

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
