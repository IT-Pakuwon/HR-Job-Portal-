<x-app-layout>

    <div class="mx-auto max-w-4xl space-y-8 px-6 py-8">

        {{-- ================= TOASTS ================= --}}
        @if (session('success'))
            <div class="rounded-xl bg-green-100 px-6 py-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl bg-red-100 px-6 py-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif


        {{-- ================= HEADER ================= --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Register Training
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Select your preferred session to continue registration.
            </p>
        </div>


        {{-- ================= TRAINING INFO CARD ================= --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-semibold text-gray-900">
                {{ $training['name'] }}
            </h2>

            @php
                $firstSession = collect($training['sessions'] ?? [])->first();
            @endphp

            @if ($firstSession)
                <div class="mt-4 space-y-2 text-sm text-gray-600">

                    {{-- DATE --}}
                    <div class="flex items-center gap-2">
                        <span>📅</span>
                        <span>
                            {{ \Carbon\Carbon::parse($firstSession['start_date'])->format('d M Y') }}
                        </span>
                    </div>

                    {{-- TYPE --}}
                    <div class="flex items-center gap-2">
                        <span>🎯</span>
                        <span>
                            {{ $training['applies_to_specific'] ? 'Specific Level Training' : 'Open For All Levels' }}
                        </span>
                    </div>

                </div>
            @endif

        </div>


        {{-- ================= REGISTRATION FORM ================= --}}
        <form method="POST" action="{{ route('training.register') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="training_id" value="{{ $training['id'] }}">

            {{-- ================= SESSION LIST ================= --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="mb-6 text-sm font-semibold text-gray-800">
                    Available Sessions
                </h3>

                @forelse($availableSessions as $session)
                    @php
                        $approved = $session['approved_count'] ?? 0;
                        $quota = $session['quota'] ?? 0;
                        $available = max(0, $quota - $approved);
                        $isFull = $approved >= $quota;
                    @endphp

                    <label
                        class="mb-4 flex cursor-pointer items-center justify-between rounded-xl border p-5 transition hover:bg-gray-50">

                        <div class="flex items-center gap-4">

                            <input type="radio" name="session_index" value="{{ $session['index'] }}" required
                                class="h-4 w-4">

                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $session['level'] ?? 'General Session' }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    {{ $approved }} / {{ $quota }} registered
                                </p>
                            </div>
                        </div>

                        <div>
                            @if ($isFull)
                                <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                    WAITLIST
                                </span>
                            @else
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                    {{ $available }} seats left
                                </span>
                            @endif
                        </div>

                    </label>

                @empty
                    <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">
                        No active sessions available.
                    </div>
                @endforelse

            </div>


            {{-- ================= INFO NOTICE ================= --}}
            <div class="rounded-xl bg-gray-50 p-4 text-xs text-gray-600">
                • Registration may require approval from your manager. <br>
                • If quota is full, you will automatically enter the waiting list. <br>
                • Late cancellation may affect future registration priority.
            </div>


            {{-- ================= ACTION BUTTONS ================= --}}
            <div class="flex justify-end gap-4">

                <a href="{{ route('training.list') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2 text-sm text-gray-600 hover:bg-gray-100">
                    Cancel
                </a>

                <button type="submit"
                    class="rounded-lg bg-black px-6 py-2 text-sm font-medium text-white transition hover:opacity-90">
                    Submit Registration
                </button>

            </div>

        </form>

    </div>

</x-app-layout>
