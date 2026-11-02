(() => {
    const root = document.getElementById('vms-vendors-page');
    if (!root) return;
    const base = root.dataset.base, tables = {}, businesses = JSON.parse(root.dataset.businesses);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const text = value => esc(value == null || value === '' ? '-' : value);
    const cell = data => ({data, defaultContent:'', render:text});
    const link = (row,tab) => `${base}/${Number(row.vendor_id)}?tab=${tab}`;
    function name(tab) { return {data:'vendor_nama',render:(v,t,row) => t === 'display' ? `<a class="vms-vendor-link" href="${link(row,tab)}">${esc(v)}</a>` : v}; }
    function profile() {return {data:'profile_url',orderable:false,searchable:false,render:(url,type) => type === 'display' && url ? `<a class="vms-vendor-link" href="${esc(url)}" target="_blank" rel="noopener noreferrer" title="Lihat Company Profile"><i class="fas fa-file-pdf" aria-hidden="true"></i> PDF</a>` : '-'};}
    function approval(role,fields) {
        return {data:role+'_approval',render:(value,type,row) => {
            const confirmed = value === 'yes', label = confirmed ? 'Confirm' : 'Not Confirm';
            const counts = fields.map(([field,label]) => label+': '+(row[field] ?? '-')).join(' | ');
            return type === 'display' ? `<div class="vms-vendor-approval"><span class="vms-document-badge ${confirmed ? '' : 'optional'}">${label}</span><small>${esc(counts)}</small></div>` : label+'; '+counts;
        }};
    }
    function columns(tab) {
        let result;
        if(tab === 'new') result = [{data:'vendor_nama',render:(value,type,row) => type === 'display' ? `<div class="vms-vendor-company"><span>${esc(value)}</span>${row.profile_url ? `<a class="vms-vendor-pdf" href="${esc(row.profile_url)}" target="_blank" rel="noopener noreferrer" title="Company Profile PDF" aria-label="Company profile ${esc(value)}"><i class="fas fa-file-pdf" aria-hidden="true"></i></a>` : ''}</div>` : value},cell('username'),cell('email'),cell('created_at')];
        if(tab === 'incomplete') result = [cell('vendor_nama'),{data:'step',render:(value,type) => {
            const labels={1:'Company Information',2:'NPWP & Bank Section',3:'Upload Document',4:'Integrity Clause',5:'Finish'};
            const step = Number(value),label=labels[step] || 'Belum diisi';
            return type === 'display' ? `<div class="vms-vendor-progress"><span class="vms-vendor-step-label">${esc(label)}</span><progress max="5" value="${Math.min(5,Math.max(0,step))}" aria-label="${esc(label)}"></progress></div>` : label;
        }},cell('username'),cell('email'),profile(),cell('created_at'),cell('parent_category_name'),cell('category_name')];
        if(tab === 'review') result = [cell('vendor_nama'),cell('username'),cell('email'),cell('created_at'),cell('parent_category_name'),cell('category_name'),approval('procurement',[['proc_pending','W']]),approval('legal',[['legal_pending','W']]),approval('tax',[['tax_rejected','X'],['tax_waitingreview','W'],['tax_request','R']]),approval('finance',[['treasury_rejected','X'],['treasury_waitingreview','W'],['treasury_request','R']])];
        if(tab === 'master') result = [cell('second_vendor_id'),cell('vendor_nama'),cell('parent_category_name'),cell('category_name'),cell('type'),cell('email'),cell('phone1'),profile()];
        return [...result,{data:null,orderable:false,searchable:false,render:(_,type,row) => type !== 'display' ? '' : tab === 'new' ? `<button type="button" class="vms-vendor-link" data-registration-review="${Number(row.vendor_id)}">Review <i class="fas fa-search" aria-hidden="true"></i></button>` : tab === 'incomplete' ? `<button type="button" class="vms-vendor-link" data-incomplete-review="${Number(row.vendor_id)}">Review <i class="fas fa-search" aria-hidden="true"></i></button>` : `<button type="button" class="vms-vendor-link" data-profile-review="${Number(row.vendor_id)}" data-mode="${tab}">Review <i class="fas fa-search" aria-hidden="true"></i></button>`}];
    }
    function exportText(value) {
        const node = document.createElement('div');node.innerHTML = value;
        const text = node.textContent || '';
        return /^[\s\uFEFF]*[=+@-]/.test(text) ? "'"+text : text;
    }
    function init(tab) {
        if(tables[tab]) {tables[tab].columns.adjust();return;}
        const cols = columns(tab), exportOptions={columns:cols.map((_,i)=>i).slice(0,-1),format:{body:exportText}};
        tables[tab] = jQuery(`#vendor-table-${tab}`).DataTable({
            ajax:(params,done)=>{
                root.querySelectorAll(`[data-vendor-filters="${tab}"] select`).forEach(input=>{params[input.name]=input.value;});
                const errorBox=root.querySelector(`[data-vendor-load-error="${tab}"]`);
                errorBox.hidden=true;
                return jQuery.ajax({url:`${base}/tab/${tab}/json`,data:params,dataType:'json',timeout:30000,
                    success:result=>{errorBox.hidden=true;done(result);},
                    error:(xhr,status)=>{
                        // Complete DataTables' request even on failure; otherwise it stays on Loading.
                        done({data:[]});
                        if(status==='abort')return;
                        errorBox.textContent=[401,419].includes(xhr.status)?'Sesi login berakhir. Silakan login kembali.':status==='timeout'?'Permintaan terlalu lama. Klik Refresh untuk mencoba kembali.':'Data belum dapat dimuat. Klik Refresh untuk mencoba kembali.';
                        errorBox.hidden=false;
                    }
                });
            },
            processing:true,serverSide:false,scrollX:true,autoWidth:false,pageLength:10,lengthMenu:[10,25,50,100],order:[[tab==='master'?1:0,'asc']],columns:cols,
            dom:'<"dt-toolbar"lBf>rtip',buttons:[{extend:'excelHtml5',text:'Excel',title:'List Vendor - '+tab,exportOptions},{extend:'csvHtml5',text:'CSV',title:'List Vendor - '+tab,exportOptions}]
        });
    }
    function tab(value,historyMode='replace') {
        root.querySelectorAll('[data-vendor-tab]').forEach(button => {const active=button.dataset.vendorTab===value;button.setAttribute('aria-selected',String(active));button.tabIndex=active?0:-1;});
        root.querySelectorAll('[data-vendor-panel]').forEach(panel => {panel.hidden = panel.dataset.vendorPanel !== value;});
        init(value);const url = new URL(location.href);url.searchParams.set('tab',value);history[historyMode+'State'](history.state,'',url);
    }
    root.querySelectorAll('[data-vendor-tab]').forEach(button => {
        button.onclick = () => tab(button.dataset.vendorTab,'push');
        button.onkeydown = event => {if(!['ArrowLeft','ArrowRight','Home','End'].includes(event.key))return;event.preventDefault();const buttons=[...root.querySelectorAll('[data-vendor-tab]')],i=buttons.indexOf(button);const next=event.key==='Home'?0:event.key==='End'?buttons.length-1:(i+(event.key==='ArrowRight'?1:-1)+buttons.length)%buttons.length;buttons[next].focus();tab(buttons[next].dataset.vendorTab,'push');};
    });
    root.querySelectorAll('[data-vendor-filters]').forEach(filters => {
        filters.addEventListener('change',event => {
            if(event.target.name === 'category') {
                const select=filters.querySelector('[name=business]');select.replaceChildren(new Option('Semua bidang usaha',''));
                businesses.filter(item => !event.target.value || String(item.parent_category) === event.target.value).forEach(item => select.add(new Option(item.category_name,item.category_id)));
            }
            tables[filters.dataset.vendorFilters]?.ajax.reload();
        });
    });
    root.querySelectorAll('[data-vendor-reset]').forEach(button=>button.onclick=()=>{const filters=button.closest('[data-vendor-filters]');filters.querySelectorAll('select').forEach(input=>{input.value='';});filters.querySelector('[name=category]').dispatchEvent(new Event('change',{bubbles:true}));});
    root.querySelectorAll('[data-vendor-refresh]').forEach(button=>button.onclick=()=>tables[button.dataset.vendorRefresh]?.ajax.reload(null,false));
    window.addEventListener('popstate',()=>{const value=new URL(location.href).searchParams.get('tab');tab(['new','incomplete','review','master'].includes(value)?value:'new');});
    const dialog=document.getElementById('vms-document-modal'),form=document.getElementById('vms-registration-form');
    const feedback=form.querySelector('[data-registration-error]'),approve=form.querySelector('[data-registration-approve]'),reject=form.querySelector('[data-registration-reject]');
    let registration=null,reviewLoading=false,reviewSaving=false,reviewBusinesses=[];
    async function registrationRequest(url,method='GET',data) {
        const response=await fetch(url,{method,credentials:'same-origin',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.elements._token.value},...(data?{body:JSON.stringify(data)}:{})});
        const result=await response.json().catch(()=>({}));
        if(!response.ok) throw Error(Object.values(result.errors||{}).flat().join('\n')||result.message||'Permintaan gagal. Silakan coba kembali.');
        return result;
    }
    function reviewBusiness(selected='') {
        const select=form.elements.bidang_usaha;select.replaceChildren(new Option('-- Pilih Bidang Usaha --',''));
        reviewBusinesses.filter(item=>String(item.parent_category)===form.elements.vendor_category.value).forEach(item=>select.add(new Option(item.category_name,item.category_id)));
        select.value=selected;
    }
    form.elements.vendor_category.addEventListener('change',()=>reviewBusiness());
    root.addEventListener('click',async event=>{
        const button=event.target.closest('[data-registration-review]');if(!button||reviewLoading||reviewSaving)return;
        reviewLoading=true;button.disabled=true;
        try {
            const data=await registrationRequest(`${base}/${button.dataset.registrationReview}/registration`);
            registration=data;reviewBusinesses=data.businesses;form.reset();feedback.hidden=true;
            ['username','email','npwp'].forEach(name=>{form.elements[name].value=data[name]||'';});
            form.elements.vendor_name.value=data.vendor_nama;form.elements.tags.value=(data.tags||[]).join(', ');
            const select=form.elements.vendor_category;select.replaceChildren(new Option('-- Pilih Category --',''));
            data.categories.forEach(item=>select.add(new Option(item.parent_category_name,item.id)));
            select.value=String(data.vendor_category);reviewBusiness(String(data.bidang_usaha));
            const pdf=form.querySelector('[data-registration-pdf]'),preview=form.querySelector('[data-registration-preview]');
            pdf.hidden=!data.profile_url;preview.hidden=!data.profile_url;form.querySelector('[data-registration-no-pdf]').hidden=!!data.profile_url;
            if(data.profile_url){pdf.href=data.profile_url;preview.src=data.profile_url;}else{pdf.removeAttribute('href');preview.removeAttribute('src');}
            approve.hidden=!data.can_decide;reject.hidden=!data.can_decide;form.querySelector('[data-registration-readonly]').hidden=data.can_decide;
            ['vendor_name','vendor_category','bidang_usaha','tags','notes'].forEach(name=>{form.elements[name].disabled=!data.can_decide;});
            dialog.showModal();
        } catch(error) {Swal.fire({icon:'error',title:'Review belum dapat dibuka',text:error.message,confirmButtonColor:'#2563eb'});}
        finally {reviewLoading=false;button.disabled=false;}
    });
    form.querySelectorAll('[data-registration-close]').forEach(button=>button.onclick=()=>{if(!reviewSaving)dialog.close();});
    dialog.addEventListener('cancel',event=>{if(reviewSaving)event.preventDefault();});
    dialog.addEventListener('close',()=>form.querySelector('[data-registration-preview]').removeAttribute('src'));
    async function decide(decision) {
        if(reviewSaving||!registration?.can_decide)return;
        if(decision==='approve'&&!form.reportValidity())return;
        reviewSaving=true;approve.disabled=true;reject.disabled=true;feedback.hidden=true;
        try {
            const confirmation=await Swal.fire({target:dialog,icon:'question',title:decision==='approve'?'Setujui registrasi vendor?':'Tolak registrasi vendor?',text:registration.vendor_nama,showCancelButton:true,confirmButtonText:decision==='approve'?'Ya, Approve':'Ya, Reject',cancelButtonText:'Batal',confirmButtonColor:decision==='approve'?'#2563eb':'#dc4e4e',focusCancel:true});
            if(!confirmation.isConfirmed)return;
            const payload={decision,review_token:registration.review_token,notes:form.elements.notes.value};
            if(decision==='approve') Object.assign(payload,{vendor_name:form.elements.vendor_name.value,vendor_category:form.elements.vendor_category.value,bidang_usaha:form.elements.bidang_usaha.value,tags:[...new Set(form.elements.tags.value.split(',').map(value=>value.trim()).filter(Boolean))]});
            const result=await registrationRequest(`${base}/${registration.vendor_id}/registration`,'POST',payload);
            dialog.close();registration=null;Object.values(tables).forEach(table=>table.ajax.reload(null,false));
            Object.entries(result.counts).forEach(([tab,count])=>{root.querySelector(`[data-vendor-count="${tab}"]`).textContent=Number(count).toLocaleString('en-US');});
            await Swal.fire({icon:'success',title:'Success',text:result.message,confirmButtonColor:'#2563eb'});
        } catch(error) {feedback.textContent=error.message;feedback.hidden=false;}
        finally {reviewSaving=false;approve.disabled=false;reject.disabled=false;}
    }
    form.addEventListener('submit',event=>{event.preventDefault();decide('approve');});
    reject.onclick=()=>decide('reject');
    const incompleteDialog=document.getElementById('vms-incomplete-modal'),incompleteContent=incompleteDialog.querySelector('[data-incomplete-content]');
    let incompleteAbort=null;
    root.addEventListener('click',async event=>{
        const button=event.target.closest('[data-incomplete-review]');if(!button||incompleteDialog.open)return;
        incompleteContent.textContent='Memuat informasi vendor...';incompleteDialog.showModal();
        const controller=new AbortController();incompleteAbort=controller;
        try{
            const response=await fetch(`${base}/${button.dataset.incompleteReview}/incomplete`,{credentials:'same-origin',headers:{Accept:'application/json'},signal:controller.signal});
            const data=await response.json().catch(()=>({}));
            if(!response.ok)throw Error(data.message||'Data vendor belum dapat dimuat. Periksa sesi login dan akses menu.');
            if(!controller.signal.aborted)incompleteContent.innerHTML=data.html;
        }catch(error){if(error.name!=='AbortError'){incompleteContent.textContent=error.message;}}
    });
    incompleteDialog.querySelectorAll('[data-incomplete-close]').forEach(button=>button.onclick=()=>incompleteDialog.close());
    incompleteDialog.addEventListener('close',()=>{incompleteAbort?.abort();incompleteContent.replaceChildren();});
    incompleteContent.addEventListener('click',event=>{
        const button=event.target.closest('[data-incomplete-tab]');if(!button)return;
        incompleteContent.querySelectorAll('[data-incomplete-tab]').forEach(item=>item.setAttribute('aria-selected',String(item===button)));
        incompleteContent.querySelectorAll('[data-incomplete-panel]').forEach(panel=>{panel.hidden=panel.dataset.incompletePanel!==button.dataset.incompleteTab;});
    });
    const profileDialog=document.getElementById('vms-profile-modal'),profileContent=profileDialog.querySelector('[data-profile-content]');
    let profileContext=null,profileSaving=false,profileAbort=null;
    function profileTab(value) {
        profileContent.querySelectorAll('[data-profile-tab]').forEach(button=>button.setAttribute('aria-selected',String(button.dataset.profileTab===value)));
        profileContent.querySelectorAll('[data-profile-panel]').forEach(panel=>{panel.hidden=panel.dataset.profilePanel!==value;});
    }
    async function loadProfile(id,mode,active='basic') {
        profileAbort?.abort();const controller=new AbortController();profileAbort=controller;
        profileContent.textContent='Memuat profil vendor...';profileContext={id,mode};
        document.getElementById('vms-profile-title').textContent=mode==='master'?'Master Vendor':'Vendor Review';
        const response=await fetch(`${base}/${id}/profile/${mode}`,{credentials:'same-origin',headers:{Accept:'application/json'},signal:controller.signal});
        const data=await response.json().catch(()=>({}));
        if(!response.ok)throw Error(data.message||'Profil belum dapat dimuat. Coba buka kembali Review.');
        if(!controller.signal.aborted){profileContent.innerHTML=data.html;profileTab(active);}
    }
    root.addEventListener('click',async event=>{
        const button=event.target.closest('[data-profile-review]');if(!button||profileDialog.open)return;
        profileDialog.showModal();
        try{await loadProfile(button.dataset.profileReview,button.dataset.mode);}catch(error){if(error.name!=='AbortError')profileContent.textContent=error.message;}
    });
    profileDialog.querySelectorAll('[data-profile-close]').forEach(button=>button.onclick=()=>{if(!profileSaving)profileDialog.close();});
    profileDialog.addEventListener('cancel',event=>{if(profileSaving)event.preventDefault();});
    profileDialog.addEventListener('close',()=>{profileAbort?.abort();profileContext=null;profileContent.replaceChildren();});
    function updateCounts(counts) {Object.entries(counts).forEach(([key,value])=>{root.querySelector(`[data-vendor-count="${key}"]`).textContent=Number(value).toLocaleString('en-US');});Object.values(tables).forEach(table=>table.ajax.reload(null,false));}
    profileContent.addEventListener('click',async event=>{
        const tabButton=event.target.closest('[data-profile-tab]');if(tabButton){profileTab(tabButton.dataset.profileTab);return;}
        const button=event.target.closest('[data-profile-decision]');if(!button||profileSaving||!profileContext)return;
        const action=button.closest('[data-profile-action]'),decision=button.dataset.profileDecision;
        const active=profileContent.querySelector('[data-profile-tab][aria-selected=true]')?.dataset.profileTab||'basic';
        profileSaving=true;
        try{
            const answer=await Swal.fire({target:profileDialog,icon:'question',title:decision==='approve'?'Approve hasil review?':decision==='revise'?'Kembalikan untuk revisi?':'Reject hasil review?',input:'textarea',inputLabel:decision==='approve'?'Catatan (opsional)':'Catatan untuk vendor (wajib)',inputAttributes:{maxlength:5000},showCancelButton:true,confirmButtonText:decision==='approve'?'Approve':decision==='revise'?'Revise':'Reject',cancelButtonText:'Batal',confirmButtonColor:decision==='approve'?'#4e70b4':'#dc5b5b',focusCancel:true,inputValidator:value=>decision!=='approve'&&!value.trim()?'Isi catatan terlebih dahulu.':undefined});
            if(!answer.isConfirmed)return;
            const payload={kind:action.dataset.kind,decision,token:action.dataset.token,notes:answer.value||'',department:action.dataset.department};if(action.dataset.record)payload.record_id=Number(action.dataset.record);
            const result=await registrationRequest(`${base}/${profileContext.id}/profile/review`,'POST',payload);
            updateCounts(result.counts);
            const id=profileContext.id;
            if(result.mode)await loadProfile(id,result.mode,active);else profileDialog.close();
            await Swal.fire({...(profileDialog.open?{target:profileDialog}:{}),icon:'success',title:'Success',text:result.message,confirmButtonColor:'#4e70b4'});
        }catch(error){await Swal.fire({target:profileDialog,icon:'error',title:'Review belum disimpan',text:error.message,confirmButtonColor:'#4e70b4'});}
        finally{profileSaving=false;}
    });
    profileContent.addEventListener('submit',async event=>{
        const chat=event.target.closest('[data-profile-chat]'),password=event.target.closest('[data-profile-password]');if(!chat&&!password)return;event.preventDefault();if(profileSaving||!profileContext)return;
        const form=chat||password;if(!form.reportValidity())return;profileSaving=true;const submit=form.querySelector('[type=submit]');submit.disabled=true;
        try{
            const payload=Object.fromEntries(new FormData(form));delete payload._token;if(password)payload.user_id=Number(password.dataset.user);
            const result=await registrationRequest(`${base}/${profileContext.id}/profile/${profileContext.mode}/${chat?'chat':'password'}`,chat?'POST':'PUT',payload);
            form.reset();await loadProfile(profileContext.id,profileContext.mode,chat?'chat':'setting');
            await Swal.fire({target:profileDialog,icon:'success',title:'Success',text:result.message,confirmButtonColor:'#4e70b4'});
        }catch(error){await Swal.fire({target:profileDialog,icon:'error',title:'Data belum disimpan',text:error.message,confirmButtonColor:'#4e70b4'});}
        finally{profileSaving=false;submit.disabled=false;}
    });
    const noteTooltip=document.createElement('div');noteTooltip.id='vms-note-tooltip';noteTooltip.className='vms-note-tooltip';noteTooltip.setAttribute('role','tooltip');noteTooltip.tabIndex=0;noteTooltip.hidden=true;profileDialog.appendChild(noteTooltip);
    let noteOwner=null,noteHideTimer=null;
    function hideNote(){clearTimeout(noteHideTimer);noteOwner?.removeAttribute('aria-describedby');noteOwner=null;noteTooltip.hidden=true;}
    function showNote(button){
        clearTimeout(noteHideTimer);noteOwner?.removeAttribute('aria-describedby');noteOwner=button;
        noteTooltip.textContent=button.dataset.noteHint;noteTooltip.hidden=false;button.setAttribute('aria-describedby',noteTooltip.id);
        const anchor=button.getBoundingClientRect(),tip=noteTooltip.getBoundingClientRect();
        const left=Math.max(16,Math.min(innerWidth-tip.width-16,anchor.left+anchor.width/2-tip.width/2));
        const below=anchor.bottom+8,top=below+tip.height<=innerHeight-16?below:Math.max(16,anchor.top-tip.height-8);
        noteTooltip.style.left=left+'px';noteTooltip.style.top=top+'px';
    }
    profileDialog.addEventListener('mouseover',event=>{const button=event.target.closest('[data-note-hint]');if(button)showNote(button);else if(noteTooltip.contains(event.target))clearTimeout(noteHideTimer);});
    profileDialog.addEventListener('mouseout',event=>{if(event.target.closest('[data-note-hint]')||noteTooltip.contains(event.target))noteHideTimer=setTimeout(hideNote,180);});
    profileDialog.addEventListener('focusin',event=>{const button=event.target.closest('[data-note-hint]');if(button)showNote(button);else if(!noteTooltip.contains(event.target))hideNote();});
    profileDialog.addEventListener('focusout',event=>{if(!noteTooltip.contains(event.relatedTarget)&&!event.relatedTarget?.closest?.('[data-note-hint]'))noteHideTimer=setTimeout(hideNote,180);});
    profileDialog.addEventListener('click',event=>{const button=event.target.closest('[data-note-hint]');if(button)showNote(button);else if(!noteTooltip.contains(event.target))hideNote();});
    profileDialog.addEventListener('keydown',event=>{if(event.key==='Escape'&&!noteTooltip.hidden){event.preventDefault();event.stopPropagation();hideNote();}});
    profileDialog.addEventListener('scroll',event=>{if(!noteTooltip.contains(event.target))hideNote();},true);
    profileDialog.addEventListener('close',hideNote);window.addEventListener('resize',hideNote);
    tab(root.dataset.active);
})();
