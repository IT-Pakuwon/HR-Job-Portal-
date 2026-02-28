<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-6 p-4">

        {{-- ================= HEADER CARD ================= --}}
        <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">

            <div class="flex items-center justify-between border-b pb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    {{ $training['name'] }}
                </h2>

                <div class="flex items-center gap-3">
                    @if ($training['is_active'])
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            ACTIVE
                        </span>
                    @else
                        <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">
                            INACTIVE
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- LEFT SIDE --}}
                <div class="space-y-4">

                    <div>
                        <p class="text-xs text-gray-500">Type</p>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $training['type'] }}
                        </p>
                    </div>

                    @if ($training['type'] === 'NON_MANDATORY')
                        <div>
                            <p class="text-xs text-gray-500">Category</p>
                            <p class="font-medium text-gray-800 dark:text-white">
                                {{ $training['category'] ?? '-' }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <p class="text-xs text-gray-500">Trainer</p>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $training['trainer'] }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Location</p>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $training['location'] }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Applies To</p>
                        <p class="font-medium text-gray-800 dark:text-white">
                            {{ $training['applies_to_specific'] ? 'Specific Levels' : 'All Levels' }}
                        </p>
                    </div>

                </div>

                {{-- RIGHT SIDE --}}
                <div class="space-y-4">

                    <div>
                        <p class="text-xs text-gray-500">Description</p>
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $training['description'] }}
                        </p>
                    </div>

                    @if (!empty($training['poster']))
                        <div>
                            <p class="mb-2 text-xs text-gray-500">Poster</p>
                            <img src="{{ asset('storage/' . $training['poster']) }}"
                                class="max-h-60 rounded-lg border border-gray-200 dark:border-gray-600">
                        </div>
                    @endif

                </div>

            </div>
        </div>


        {{-- ================= SESSION CARD ================= --}}
        <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">

            <div class="border-b pb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Training Sessions
                </h3>
            </div>

            @if (empty($training['sessions']))
                <p class="mt-6 text-sm text-gray-500">
                    No sessions added yet.
                </p>
            @else
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm dark:border-gray-700">

                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @if ($training['applies_to_specific'])
                                    <th class="border-b px-4 py-3 text-left">Level</th>
                                @endif
                                <th class="border-b px-4 py-3 text-left">Date</th>
                                <th class="border-b px-4 py-3 text-left">Time</th>
                                <th class="border-b px-4 py-3 text-left">Quota</th>
                                <th class="border-b px-4 py-3 text-left">Close Registration</th>
                                <th class="border-b px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($training['sessions'] as $session)
                                <tr class="border-b">

                                    @if ($training['applies_to_specific'])
                                        <td class="px-4 py-2">
                                            {{ $session['level'] ?? '-' }}
                                        </td>
                                    @endif

                                    <td class="px-4 py-2">
                                        {{ $session['start_date'] }}
                                    </td>

                                    <td class="px-4 py-2">
                                        {{ $session['start_time'] }} - {{ $session['end_time'] }}
                                    </td>

                                    <td class="px-4 py-2">
                                        {{ $session['quota'] }}
                                    </td>

                                    <td class="px-4 py-2">
                                        {{ $session['close_date'] ?? '-' }}
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        @if ($session['is_active'])
                                            <span
                                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                ACTIVE
                                            </span>
                                        @else
                                            <span
                                                class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">
                                                INACTIVE
                                            </span>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            @endif

        </div>


        {{-- ================= ACTION BUTTONS ================= --}}
        <div class="flex justify-end gap-4">

            <a href="{{ route('mastertraining.index') }}"
                class="rounded-md border border-gray-300 px-5 py-2 text-sm text-gray-600 hover:bg-gray-100">
                Back
            </a>

            <a href="{{ route('mastertraining.edit', $training['id']) }}"
                class="rounded-md bg-black px-6 py-2 text-sm text-white hover:opacity-90">
                Edit Training
            </a>

        </div>

    </div>

</x-app-layout>
