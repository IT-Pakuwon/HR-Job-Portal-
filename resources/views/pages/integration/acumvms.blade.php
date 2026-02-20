<x-app-layout>
    <div class="max-w-7xl mx-auto w-full p-4">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">
                        Staging Runner — {{ $appId }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Update window last_update / next_update lalu klik Run untuk eksekusi.
                    </p>
                </div>

                <form method="GET" action="{{ route('staging.acumvms.index') }}" class="flex items-center gap-2">
                    <input name="app" value="{{ $appId }}"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                        placeholder="ACUMVMS">
                    <button class="rounded-lg bg-gray-700 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800">
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
                {{-- FORM UPDATE --}}
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Setting Window</h2>

                    <form method="POST" action="{{ route('staging.acumvms.update') }}" class="mt-4 grid grid-cols-1 gap-4">
                        @csrf
                        <input type="hidden" name="app" value="{{ $appId }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Update</label>
                            <input type="datetime-local" name="last_update"
                                value="{{ old('last_update', optional($row->last_update)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Next Update</label>
                            <input type="datetime-local" name="next_update"
                                value="{{ old('next_update', optional($row->next_update)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interval (menit)</label>
                                <input type="number" name="interval" min="1"
                                    value="{{ old('interval', $row->interval) }}"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <input name="status" value="{{ old('status', $row->status) }}"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                Save Setting
                            </button>
                        </div>
                    </form>
                </div>

                {{-- RUNNER --}}
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Execute</h2>

                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-semibold">App:</span> {{ $row->id_application }}</div>
                        <div><span class="font-semibold">Last:</span> {{ optional($row->last_update)->format('Y-m-d H:i:s') }}</div>
                        <div><span class="font-semibold">Next:</span> {{ optional($row->next_update)->format('Y-m-d H:i:s') }}</div>
                    </div>

                    <form method="POST" action="{{ route('staging.acumvms.run') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="app" value="{{ $appId }}">
                        <button type="submit" id="btnRun"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 active:scale-[0.99]">
                            Run Staging Now
                        </button>
                    </form>

                    @if (session('run_result'))
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Result</h3>
                            <pre class="mt-2 max-h-80 overflow-auto rounded-lg bg-gray-900 p-3 text-xs text-gray-100">{{ json_encode(session('run_result'), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        // UX sederhana: disable tombol Run saat submit
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('staging.acumvms.run') }}"]');
            const btn = document.getElementById('btnRun');
            if (form && btn) {
                form.addEventListener('submit', () => {
                    btn.disabled = true;
                    btn.textContent = 'Running...';
                });
            }
        });
    </script>
</x-app-layout>
