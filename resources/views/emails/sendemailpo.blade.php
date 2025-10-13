<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Send Email – {{ $ponbr }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


  <style>
    body{background:#f5f7fb}
    .compose-card{max-width:960px;margin:24px auto;border-radius:14px;box-shadow:0 10px 30px rgba(17,24,39,.08)}
    .label{font-size:.8rem;color:#6b7280}
    .meta-grid{display:grid;grid-template-columns:110px 1fr 110px 1fr;gap:10px 12px;margin-bottom:12px}
    .chip{display:inline-flex;align-items:center;gap:.35rem;background:#eef2ff;border:1px solid #c7d2fe;color:#3730a3;border-radius:999px;padding:.2rem .6rem;margin:.15rem .25rem 0 0}
    .chip .x{cursor:pointer;margin-left:.35rem}
    .toolbar-small .note-editable{min-height:260px}
  </style>
</head>
<body>
<div class="card compose-card">
  <div class="card-body p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">           
      <button id="btnSend" class="btn btn-primary btn-send d-inline-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        Send
      </button>
    </div>

    {{-- Meta Info --}}
    <div class="meta-grid">
      <div class="label">Order Nbr</div>
      <div><input id="orderNbr" type="text" class="form-control" value="{{ $ponbr }}" readonly></div>      
      <div class="label">Template</div>
      <div><input id="templateType" type="text" class="form-control" value="{{ $template }}" readonly></div>

      <div class="label">Vendor</div>
      <div><input id="vendorName" type="text" class="form-control" value="{{ $vendor }}" readonly></div>
    </div>

    {{-- Header --}}
    <div class="row g-3">
      <div class="col-md-6">
        <label class="label mb-1">From</label>
        <input id="from" type="text" class="form-control" value="{{ $from_email }}" readonly>        
      </div>
      <div class="col-md-6"></div>

      <div class="col-12">
        <label class="label mb-1">To</label>
        <input id="toInput" type="text" class="form-control" value="{{ $to_email }}" placeholder="email1@domain.com, email2@domain.com">
        <div id="toChips" class="mt-1"></div>
      </div>

      <div class="col-12">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="label mb-1">Cc</label>
            <input id="ccInput" type="text" class="form-control" placeholder="(optional)">
            <div id="ccChips" class="mt-1"></div>
          </div>
          <div class="col-md-6">
            <label class="label mb-1">Bcc</label>
            <input id="bccInput" type="text" class="form-control" placeholder="(optional)">
            <div id="bccChips" class="mt-1"></div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <label class="label mb-1">Subject</label>
        <input id="subject" type="text" class="form-control" value="{{ $subject_email }}">
      </div>
    </div>

    <div class="mt-3 toolbar-small">
      <div id="editor"></div>
    </div>

    <small class="text-muted d-block mt-2" id="autosaveMsg"></small>

    <input type="hidden" id="ponbr" value="{{ $ponbr }}">
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function(){
 

toastr.options = {
  positionClass: 'toast-top-right',
  closeButton: true,
  newestOnTop: true,
  progressBar: true,
  timeOut: 2500
};


  // --- Email chips state & helpers ---
  const EMAIL_RE=/^[\w.!#$%&'*+\/=?^`{|}~-]+@[\w-]+(\.[\w-]+)+$/i;
  const state={to:[],cc:[],bcc:[]};

  function splitEmails(raw){
    return (raw||'')
      .split(/[,;]+/)   // support comma & semicolon
      .map(v=>v.trim())
      .filter(Boolean);
  }

  function addChip(type, raw){
    const list = state[type];
    const target = (type==='to') ? '#toChips' : (type==='cc' ? '#ccChips' : '#bccChips');
    splitEmails(raw).forEach(v=>{
      if (!EMAIL_RE.test(v)) { toastr.warning(`Invalid email skipped: ${v}`); return; }
      if(list.includes(v)) return;
      list.push(v);
      const $chip=$(`<span class="chip" data-type="${type}" data-email="${v}">${v}<span class="x">×</span></span>`);
      $(target).append($chip);
    });
  }

  // remove chip
  $(document).on('click','.chip .x',function(){
    const $p=$(this).closest('.chip'); const t=$p.data('type'); const e=$p.data('email');
    state[t]=state[t].filter(x=>x!==e); $p.remove(); // (optional) saveDraft();
  });

  // convert typed text to chips on Enter or comma
  $('#toInput').on('keydown',e=>{ if(e.key==='Enter'||e.key===','){ e.preventDefault(); addChip('to', e.target.value); e.target.value=''; }});
  $('#ccInput').on('keydown',e=>{ if(e.key==='Enter'||e.key===','){ e.preventDefault(); addChip('cc', e.target.value); e.target.value=''; }});
  $('#bccInput').on('keydown',e=>{ if(e.key==='Enter'||e.key===','){ e.preventDefault(); addChip('bcc', e.target.value); e.target.value=''; }});

  // hydrate "To" from controller value
  $(function(){
    const initialTo = $('#toInput').val();
    if(initialTo){ addChip('to', initialTo); $('#toInput').val(''); }
  });

  // --- Summernote ---
  $('#editor').summernote({
    placeholder:'Tulis isi email di sini…',
    height:260,
    toolbar:[
      ['style',['style']],
      ['font',['bold','underline','italic','clear']],
      ['fontname',['fontname']],
      ['color',['color']],
      ['para',['ul','ol','paragraph']],
      ['table',['table']],
      ['insert',['link','picture','video']],
      ['view',['codeview','help']]
    ]
  });

  // Inject initial HTML safely
  $('#editor').summernote('code', `{!! $initial_html !!}`);

  // --- Send ---
  $('#btnSend').on('click', async function(){
    // sweep leftovers (user typed but didn’t press Enter)
    if($('#toInput').value?.trim){ const v=$('#toInput').val().trim(); if(v){ addChip('to', v); $('#toInput').val(''); } }
    if($('#ccInput').value?.trim){ const v=$('#ccInput').val().trim(); if(v){ addChip('cc', v); $('#ccInput').val(''); } }
    if($('#bccInput').value?.trim){ const v=$('#bccInput').val().trim(); if(v){ addChip('bcc', v); $('#bccInput').val(''); } }

    const payload = {
      from:   $('#from').val(),
      to:     state.to,
      cc:     state.cc,
      bcc:    state.bcc,
      subject: $('#subject').val().trim(),
      html:   $('#editor').summernote('code')
    };

    if (!payload.to.length) { toastr.error('Field "To" wajib diisi'); return; }
    if (!payload.subject)   { toastr.error('Subject wajib diisi'); return; }


    try{
      const res = await fetch(`/po/${encodeURIComponent($('#orderNbr').val())}/email/send`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',              // ask server for JSON even on errors
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(payload)
      });

      const text = await res.text();                 // always read as text first
      let json;
      try { json = JSON.parse(text); }               // try JSON parse
      catch (e) {
        console.error('Non-JSON response:', text);
        toastr.error('Server mengembalikan non-JSON. Cek console.');
        return;
      }

      if(res.ok && json.success){
        toastr.success('Email terkirim.');
      }else{
        toastr.error(json.message || 'Gagal mengirim email.');
      }
    }catch(err){
      console.error(err);
      toastr.error('Terjadi kesalahan jaringan.');
    }
  });
})();
</script>
</body>
</html>
