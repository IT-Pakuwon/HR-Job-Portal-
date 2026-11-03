(() => {
    const form=document.querySelector('[data-order-filters]'),dialog=document.getElementById('vms-order-modal');
    if(!form||!dialog)return;
    const url=new URL(location.href);
    if(url.searchParams.has('type')){url.searchParams.delete('type');history.replaceState(history.state,'',url);}
    let timer,controller;
    form.querySelectorAll('select').forEach(select=>select.addEventListener('change',()=>{clearTimeout(timer);form.requestSubmit();}));
    const vendor=form.elements.vendor;
    if(window.jQuery?.fn.select2){
        jQuery(vendor).select2({width:'100%',minimumResultsForSearch:0,dropdownCssClass:'vms-order-vendor-dropdown',language:{noResults:()=> 'Vendor tidak ditemukan',searching:()=> 'Mencari vendor...'}})
            .on('select2:select',()=>vendor.dispatchEvent(new Event('change',{bubbles:true})))
            .on('select2:open',()=>{const input=document.querySelector('.select2-container--open .select2-search__field');if(input){input.placeholder='Cari nama vendor / nomor VV';input.setAttribute('aria-label','Cari vendor');input.focus();}});
    }
    const search=form.elements.search;search.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(()=>{if(!search.composing)form.requestSubmit();},650);});
    search.addEventListener('compositionstart',()=>{search.composing=true;});search.addEventListener('compositionend',()=>{search.composing=false;search.dispatchEvent(new Event('input'));});
    document.querySelectorAll('[data-order-detail]').forEach(link=>link.addEventListener('click',async event=>{
        if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;
        event.preventDefault();controller?.abort();controller=new AbortController();const content=dialog.querySelector('[data-order-content]');content.textContent='Memuat detail pesanan...';dialog.showModal();
        try{const response=await fetch(link.href,{signal:controller.signal,headers:{Accept:'text/html','X-Requested-With':'XMLHttpRequest'}});if(!response.ok||response.redirected)throw Error('Detail belum dapat dimuat. Periksa sesi login Anda lalu coba kembali.');content.innerHTML=await response.text();}catch(error){if(error.name!=='AbortError')content.textContent=error.message;}
    }));
    dialog.querySelectorAll('[data-order-close]').forEach(button=>button.addEventListener('click',()=>dialog.close()));
    dialog.addEventListener('close',()=>controller?.abort());
})();
