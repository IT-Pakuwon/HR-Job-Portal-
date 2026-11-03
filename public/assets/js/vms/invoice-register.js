(() => {
    let form=document.querySelector('[data-invoice-filters]');
    const dialog=document.getElementById('vms-order-modal');if(!form||!dialog)return;
    let timer,controller,currentUrl,saving=false,changed=false,listController;
    let listUrl=new URL(location.href);listUrl.searchParams.set('tab',form.elements.tab.value);
    function cleanUrl(value){const url=new URL(value);url.searchParams.delete('tab');return url;}
    history.replaceState(history.state,'',cleanUrl(location.href));
    function busy(value){const root=document.querySelector('[data-invoice-page]');root?.setAttribute('aria-busy',String(value));const loading=root?.querySelector('.vms-invoice-loading');if(loading)loading.hidden=!value;}
    async function loadList(value){
        clearTimeout(timer);listController?.abort();const request=new AbortController();listController=request;
        const url=new URL(value,location.href),root=document.querySelector('[data-invoice-page]'),error=root.querySelector('[data-invoice-list-error]');if(error)error.hidden=true;busy(true);
        try{
            const response=await fetch(url,{signal:request.signal,cache:'no-store',headers:{Accept:'text/html','X-Requested-With':'XMLHttpRequest'}});
            if(!response.ok||response.redirected)throw Error([401,419].includes(response.status)?'Sesi login berakhir. Silakan login kembali.':'Data belum dapat dimuat. Klik Refresh untuk mencoba kembali.');
            const html=new DOMParser().parseFromString(await response.text(),'text/html'),next=html.querySelector('[data-invoice-page]');if(!next)throw Error('Data belum dapat dimuat. Silakan muat ulang halaman.');
            if(request.signal.aborted)return;
            const active=document.activeElement,focusSearch=active===form.elements.search,selection=focusSearch?[active.selectionStart,active.selectionEnd]:null;
            if(window.jQuery?.fn.select2)jQuery(root).find('.select2-hidden-accessible').select2('destroy');
            root.replaceWith(next);listUrl=url;history.replaceState(history.state,'',cleanUrl(url));initFilters();
            if(focusSearch){form.elements.search.focus({preventScroll:true});form.elements.search.setSelectionRange(...selection);}
        }catch(failure){if(failure.name!=='AbortError'&&error){error.textContent=failure.message;error.hidden=false;}}
        finally{if(listController===request)busy(false);}
    }
    function initFilters(){
        form=document.querySelector('[data-invoice-filters]');
        form.addEventListener('submit',event=>{event.preventDefault();const url=new URL(location.pathname,location.origin);new FormData(form).forEach((value,key)=>{if(value!=='')url.searchParams.set(key,value);});loadList(url);});
        form.querySelectorAll('select').forEach(select=>select.addEventListener('change',()=>form.requestSubmit()));
        const vendor=form.elements.vendor;
        if(vendor&&window.jQuery?.fn.select2)jQuery(vendor).select2({width:'100%',minimumResultsForSearch:0,dropdownCssClass:'vms-order-vendor-dropdown',language:{noResults:()=> 'Vendor tidak ditemukan'}}).on('select2:select',()=>vendor.dispatchEvent(new Event('change',{bubbles:true}))).on('select2:open',()=>{const input=document.querySelector('.select2-container--open .select2-search__field');if(input){input.placeholder='Cari nama vendor / nomor VV';input.focus();}});
        const search=form.elements.search;search.addEventListener('input',()=>{clearTimeout(timer);listController?.abort();busy(false);if(!search.composing)timer=setTimeout(()=>form.requestSubmit(),400);});search.addEventListener('compositionstart',()=>{search.composing=true;clearTimeout(timer);listController?.abort();busy(false);});search.addEventListener('compositionend',()=>{search.composing=false;search.dispatchEvent(new Event('input'));});
    }
    initFilters();
    async function load(url){controller?.abort();controller=new AbortController();currentUrl=url;const content=dialog.querySelector('[data-order-content]');content.textContent='Memuat Invoice Register...';try{const response=await fetch(url,{signal:controller.signal,headers:{Accept:'text/html','X-Requested-With':'XMLHttpRequest'}});if(!response.ok||response.redirected)throw Error('Detail belum dapat dimuat. Periksa sesi login Anda lalu coba kembali.');content.innerHTML=await response.text();}catch(error){if(error.name!=='AbortError')content.textContent=error.message;}}
    document.addEventListener('click',event=>{
        const link=event.target.closest('a');if(!link||!link.closest('[data-invoice-page]')||event.ctrlKey||event.metaKey||event.shiftKey||event.altKey||event.button!==0)return;
        if(link.matches('[data-invoice-detail]')){event.preventDefault();dialog.showModal();load(link.href);return;}
        const url=new URL(link.href);if(url.origin===location.origin&&url.pathname===location.pathname){event.preventDefault();loadList(url);}
    });
    dialog.querySelectorAll('[data-invoice-close]').forEach(button=>button.addEventListener('click',()=>{if(!saving)dialog.close();}));dialog.addEventListener('cancel',event=>{if(saving)event.preventDefault();});dialog.addEventListener('close',()=>{controller?.abort();if(changed){changed=false;loadList(listUrl);}});
    dialog.addEventListener('click',async event=>{
        const button=event.target.closest('[data-invoice-action]');if(!button||saving)return;
        const action=button.dataset.invoiceAction,record=dialog.querySelector('[data-invoice-record]'),notes=['revise','reject','return'].includes(action),labels={approve:'Approve dokumen',revise:'Minta revisi',reject:'Reject invoice',final:'Approve final Finance',receive:'Receive invoice',return:'Return invoice'};
        if(!window.Swal){return;}
        const result=await Swal.fire({target:dialog,title:labels[action]+'?',text:notes?'Tuliskan alasan untuk vendor.':'Status invoice akan diperbarui.',icon:'question',showCancelButton:true,confirmButtonText:'Ya, lanjutkan',cancelButtonText:'Batal',confirmButtonColor:'#4e70b4',...(notes?{input:'textarea',inputAttributes:{maxlength:5000},inputValidator:value=>!value?.trim()?'Catatan wajib diisi.':undefined}:{})});
        if(!result.isConfirmed)return;
        saving=true;dialog.querySelectorAll('[data-invoice-action]').forEach(item=>item.disabled=true);
        try{const response=await fetch(record.dataset.decisionUrl,{method:'POST',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':record.querySelector('[name=_token]').value},body:JSON.stringify({action,document:button.dataset.document?Number(button.dataset.document):null,tax_invoice:button.dataset.tax||null,note:notes?result.value:null,token:record.dataset.token})});const data=await response.json().catch(()=>({}));if(!response.ok)throw Error(Object.values(data.errors||{}).flat().join('\n')||data.message||'Permintaan gagal.');changed=true;await Swal.fire({target:dialog,icon:'success',title:'Berhasil',text:data.message,confirmButtonColor:'#4e70b4'});await load(currentUrl);}catch(error){await Swal.fire({target:dialog,icon:'error',title:'Belum berhasil',text:error.message,confirmButtonColor:'#4e70b4'});}finally{saving=false;dialog.querySelectorAll('[data-invoice-action]').forEach(item=>item.disabled=false);}
    });
})();
