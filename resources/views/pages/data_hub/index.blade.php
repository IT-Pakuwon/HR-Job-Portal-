<x-app-layout>
    <div class="mx-auto w-full max-w-7xl space-y-6 p-4 sm:p-6">
        <div>
            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Data Hub</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-800 dark:text-gray-100">OM Dashboard</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih mall untuk melihat performa operasional, laporan departemen, dan Kaizen.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($malls as $code => $name)
                <a href="{{ route('datahub.om.detail', $code) }}"
                    class="group flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-500">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5l7-2v18m0-14h7v14M8 7v1m0 3v1m0 3v1m7-5h1m-1 4h1" /></svg>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ $code }}</span>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $name }}</h2>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-gray-500 dark:text-gray-400">Pantau performa operasional {{ $name }}.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400">Buka dashboard <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" /></svg></span>
                </a>
            @empty
                <div class="rounded-xl border border-gray-200 bg-white p-8 text-center sm:col-span-2 xl:col-span-4 dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">Tidak ada mall yang dapat diakses</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Akses Data Hub tersedia, tetapi perusahaan akun belum dipetakan ke mall OM.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
