<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Send Email – {{ $ponbr }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- libs -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
</head>

<style>
    /* Make Tagify look like a normal input */
    .tagify {
        width: 100%;
        min-height: 30px;
        height: auto;
        border-radius: 6px;
    }

    /* Allow wrapping to multiple lines */
    .tagify__input {
        min-width: 120px;
        white-space: normal;
    }

    /* Wrap tags nicely */
    .tagify__tag {
        margin: 3px 4px;
    }

    /* Prevent horizontal scroll */
    .tagify__input,
    .tagify {
        overflow-x: hidden;
    }

    /* Optional: Outlook-like spacing */
    .tagify--focus {
        border-color: #2563eb;
    }
</style>

<body style="background:#f5f7fb;margin:0;padding:0;height:100vh;">

    <!-- ================= WRAPPER ================= -->
    <div
        style="
    width:100%;
    background:#ffffff;
    display:flex;
    flex-direction:column;
    min-height:100vh;
">

        <!-- ================= CONTENT ================= -->
        <div class="card-body"
            style="
    padding:16px;
    display:flex;
    flex-direction:column;
    gap:8px;
    flex:1;
    min-height:0;
">
            <!-- ===== HEADER + ACTION (ONE ROW) ===== -->
            <div style="
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
">

    <!-- ===== OVERLAY LOADING ===== -->
    <div id="sendOverlay" style="
        position:fixed;
        inset:0;
        background:rgba(17,24,39,.55);
        display:none;
        align-items:center;
        justify-content:center;
        z-index:99999;
    ">
        <div style="
            background:#fff;
            padding:18px 22px;
            border-radius:12px;
            display:flex;
            align-items:center;
            gap:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.25);
            min-width:240px;
        ">
            <div class="spinner-border" role="status" aria-hidden="true"></div>
            <div>
                <div style="font-weight:600;color:#111827;">Sending email…</div>
                <div style="font-size:12px;color:#6b7280;">Please wait</div>
            </div>
        </div>
    </div>


                <!-- LEFT : TITLE -->
                <div>
                    <h2 style="margin:0;font-size:18px;font-weight:600;color:#111827;">
                        PO Email
                    </h2>
                    <div style="font-size:12px;color:#6b7280;">
                        Send purchase order details by email
                    </div>
                </div>

                <!-- RIGHT : ACTIONS -->
                <div style="display:flex;align-items:center;gap:12px;">

                    <!-- BACK -->
                    <button type="button" onclick="history.back()"
                        style="
                display:inline-flex;
                align-items:center;
                gap:6px;
                background:#f3f4f6;
                color:#374151;
                border:1px solid #d1d5db;
                border-radius:8px;
                padding:6px 10px;
                cursor:pointer;
                font-size:13px;
            ">
                        ← Back
                    </button>

                    <!-- SEND -->
                    <button id="btnSend"
                        style="
                display:inline-flex;
                align-items:center;
                gap:8px;
                background:#2563eb;
                color:#fff;
                border:none;
                border-radius:8px;
                padding:8px 14px;
                cursor:pointer;
                font-size:14px;
            ">
                        Send
                    </button>

                </div>
            </div>



            <!-- ===== META GRID ===== -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px 12px;">
                <div>
                    <div style="font-size:12px;color:#6b7280;">Vendor</div>
                    <input id="vendorName" type="text" value="{{ $vendor }}" readonly
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                </div>
                <div>
                    <div style="font-size:12px;color:#6b7280;">Order Nbr</div>
                    <input id="orderNbr" type="text" value="{{ $ponbr }}" readonly
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">From</label>
                    <input id="from" type="text" value="{{ $from_email }}" readonly
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                    <div style="font-size:11px;color:#9ca3af;">This email will appear as the sender</div>
                </div>
                {{-- <div>
                    <div style="font-size:12px;color:#6b7280;">Template</div>
                    <input id="templateType" type="text" value="{{ $template }}" readonly
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                </div> --}}

            </div>

            <!-- ===== FROM / TO ===== -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column: span 2;">
                    <label style="font-size:12px;color:#6b7280;">Send Email To :</label>
                    <input id="toInput"
                        value="{{ $to_email }}"
                        placeholder="email1@domain.com, email2@domain.com"
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                    <div style="font-size:11px;color:#9ca3af;">
                        Press <strong>Enter</strong> or <strong>Space</strong> to add the email
                    </div>
                </div>
            </div>


            <!-- ===== RECIPIENTS ===== -->
            {{-- <div style="display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap;">
                <div style="font-size:12px;color:#6b7280;white-space:nowrap;">Recipients:</div>
                <div id="toChips" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
            </div> --}}

            <!-- ===== CC / BCC ===== -->
            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label style="font-size:12px;color:#6b7280;">Cc</label>
                    <input id="ccInput" type="text"
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                    {{-- <div id="ccChips"></div> --}}
                </div>
                <div style="flex:1;">
                    <label style="font-size:12px;color:#6b7280;">Bcc</label>
                    <input id="bccInput" type="text"
                        style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                    {{-- <div id="bccChips"></div> --}}
                </div>
            </div>

            <!-- ===== SUBJECT ===== -->
            <div>
                <label style="font-size:12px;color:#6b7280;">Subject</label>
                <input id="subject" type="text" value="{{ $subject_email }}"
                    style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
            </div>

            <!-- ================= EDITOR AREA ================= -->
            <div
                style="
    margin-top:12px;
    flex:1;
    min-height:0;
    display:flex;
    flex-direction:column;
    overflow:hidden;
