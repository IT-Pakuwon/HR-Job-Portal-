<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-7xl mx-auto w-full p-4">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between md:gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">
                            Staging Runner — {{ $appId }}
                        </h1>

                        {{-- status badge --}}
                        <span id="badgeRunning"
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $running ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200' }}">
                            {{ $running ? 'RUNNING' : 'IDLE' }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                        Update window <span class="font-semibold">last_update / next_update</span> lalu klik Run untuk eksekusi.
                    </p>
                </div>

                <form method="GET" action="{{ route('integration.acumvms.index') }}" class="flex items-center gap-2">
                    <input name="app" value="{{ $appId }}"
                        class="w-44 rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        placeholder="ACUMVMS">
                    <button class="rounded-lg bg-gray-700 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800 active:scale-[0.99]">
                        Load
                    </button>
                </form>
            </div>

            {{-- Alerts --}}
            @if (session('success'))
                <div class="mt-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-200">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- FORM UPDATE WINDOW --}}
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Setting Window</h2>

                        @if (!$setting)
                            <span class="text-xs font-semibold text-red-600 dark:text-red-300">
                                Setting belum ada untuk {{ $appId }}
                            </span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('integration.acumvms.save') }}" class="mt-4 grid grid-cols-1 gap-4">
                        @csrf
                        <input type="hidden" name="app" value="{{ $appId }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Update</label>
                            <input type="datetime-local" name="last_update"
                                value="{{ old('last_update', optional($setting?->last_update)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                {{ !$setting ? 'disabled' : '' }}>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Next Update</label>
                            <input type="datetime-local" name="next_update"
                                value="{{ old('next_update', optional($setting?->next_update)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                {{ !$setting ? 'disabled' : '' }}>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interval (menit)</label>
                                <input type="number" name="interval" min="1"
                                    value="{{ old('interval', $setting?->interval) }}"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    {{ !$setting ? 'disabled' : '' }}>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select name="status"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    {{ !$setting ? 'disabled' : '' }}>
                                    @php $st = old('status', $setting?->status); @endphp
                                    <option value="">-</option>
                                    <option value="A" {{ $st === 'A' ? 'selected' : '' }}>A (Active)</option>
                                    <option value="I" {{ $st === 'I' ? 'selected' : '' }}>I (Inactive)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-[0.99]
                                {{ !$setting ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !$setting ? 'disabled' : '' }}>
                                Save Setting
                            </button>
                        </div>
                    </form>
                </div>

                {{-- RUNNER --}}
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Execute</h2>

                        <div class="text-xs text-gray-500 dark:text-gray-300">
                            <span class="font-semibold">Lock:</span> <span id="lockText">{{ $running ? 'Running' : 'Idle' }}</span>
                        </div>
                    </div>

                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-semibold">App:</span> {{ $setting?->id_application ?? $appId }}</div>
                        <div><span class="font-semibold">Last:</span> {{ optional($setting?->last_update)->format('Y-m-d H:i:s') ?? '-' }}</div>
                        <div><span class="font-semibold">Next:</span> {{ optional($setting?->next_update)->format('Y-m-d H:i:s') ?? '-' }}</div>
                        <div><span class="font-semibold">Status:</span> {{ $setting?->status ?? '-' }}</div>
                    </div>

                    <form method="POST" action="{{ route('integration.acumvms.run') }}" class="mt-4" id="formRun">
                        @csrf
                        <input type="hidden" name="app" value="{{ $appId }}">

                        @php
                            $selectedModules = old('modules', array_keys($stagingModules ?? []));
                        @endphp

                        <div class="mb-4">
                            <div class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">Pilih Modul</div>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                @foreach (($stagingModules ?? []) as $moduleKey => $moduleLabel)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700/60">
                                        <input type="checkbox" name="modules[]" value="{{ $moduleKey }}"
                                            class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                            {{ in_array($moduleKey, $selectedModules, true) ? 'checked' : '' }}
                                            {{ (!$setting || $running) ? 'disabled' : '' }}>
                                        <span>{{ $moduleLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Jika hanya sebagian modul dijalankan, window tidak digeser agar modul lain masih bisa diproses pada periode yang sama.
                            </p>
                        </div>

                        <button type="submit" id="btnRun"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed"
                            {{ (!$setting || $running) ? 'disabled' : '' }}>
                            {{ $running ? 'Staging is Running...' : 'Run Staging Now' }}
                        </button>

                        @if (!$setting)
                            <p class="mt-2 text-xs text-red-600 dark:text-red-300">
                                Setting sys_staging_setting untuk {{ $appId }} belum ada, jadi Run dinonaktifkan.
                            </p>
                        @endif
                    </form>

                    @if (session('result'))
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Result</h3>
                            <pre class="mt-2 max-h-80 overflow-auto rounded-lg bg-gray-900 p-3 text-xs text-gray-100">{{ json_encode(session('result'), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnRun = document.getElementById('btnRun');
            const badge = document.getElementById('badgeRunning');
            const lockText = document.getElementById('lockText');
            const formRun = document.getElementById('formRun');

            // Disable tombol Run saat submit (UX)
            if (formRun && btnRun) {
                formRun.addEventListener('submit', () => {
                    btnRun.disabled = true;
                    btnRun.textContent = 'Running...';
                });
            }

            // Polling status lock
            const statusUrl = @json(route('integration.acumvms.status'));

            async function poll() {
                try {
                    const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();

                    const running = !!data.running;

                    // badge
                    badge.textContent = running ? 'RUNNING' : 'IDLE';
                    lockText.textContent = running ? 'Running' : 'Idle';

                    badge.className = 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ' +
                        (running
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'
                            : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200');

                    // tombol run: kalau running => disable
                    // NOTE: kalau setting null dari server, tombol sudah disabled dari blade
                    if (btnRun && !btnRun.hasAttribute('data-force-disabled')) {
                        const forceDisabledByBlade = btnRun.hasAttribute('data-disabled-by-blade');
                        if (!forceDisabledByBlade) {
                            btnRun.disabled = running;
                            btnRun.textContent = running ? 'Staging is Running...' : 'Run Staging Now';
                        }
                    }
                } catch (e) {
                    // silent
                }
            }

            // start polling
            poll();
            setInterval(poll, 3000);
        });
    </script>
</x-app-layout>
