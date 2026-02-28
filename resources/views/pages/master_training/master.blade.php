<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ================= STATUS SUMMARY ================= --}}
        @php
            $all = count($trainings);
            $open = collect($trainings)->where('status', 'OPEN')->count();
            $draft = collect($trainings)->where('status', 'DRAFT')->count();
            $closed = collect($trainings)->where('status', 'CLOSED')->count();
        @endphp

        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">

            {{-- All --}}
            <div
                class="status-card flex items-center gap-3 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-600">
                <div class="text-lg">📚</div>
                <div class="flex-grow">
                    <p class="text-sm font-medium">All Trainings</p>
                </div>
                <p class="text-base font-bold">{{ $all }}</p>
            </div>

            {{-- Open --}}
            <div
                class="status-card flex items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                <div class="text-lg">🟢</div>
                <div class="flex-grow">
                    <p class="text-sm font-medium">Open</p>
                </div>
                <p class="text-base font-bold">{{ $open }}</p>
            </div>

            {{-- Draft --}}
            <div
                class="status-card flex items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600">
                <div class="text-lg">✏️</div>
                <div class="flex-grow">
                    <p class="text-sm font-medium">Draft</p>
                </div>
                <p class="text-base font-bold">{{ $draft }}</p>
            </div>

            {{-- Closed --}}
            <div
                class="status-card flex items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                <div class="text-lg">🔒</div>
                <div class="flex-grow">
                    <p class="text-sm font-medium">Closed</p>
                </div>
                <p class="text-base font-bold">{{ $closed }}</p>
            </div>

        </div>

        {{-- ================= TABLE CONTAINER ================= --}}
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">

            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">
                    Master Training
                </h1>

                <a href="{{ route('mastertraining.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Create
                </a>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-secondary-soft border-b text-sm font-medium">
                        <tr>
                            <th class="px-6 py-2">Training Name</th>
                            <th class="px-6 py-2">Type</th>
                            <th class="px-6 py-2">Date</th>
                            <th class="px-6 py-2">Level</th>
                            <th class="px-6 py-2">Status</th>
                            <th class="px-6 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                        @forelse($trainings as $training)

                            @php
                                $sessions = $training['sessions'] ?? [];

                                $firstDate = null;
                                $lastDate = null;

                                if (!empty($sessions)) {
                                    $sorted = collect($sessions)->sortBy('start_date');
                                    $firstDate = $sorted->first()['start_date'] ?? null;
                                    $lastDate = $sorted->last()['start_date'] ?? null;
                                }

                                $levelLabel = 'All Levels';

                                if (!empty($training['applies_to_specific'])) {
                                    $levels = collect($sessions)->pluck('level')->filter()->unique()->implode(', ');

                                    $levelLabel = $levels ?: 'Specific';
                                }
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">

                                {{-- Name --}}
                                <td class="px-6 py-3 font-semibold">
                                    {{ $training['name'] }}
                                </td>

                                {{-- Type --}}
                                <td class="px-6 py-3">
                                    {{ $training['type'] }}
                                </td>

                                {{-- Date --}}
                                <td class="px-6 py-3">
                                    @if ($firstDate)
                                        {{ \Carbon\Carbon::parse($firstDate)->format('d M Y') }}
                                        @if ($firstDate !== $lastDate)
                                            - {{ \Carbon\Carbon::parse($lastDate)->format('d M Y') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Level --}}
                                <td class="px-6 py-3">
                                    <span
                                        class="rounded border border-indigo-500/40 bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                        {{ $levelLabel }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-3">
                                    @if ($training['status'] == 'OPEN')
                                        <span
                                            class="rounded border border-green-600/40 bg-green-200/60 px-3 py-1 text-xs font-semibold text-green-800">
                                            OPEN
                                        </span>
                                    @elseif($training['status'] == 'DRAFT')
                                        <span
                                            class="rounded border border-gray-600/40 bg-gray-200/60 px-3 py-1 text-xs font-semibold text-gray-800">
                                            DRAFT
                                        </span>
                                    @else
                                        <span
                                            class="rounded border border-red-600/40 bg-red-200/60 px-3 py-1 text-xs font-semibold text-red-800">
                                            CLOSED
                                        </span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('mastertraining.show', $training['id']) }}"
                                        class="inline-flex items-center rounded-md bg-gray-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-gray-700">
                                        View
                                    </a>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                    No training created yet.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </div>

</x-app-layout>
