(() => {
    let form = document.querySelector('[data-order-filters]');
    const dialog = document.getElementById('vms-order-modal');
    if (!form || !dialog) return;

    const initialUrl = new URL(location.href);
    const requestedType = initialUrl.searchParams.get('type');
    const savedType = history.state?.vmsPurchaseOrderType;
    initialUrl.searchParams.delete('type');
    history.replaceState(history.state, '', initialUrl);

    let timer, listController, detailController;
    const cleanUrl = value => { const url = new URL(value, location.href); url.searchParams.delete('type'); return url; };
    function setBusy(value) {
        const root = document.querySelector('[data-order-directory]');
        root?.setAttribute('aria-busy', String(value));
        const loading = root?.querySelector('[data-order-loading]');
        if (loading) loading.hidden = !value;
    }
    function abortList() { listController?.abort(); setBusy(false); }

    async function loadList(value, refreshVendors = false) {
        clearTimeout(timer);
        listController?.abort();
        const request = new AbortController();
        listController = request;
        const url = new URL(value, location.href);
        if (!url.searchParams.has('type')) url.searchParams.set('type', form.elements.type.value);
        const root = document.querySelector('[data-order-directory]');
        const errorBox = root.querySelector('[data-order-list-error]');
        if (errorBox) errorBox.hidden = true;
        setBusy(true);
        let timedOut = false;
        const deadline = setTimeout(() => { timedOut = true; request.abort(); }, 30000);
        try {
            let next;
            for (let attempt = 0; attempt < 2; attempt++) {
                const forceOptions = refreshVendors || attempt > 0;
                const response = await fetch(url, { signal: request.signal, cache: 'no-store', headers: {
                    Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest',
                    'X-Vms-Order-Partial': '1', 'X-Vms-Order-Refresh': forceOptions ? '1' : '0'
                }});
                if (!response.ok || response.redirected) {
                    if ([401,419].includes(response.status) || response.redirected) throw Error('Sesi login berakhir. Silakan login kembali.');
                    if (response.status === 403) throw Error('Anda tidak memiliki akses ke data ini.');
                    throw Error('Data belum dapat dimuat. Klik Refresh untuk mencoba kembali.');
                }
                const html = new DOMParser().parseFromString(await response.text(), 'text/html');
                next = html.querySelector('[data-order-directory]');
                if (!next) throw Error('Data belum dapat dimuat. Silakan muat ulang halaman.');
                if (request.signal.aborted) return;
                const oldVendor = root.querySelector('[name=vendor]');
                const sameScope = root.dataset.orderScope && root.dataset.orderScope === next.dataset.orderScope;
                if (next.dataset.orderVendorOptions === '1') break;
                if (!sameScope || !oldVendor) {
                    if (attempt === 0) continue;
                    throw Error('Pilihan vendor belum dapat dimuat. Klik Refresh untuk mencoba kembali.');
                }
                const newVendor = next.querySelector('[name=vendor]');
                if (!newVendor) throw Error('Pilihan vendor belum dapat dimuat. Klik Refresh untuk mencoba kembali.');
                newVendor.replaceChildren(...Array.from(oldVendor.options, option => option.cloneNode(true)));
                newVendor.value = url.searchParams.get('vendor') || '';
                break;
            }
            if (request.signal.aborted) return;
            const active = document.activeElement;
            const focusSearch = active === form.elements.search;
            const selection = focusSearch ? [active.selectionStart, active.selectionEnd] : null;
            const oldVendor = form.elements.vendor;
            if (window.jQuery?.fn.select2 && jQuery(oldVendor).hasClass('select2-hidden-accessible')) jQuery(oldVendor).select2('destroy');
            root.replaceWith(next);
            history.replaceState({...history.state, vmsPurchaseOrderType:next.dataset.orderDirectory}, '', cleanUrl(url));
            initFilters();
            if (focusSearch) { form.elements.search.focus({preventScroll:true}); form.elements.search.setSelectionRange(...selection); }
        } catch (failure) {
            if (errorBox && (failure.name !== 'AbortError' || timedOut)) {
                errorBox.textContent = timedOut ? 'Permintaan terlalu lama. Klik Refresh untuk mencoba kembali.' : failure.message;
                errorBox.hidden = false;
            }
        } finally {
            clearTimeout(deadline);
            if (listController === request) setBusy(false);
        }
    }
    function initFilters() {
        form = document.querySelector('[data-order-filters]');
        form.addEventListener('submit', event => {
            if (!window.fetch) return;
            event.preventDefault();
            const url = new URL(location.pathname, location.origin);
            new FormData(form).forEach((value, key) => { if (value !== '') url.searchParams.set(key, value); });
            loadList(url);
        });
        form.querySelectorAll('select').forEach(select => select.addEventListener('change', () => { clearTimeout(timer); form.requestSubmit(); }));
        const vendor = form.elements.vendor;
        if (window.jQuery?.fn.select2) {
            jQuery(vendor).select2({width:'100%',minimumResultsForSearch:0,dropdownCssClass:'vms-order-vendor-dropdown',language:{noResults:()=> 'Vendor tidak ditemukan',searching:()=> 'Mencari vendor...'}})
                .on('select2:select', () => vendor.dispatchEvent(new Event('change', {bubbles:true})))
                .on('select2:open', () => { const input=document.querySelector('.select2-container--open .select2-search__field'); if(input){input.placeholder='Cari nama vendor / nomor VV';input.setAttribute('aria-label','Cari vendor');input.focus();} });
        }
        const search = form.elements.search;
        search.addEventListener('input', () => { clearTimeout(timer); abortList(); if (!search.composing) timer=setTimeout(() => form.requestSubmit(), 400); });
        search.addEventListener('compositionstart', () => { search.composing=true; clearTimeout(timer); abortList(); });
        search.addEventListener('compositionend', () => { search.composing=false; search.dispatchEvent(new Event('input')); });
    }
    initFilters();
    if (!requestedType && ['po','spk','kontrak'].includes(savedType) && form.elements.type.value !== savedType) {
        const restoreUrl = new URL(location.href); restoreUrl.searchParams.set('type', savedType); loadList(restoreUrl);
    } else {
        history.replaceState({...history.state, vmsPurchaseOrderType:form.elements.type.value}, '', location.href);
    }

    document.addEventListener('click', async event => {
        const link = event.target.closest('a');
        if (!link || !link.closest('[data-order-directory]') || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        if (link.matches('[data-order-detail]')) {
            event.preventDefault(); detailController?.abort(); detailController=new AbortController();
            const content=dialog.querySelector('[data-order-content]'); content.textContent='Memuat detail pesanan...'; dialog.showModal();
            try { const response=await fetch(link.href,{signal:detailController.signal,headers:{Accept:'text/html','X-Requested-With':'XMLHttpRequest'}}); if(!response.ok||response.redirected)throw Error('Detail belum dapat dimuat. Periksa sesi login Anda lalu coba kembali.'); content.innerHTML=await response.text(); }
            catch (error) { if(error.name!=='AbortError')content.textContent=error.message; }
            return;
        }
        const url = new URL(link.href);
        if (window.fetch && url.origin===location.origin && url.pathname===location.pathname) {
            event.preventDefault(); loadList(url, link.hasAttribute('data-order-refresh'));
        }
    });
    dialog.querySelectorAll('[data-order-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('close', () => detailController?.abort());
})();
