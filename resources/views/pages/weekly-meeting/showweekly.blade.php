<x-app-layout>
    <div class="mx-auto w-full max-w-6xl p-3">
        <div id="mom" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between gap-4 border-b pb-5 dark:border-gray-700">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Minute of Meeting</p>
                    <h1 class="mt-1 text-2xl font-extrabold">{{ $meeting->weeklymeeting_topic }}</h1>
                    <p class="mt-2 text-sm text-gray-500">{{ $meeting->weeklymeeting_id }} · {{ $meeting->weeklymeeting_date->format('d M Y') }}</p>
                </div>
                <button type="button" onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white print:hidden">Export / Print</button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h2 class="mb-3 font-extrabold">Attendance</h2>
                    <div class="overflow-hidden rounded-lg border dark:border-gray-700">
                        @forelse ($meeting->participants->sortBy('order_participant') as $participant)
                            <div class="flex gap-3 border-b px-4 py-3 last:border-0 dark:border-gray-700">
                                <span class="text-gray-400">{{ $participant->order_participant }}.</span>
                                <span class="font-semibold">{{ $participant->name_participant }}</span>
                            </div>
                        @empty
                            <div class="p-5 text-sm text-gray-500">No participants.</div>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h2 class="mb-3 font-extrabold">Finding Summary</h2>
                    <div class="overflow-hidden rounded-lg border dark:border-gray-700">
                        @forelse ($meeting->findings as $meetingFinding)
                            <div class="border-b px-4 py-3 last:border-0 dark:border-gray-700">
                                <div class="flex justify-between gap-3">
                                    <strong>{{ $meetingFinding->finding_id }}</strong>
                                    <span>{{ $meetingFinding->finding_status }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ $meetingFinding->finding?->issue }}</p>
                            </div>
                        @empty
                            <div class="p-5 text-sm text-gray-500">No findings selected.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (request()->boolean('export'))
        @push('scripts')
            <script>window.addEventListener('load', () => window.print());</script>
        @endpush
    @endif
</x-app-layout>
