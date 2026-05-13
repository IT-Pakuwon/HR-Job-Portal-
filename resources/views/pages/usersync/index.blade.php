<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-5xl mx-auto p-4">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">User Sync (MySQL → PostgreSQL)</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Menjalankan command <code class="px-1 rounded bg-gray-100 dark:bg-gray-700">sync:users-das-to-pg</code> secara manual.
                    </p>
                </div>

                <button id="btnRun"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-95">
                    Run Sync
                </button>
            </div>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                        Since (optional) - format: YYYY-MM-DD HH:MM:SS
                    </label>
                    <input id="since" type="text" placeholder="2026-02-26 10:00:00"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:bg-gray-900 dark:text-white dark:border-gray-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Jika diisi, hanya user dengan <code>updated_at >= since</code> yang di-sync.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                        Chunk
                    </label>
                    <input id="chunk" type="number" min="50" max="5000" value="500"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:bg-gray-900 dark:text-white dark:border-gray-700">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Besar batch per iterasi.
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-200">Output</h2>
                    <span id="statusBadge"
                        class="hidden rounded-full px-3 py-1 text-xs font-semibold"></span>
                </div>

                <pre id="outputBox"
                    class="mt-2 min-h-[140px] whitespace-normal rounded-xl border border-gray-200 bg-gray-50 p-4 text-xs text-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">Klik "Run Sync" untuk menjalankan...</pre>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('btnRun');
            const sinceEl = document.getElementById('since');
            const chunkEl = document.getElementById('chunk');
            const outputBox = document.getElementById('outputBox');
            const badge = document.getElementById('statusBadge');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function setBadge(type, text) {
                badge.classList.remove('hidden');
                badge.textContent = text;

                badge.classList.remove('bg-green-100','text-green-800','bg-red-100','text-red-800','bg-yellow-100','text-yellow-800');
                if (type === 'ok') badge.classList.add('bg-green-100','text-green-800');
                else if (type === 'err') badge.classList.add('bg-red-100','text-red-800');
                else badge.classList.add('bg-yellow-100','text-yellow-800');
            }

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                setBadge('warn', 'Running...');
                outputBox.textContent = 'Running command...';

                try {
                    const payload = {
                        since: (sinceEl.value || '').trim() || null,
                        chunk: Number(chunkEl.value || 500),
                    };

                    const res = await fetch("{{ route('user_sync.run') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.ok) {
                        setBadge('err', 'Failed');
                        outputBox.textContent = data?.message ? (data.message + '\n' + (data.output || '')) : JSON.stringify(data, null, 2);
                        return;
                    }

                    setBadge('ok', 'Success');
                    outputBox.textContent = data.output || 'OK (no output)';

                } catch (e) {
                    setBadge('err', 'Error');
                    outputBox.textContent = String(e);
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            });
        })();
    </script>
</x-app-layout>
