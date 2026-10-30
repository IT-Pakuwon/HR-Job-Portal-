(() => {
    'use strict';

    const form = document.getElementById('ai-search-form');
    if (!form) return;

    const question = document.getElementById('ai-search-question');
    const source = document.getElementById('ai-search-source');
    const button = document.getElementById('ai-search-submit');
    const status = document.getElementById('ai-search-status');
    const result = document.getElementById('ai-search-result');
    const scope = document.getElementById('ai-search-scope');
    const tableContainer = document.getElementById('ai-search-table');

    function renderTable(columns, rows) {
        const table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200 text-left text-sm';
        const head = table.createTHead().insertRow();
        for (const label of columns) {
            const cell = document.createElement('th');
            cell.className = 'px-3 py-2 font-semibold';
            cell.scope = 'col';
            cell.textContent = label;
            head.appendChild(cell);
        }
        const body = table.createTBody();
        for (const values of rows) {
            const row = body.insertRow();
            for (const value of values) {
                const cell = row.insertCell();
                cell.className = 'border-t border-gray-200 px-3 py-2';
                cell.textContent = value !== null && typeof value === 'object'
                    ? JSON.stringify(value) : String(value ?? '');
            }
        }
        tableContainer.replaceChildren(table);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) return;
        if (!source || !source.value) {
            status.textContent = 'Pilih sumber data. Jika pilihan sumber tidak muncul, muat ulang halaman dengan Ctrl+F5.';
            return;
        }

        const value = question.value.trim();
        if (!value) {
            status.textContent = 'Pertanyaan tidak boleh kosong.';
            question.focus();
            return;
        }

        button.disabled = true;
        form.setAttribute('aria-busy', 'true');
        status.textContent = 'Memproses pertanyaan...';
        result.textContent = 'Menunggu hasil...';
        scope.textContent = '';
        tableContainer.replaceChildren();
        const abort = new AbortController();
        const timeout = setTimeout(() => abort.abort(), 45000);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                },
                body: JSON.stringify({ question: value, source_id: Number(source.value) }),
                signal: abort.signal,
            });

            if (response.status === 401 || response.status === 419 || response.redirected) {
                status.textContent = 'Sesi berakhir. Muat ulang halaman dan login kembali.';
                return;
            }
            if (response.status === 429) {
                status.textContent = 'Terlalu banyak permintaan. Silakan coba lagi sebentar.';
                return;
            }
            if (!response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Unexpected response');
            }

            const payload = await response.json();
            status.textContent = payload.errors?.question?.[0] || payload.errors?.source_id?.[0]
                || payload.message
                || 'Permintaan belum dapat diproses.';
            if (response.ok && payload.status === 'success') {
                result.textContent = payload.message;
                scope.textContent = payload.data.scope || '';
                renderTable(payload.data.columns, payload.data.rows);
            }
        } catch (error) {
            status.textContent = error.name === 'AbortError'
                ? 'Permintaan terlalu lama. Silakan coba lagi.'
                : 'Tidak dapat menghubungi layanan pencarian. Silakan coba lagi.';
        } finally {
            clearTimeout(timeout);
            if (result.textContent === 'Menunggu hasil...') {
                result.textContent = 'Belum ada hasil pencarian.';
            }
            button.disabled = false;
            form.removeAttribute('aria-busy');
        }
    });
})();
