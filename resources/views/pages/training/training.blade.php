<x-app-layout>
    <div class="max-w-9xl mx-auto w-full space-y-4 p-2">
        {{-- ================= HEADER ================= --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Training Events
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Browse and register for available training sessions.
                </p>
            </div>

            {{-- STATUS FILTER --}}
            @php $currentStatus = request('status'); @endphp
            <div class="flex flex-wrap gap-2">
                @foreach (['ALL', 'OPEN', 'FULL', 'CLOSED', 'FINISHED'] as $filter)
                    <a href="{{ route('training.list', ['status' => $filter == 'ALL' ? null : $filter]) }}"
                        class="{{ ($currentStatus ?? 'ALL') == $filter
                            ? 'bg-black text-white shadow'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} rounded-full px-4 py-2 text-xs font-medium transition">
                        {{ $filter }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ================= GRID ================= --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            @php $filter = request('status'); @endphp

            @forelse($trainings as $training)

                @php
                    $sessions = collect($training['sessions'] ?? [])->where('is_active', true);

                    if (!$training['is_active'] || $sessions->isEmpty()) {
                        continue;
                    }

                    $sorted = $sessions->sortBy('start_date');
                    $firstSession = $sorted->first();
                    $lastSession = $sorted->last();

                    $firstDate = $firstSession['start_date'] ?? null;
                    $lastDate = $lastSession['start_date'] ?? null;

                    $totalQuota = $sessions->sum('quota');
                    $totalApproved = $sessions->sum('approved_count');
                    $percentage = $totalQuota > 0 ? ($totalApproved / $totalQuota) * 100 : 0;

                    $status = $training['status'] ?? 'OPEN';

                    if ($totalApproved >= $totalQuota && $totalQuota > 0) {
                        $status = 'FULL';
                    }

                    $levels = $training['applies_to_specific']
                        ? $sessions->pluck('level')->filter()->unique()->toArray()
                        : ['ALL'];

                    if ($filter && $filter != 'ALL' && $status != $filter) {
                        continue;
                    }
                @endphp

                {{-- ================= CARD ================= --}}
                <div
                    class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition duration-300 hover:shadow-xl">

                    {{-- POSTER --}}
                    <div class="relative h-40 bg-gray-100">
                        @if (!empty($training['poster']))
                            <img src="{{ asset('storage/' . $training['poster']) }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-gray-400">
                                No Image
                            </div>
                        @endif

                        {{-- STATUS BADGE --}}
                        <div class="absolute right-4 top-4">
                            <span
                                class="@if ($status == 'OPEN') bg-green-500
                            @elseif($status == 'FULL') bg-yellow-500
                            @elseif($status == 'CLOSED') bg-gray-500
                            @elseif($status == 'FINISHED') bg-blue-500
                            @else bg-gray-400 @endif rounded-full px-3 py-1 text-xs text-white shadow">
                                {{ $status }}
                            </span>
                        </div>
                    </div>

                    {{-- CONTENT --}}
                    <div class="space-y-4 p-6">

                        <h3 class="line-clamp-2 text-base font-semibold text-gray-900">
                            {{ $training['name'] }}
                        </h3>

                        {{-- DATE --}}
                        @if ($firstDate)
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <span>📅</span>
                                <span>
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d M Y') }}
                                    @if ($firstDate != $lastDate)
                                        - {{ \Carbon\Carbon::parse($lastDate)->format('d M Y') }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- LEVEL --}}
                        <div class="flex items-center gap-2 text-sm text-gray-500">
                            <span>🎯</span>
                            <span>{{ implode(' • ', $levels) }}</span>
                        </div>

                        {{-- QUOTA --}}
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>{{ $totalApproved }} / {{ $totalQuota }} Participants</span>
                            <span>{{ round($percentage) }}%</span>
                        </div>

                        {{-- PROGRESS BAR --}}
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full bg-black transition-all duration-500"
                                style="width: {{ $percentage }}%">
                            </div>
                        </div>

                        {{-- BUTTON --}}
                        <div class="pt-2">
                            @if ($status == 'OPEN')
                                <a href="{{ route('training.register.form', $training['id']) }}"
                                    class="block w-full rounded-xl bg-black py-2.5 text-center text-sm text-white transition hover:opacity-90">
                                    Register Now
                                </a>
                            @elseif($status == 'FULL')
                                <a href="{{ route('training.register.form', $training['id']) }}"
                                    class="block w-full rounded-xl bg-yellow-500 py-2.5 text-center text-sm text-white transition hover:opacity-90">
                                    Join Waiting List
                                </a>
                            @else
                                <button disabled
                                    class="w-full cursor-not-allowed rounded-xl bg-gray-200 py-2.5 text-sm text-gray-500">
                                    Registration Closed
                                </button>
                            @endif
                        </div>

                    </div>
                </div>

            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                    <div class="mb-4 text-5xl">📚</div>
                    <p class="text-sm">No training events available.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
