@php
    // Placeholder content — add more aspects / sub-aspects following the same shape.
    // 'score' is the position (1–9) of the marker on the scale.
    $psychoScaleSize = 9;
    $psychoAspects = [
        [
            'name'  => 'WORK ORIENTATION',
            'items' => [
                [
                    'label' => 'Kebutuhan untuk penyelesaian Tugas',
                    'score' => 5,
                    'desc'  => 'Kecenderungan tanggung jawab terhadap tugas tinggi.',
                ],
            ],
        ],
    ];
@endphp

<div class="mx-auto flex w-full max-w-6xl flex-col gap-4">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
        </span>
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Dorongan Psikologis <span class="font-medium text-gray-400">(General)</span></h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Grafik menggambarkan tinggi / rendahnya kecenderungan dorongan dalam diri individu pada aspek berikut.</p>
        </div>
    </div>

    {{-- ── Aspects ────────────────────────────────────────────────── --}}
    @foreach ($psychoAspects as $aspect)
        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <header class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-2.5">
                    <span class="h-6 w-1.5 rounded-full bg-violet-500"></span>
                    <p class="text-sm font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">{{ $aspect['name'] }}</p>
                </div>
                <span class="rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">{{ count($aspect['items']) }} sub-aspek</span>
            </header>

            <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($aspect['items'] as $item)
                    <div class="grid grid-cols-1 items-center gap-3 px-5 py-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1.2fr)_minmax(0,1.3fr)] lg:gap-6">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Sub-aspek</p>
                            <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $item['label'] }}</p>
                        </div>

                        {{-- Scale --}}
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Skala</p>
                            <div class="mt-1.5 flex items-center gap-1" role="img" aria-label="Skala {{ $item['score'] }} dari {{ $psychoScaleSize }}">
                                @for ($i = 1; $i <= $psychoScaleSize; $i++)
                                    @if ($i === (int) $item['score'])
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-violet-600 text-white shadow-sm shadow-violet-500/30">
                                            <span class="h-2 w-2 rounded-full bg-white"></span>
                                        </span>
                                    @else
                                        <span class="h-6 flex-1 rounded-lg {{ $i < (int) $item['score'] ? 'bg-violet-100 dark:bg-violet-500/20' : 'bg-gray-100 dark:bg-gray-700' }}"></span>
                                    @endif
                                @endfor
                            </div>
                            <div class="mt-1 flex justify-between text-[10px] font-medium uppercase tracking-wide text-gray-400">
                                <span>Rendah</span><span>Tinggi</span>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Keterangan</p>
                            <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