">
                <div id="editor"
                    style="
        flex:1;
        border:1px solid #d1d5db;
        border-radius:6px;
        overflow-y:auto;
    ">
                </div>
            </div>

            <input type="hidden" id="ponbr" value="{{ $ponbr }}">
            <input type="hidden" id="cpny_id" value="{{ $cpny_id ?? ($po->cpny_id ?? '') }}">

        </div>
    </div>

    <!-- ================= JS ================= -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    {{--
    <script>
        const toTagify = new Tagify(document.querySelector('#toInput'), {
            delimiters: ", ",
            pattern: /^[\w.!#$%&'*+\/=?^`{|}~-]+@[\w-]+(\.[\w-]+)+$/,
        dropdown: {
            enabled: 0
        }
    });

    const payload = {
        to: toTagify.value.map(v => v.value),
        subject: $('#subject').val().trim(),
        html: $('#editor').summernote('code')
    };


    function() {

        toastr.options = {
            positionClass: 'toast-top-right',
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            timeOut: 2500
        };

        const EMAIL_RE = /^[\w.!#$%&'*+\/=?^`{|}~-]+@[\w-]+(\.[\w-]+)+$/i;
            const state = {
                to: [],
                cc: [],
                bcc: []
            };

            function splitEmails(raw) {
                return (raw || '').split(/[,;]+/).map(v => v.trim()).filter(Boolean);
            }

            function chipStyle() {
                return `
                                                            display:inline-flex;
                                                            align-items:center;
                                                            gap:6px;
                                                            background:#eef2ff;
                                                            border:1px solid #c7d2fe;
                                                            color:#3730a3;
                                                            border-radius:999px;
                                                            padding:3px 10px;
                                                            margin:3px 6px 0 0;
                                                            font-size:12px;
                                                            `;
            }

            function addChip(type, raw) {
                const list = state[type];
                const target = type === 'to' ? '#toChips' : type === 'cc' ? '#ccChips' : '#bccChips';

                splitEmails(raw).forEach(v => {
                    if (!EMAIL_RE.test(v)) {
                        toastr.warning(`Invalid email skipped: ${v}`);
                        return;
                    }
                    if (list.includes(v)) return;

                    list.push(v);
                    $(target).append(`
                                                                        <span style="${chipStyle()}" data-type="${type}" data-email="${v}">
                                                                            ${v}<span style="cursor:pointer;margin-left:6px;">×</span>
                                                                        </span>
                                                                    `);
                });
            }

            $(document).on('click', 'span[data-type] span', function() {
                const $p = $(this).parent();
                const t = $p.data('type');
                const e = $p.data('email');
                state[t] = state[t].filter(x => x !== e);
                $p.remove();
            });

            ['to', 'cc', 'bcc'].forEach(type => {
                $('#' + type + 'Input').on('keydown', e => {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        addChip(type, e.target.value);
                        e.target.value = '';
                    }
                });
            });

            $(function() {
                const init = $('#toInput').val();
                if (init) {
                    addChip('to', init);
                    $('#toInput').val('');
                }
            });

            $('#editor').summernote({
                placeholder: 'Tulis isi email di sini…',
                height: '100%',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['codeview', 'help']]
                ]
            });

            $('#editor').summernote('code', `{!! $initial_html !!}`);

            // --- Send ---
            $('#btnSend').on('click', async function() {
                // sweep leftovers (user typed but didn’t press Enter)
                if ($('#toInput').value?.trim) {
                    const v = $('#toInput').val().trim();
                    if (v) {
                        addChip('to', v);
                        $('#toInput').val('');
                    }
                }
                if ($('#ccInput').value?.trim) {
                    const v = $('#ccInput').val().trim();
                    if (v) {
                        addChip('cc', v);
                        $('#ccInput').val('');
                    }
                }
                if ($('#bccInput').value?.trim) {
                    const v = $('#bccInput').val().trim();
                    if (v) {
                        addChip('bcc', v);
                        $('#bccInput').val('');
                    }
                }

                const payload = {
                    ponbr: $('#ponbr').val(),
                    cpny_id: $('#cpny_id').val(),
                    from: $('#from').val(),
                    to: state.to,
                    cc: state.cc,
                    bcc: state.bcc,
                    subject: $('#subject').val().trim(),
                    html: $('#editor').summernote('code')
                };

                if (!payload.to.length) {
                    toastr.error('Field "To" wajib diisi');
                    return;
                }
                if (!payload.subject) {
                    toastr.error('Subject wajib diisi');
                    return;
                }


                try {

                    const ponbr = encodeURIComponent($('#orderNbr').val());
                    const cpny = encodeURIComponent($('#cpny_id').val() || '');
                    const res = await fetch(`/po/${ponbr}/email/send?cpny_id=${cpny}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // ask server for JSON even on errors
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const text = await res.text(); // always read as text first
                    let json;
                    try {
                        json = JSON.parse(text);
                    } // try JSON parse
                    catch (e) {
                        console.error('Non-JSON response:', text);
                        toastr.error('Server mengembalikan non-JSON. Cek console.');
                        return;
                    }

                    if (res.ok && json.success) {
                        toastr.success('Email terkirim.');
                        const encodedId = @json($eid);
                        window.location.href = `/showpo/${encodedId}`;
                    } else {
                        toastr.error(json.message || 'Gagal mengirim email.');
                    }
                } catch (err) {
                    console.error(err);
                    toastr.error('Terjadi kesalahan jaringan.');
                }
            });


        })();
    </script> --}}

    <script>
        (function() {

            // ===== TOASTR =====
            toastr.options = {
                positionClass: 'toast-top-right',
                closeButton: true,
                newestOnTop: true,
                progressBar: true,
                timeOut: 2500
            };

            // // ===== TAGIFY EMAIL REGEX =====
            // const EMAIL_RE = /^[\w.!#$%&'*+\/=?^`{|}~-]+@[\w-]+(\.[\w-]+)+$/;

        // ===== TAGIFY INIT =====
        const EMAIL_RE = /^[\w.!#$%&'*+\/=?^`{|}~-]+@[\w-]+(\.[\w-]+)+$/;

            const toTagify = new Tagify(document.querySelector('#toInput'), {
                delimiters: ",| |;",
                pattern: EMAIL_RE,
                addTagOnBlur: true,
                transformTag(tag) {
                    tag.value = tag.value.trim();
                }
            });

            const ccTagify = new Tagify(document.querySelector('#ccInput'), {
                delimiters: ",| |;",
                pattern: EMAIL_RE,
                addTagOnBlur: true,
                transformTag(tag) {
                    tag.value = tag.value.trim();
                }
            });

            const bccTagify = new Tagify(document.querySelector('#bccInput'), {
                delimiters: ",| |;",
                pattern: EMAIL_RE,
                addTagOnBlur: true,
                transformTag(tag) {
                    tag.value = tag.value.trim();
                }
            });


            // ===== SUMMERNOTE =====
            $('#editor').summernote({
                placeholder: 'Tulis isi email di sini…',
                height: '100%',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['codeview', 'help']]
                ]
            });

            $('#editor').summernote('code', `{!! $initial_html !!}`);

            function setSending(isSending) {
                const overlay = document.getElementById('sendOverlay');
                const btn = document.getElementById('btnSend');

                if (isSending) {
                    overlay.style.display = 'flex';
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                    btn.dataset.oldText = btn.innerHTML;
                    btn.innerHTML = 'Sending...';
                } else {
                    overlay.style.display = 'none';
                    btn.disabled = false;
                    btn.style.opacity = '';
                    btn.style.cursor = '';
                    if (btn.dataset.oldText) btn.innerHTML = btn.dataset.oldText;
                }
            }


            // ===== SEND EMAIL =====
            // $('#btnSend').on('click', async function() {

            //     const payload = {
            //         ponbr: $('#ponbr').val(),
            //         cpny_id: $('#cpny_id').val(),
            //         from: $('#from').val(),
            //         to: toTagify.value.map(v => v.value),
            //         cc: ccTagify.value.map(v => v.value),
            //         bcc: bccTagify.value.map(v => v.value),
            //         subject: $('#subject').val().trim(),
            //         html: $('#editor').summernote('code')
            //     };

            //     if (!payload.to.length) {
            //         toastr.error('Field "To" wajib diisi');
            //         return;
            //     }

            //     if (!payload.subject) {
            //         toastr.error('Subject wajib diisi');
            //         return;
            //     }

            //     try {
            //         const ponbr = encodeURIComponent($('#orderNbr').val());
            //         const cpny = encodeURIComponent(payload.cpny_id || '');

            //         const res = await fetch(`/po/${ponbr}/email/send?cpny_id=${cpny}`, {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'Accept': 'application/json',
            //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //             },
            //             body: JSON.stringify(payload)
            //         });

            //         const json = await res.json();

            //         if (res.ok && json.success) {
            //             toastr.success('Email terkirim.');
            //             setTimeout(() => {
            //                 window.location.href = `/showpo/${@json($eid)}`;
            //             }, 800);
            //         } else {
            //             toastr.error(json.message || 'Gagal mengirim email.');
            //         }


            //     } catch (err) {
            //         console.error(err);
            //         toastr.error('Terjadi kesalahan jaringan.');
            //     }
            // });
            $('#btnSend').on('click', async function() {

                const payload = {
                    ponbr: $('#ponbr').val(),
                    cpny_id: $('#cpny_id').val(),
                    from: $('#from').val(),
                    to: toTagify.value.map(v => v.value),
                    cc: ccTagify.value.map(v => v.value),
                    bcc: bccTagify.value.map(v => v.value),
                    subject: $('#subject').val().trim(),
                    html: $('#editor').summernote('code')
                };

                if (!payload.to.length) {
                    toastr.error('Field "To" wajib diisi');
                    return;
                }
                if (!payload.subject) {
                    toastr.error('Subject wajib diisi');
                    return;
                }

                setSending(true); // ✅ SHOW overlay

                try {
                    const ponbr = encodeURIComponent($('#orderNbr').val());
                    const cpny = encodeURIComponent(payload.cpny_id || '');

                    const res = await fetch(`/po/${ponbr}/email/send?cpny_id=${cpny}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const json = await res.json();

                    if (res.ok && json.success) {
                        toastr.success('Email terkirim.');
                        setTimeout(() => {
                            window.location.href = `/showpo/${@json($eid)}`;
                        }, 800);
                    } else {
                        toastr.error(json.message || 'Gagal mengirim email.');
                    }

                } catch (err) {
                    console.error(err);
                    toastr.error('Terjadi kesalahan jaringan.');
                } finally {
                    setSending(false); // ✅ HIDE overlay (kalau sukses, redirect akan jalan, tapi aman)
                }
            });


        })();
    </script>

</body>

</html>
