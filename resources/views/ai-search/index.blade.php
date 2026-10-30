<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="rounded-xl bg-white p-6 shadow">
            <h1 class="text-2xl font-semibold text-gray-900">AI Search</h1>
            <p class="mt-2 text-sm text-gray-600">
                Cari informasi dengan pertanyaan dalam bahasa sehari-hari.
            </p>
            @if (!$aiConfigured)
                <p class="mt-4 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">
                    Konfigurasi layanan AI belum lengkap.
                </p>
            @endif
            <p class="mt-4 text-sm text-gray-600">
                Cari daftar data, hitung jumlah, atau temukan nilai terbanyak pada satu tabel.
                Tabel dan kolom mengikuti katalog serta izin pengguna. Join dan total nominal belum didukung.
            </p>

            <form id="ai-search-form" action="{{ route('ai-search.search') }}" method="POST" class="mt-6">
                @csrf
                <label for="ai-search-source" class="block text-sm font-medium text-gray-700">Sumber data</label>
                <select id="ai-search-source" name="source_id" required class="mt-2 mb-4 block w-full rounded-lg border-gray-300">
                    @forelse ($sources as $source)
                        <option value="{{ $source['id'] }}" @selected($source['id'] === (int) config('ai-search.source_id'))>
                            {{ $source['name'] }} ({{ $source['table_count'] }} tabel)
                        </option>
                    @empty
                        <option value="">Tidak ada sumber yang diizinkan. Periksa ai_permissions.</option>
                    @endforelse
                </select>
                <label for="ai-search-question" class="block text-sm font-medium text-gray-700">Pertanyaan</label>
                <textarea id="ai-search-question" name="question" rows="4" required maxlength="2000"
                    class="mt-2 block w-full rounded-lg border-gray-300"
                    placeholder="Sebutkan tabel atau kebutuhan Anda, misalnya: Berapa jumlah data pada tabel purchasing yang dipilih?"
                    aria-describedby="ai-search-status"></textarea>
                <button id="ai-search-submit" type="submit" @disabled(count($sources) === 0)
                    class="mt-4 rounded-lg bg-indigo-600 px-5 py-2 text-white disabled:opacity-50">
                    Cari
                </button>
            </form>

            <p id="ai-search-status" role="status" aria-live="polite" class="mt-4 text-sm text-gray-700"></p>
            <section class="mt-6 border-t border-gray-200 pt-4" aria-labelledby="ai-search-results-title">
                <h2 id="ai-search-results-title" class="text-lg font-semibold text-gray-900">Hasil pencarian</h2>
                <p id="ai-search-result" class="mt-2 text-sm text-gray-700">Belum ada hasil pencarian.</p>
                <p id="ai-search-scope" class="mt-2 text-sm text-gray-500"></p>
                <div id="ai-search-table" class="mt-4 overflow-x-auto"></div>
            </section>
        </div>
    </div>
    <script src="{{ asset('assets/js/ai-search.js') }}?v={{ filemtime(public_path('assets/js/ai-search.js')) }}" defer></script>
</x-app-layout>
