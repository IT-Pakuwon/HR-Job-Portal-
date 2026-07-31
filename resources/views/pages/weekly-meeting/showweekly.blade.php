<x-app-layout>
    <div class="mx-auto w-full max-w-6xl p-3">
        <div id="mom" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between gap-4 border-b pb-5 dark:border-gray-700">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Minute of Meeting</p>
                    <h1 class="mt-1 text-2xl font-extrabold">{{ $meeting->weeklymeeting_topic }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $meeting->weeklymeeting_id }}</p>
                </div>
                <button type="button" onclick="window.print()"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white print:hidden">
                    Export / Print
                </button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-lg border p-4 dark:border-gray-700">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Topic</p>
                    <p class="mt-2 font-extrabold text-gray-800 dark:text-white">{{ $meeting->weeklymeeting_topic }}</p>
                </div>
                <div class="rounded-lg border p-4 dark:border-gray-700">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Meeting Time</p>
                    <p class="mt-2 font-extrabold text-gray-800 dark:text-white">
                        {{ $meeting->weeklymeeting_date?->locale('id')->translatedFormat('l, d F Y') }}
                        · {{ $meeting->weeklymeeting_startdate?->format('H:i') }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="mb-3 font-extrabold">Attendance</h2>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($meeting->participants->sortBy('order_participant') as $participant)
                        <div class="flex items-center gap-3 rounded-lg border px-4 py-3 dark:border-gray-700">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                                {{ $participant->order_participant }}
                            </span>
                            <div>
                                <p class="font-semibold">{{ $participant->name_participant }}</p>
                                <p class="text-xs text-gray-400">{{ $participant->user_participant }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-sm text-gray-500 dark:text-gray-400">No participants.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-6">
                <h2 class="mb-3 font-extrabold">Minute of Meeting</h2>
                <div class="ql-editor min-h-40 rounded-lg border p-5 dark:border-gray-700">
                    @if ($momContent)
                        {!! $momContent !!}
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No MOM content available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    @push('styles')
        <style>
            #mom .ql-editor img {
                max-width: 100%;
                height: auto;
            }
            @media print {
                body { background: white !important; }
                #mom { border: 0 !important; box-shadow: none !important; }
            }
        </style>
    @endpush

    @if (request()->boolean('export'))
        @push('scripts')
            <script>window.addEventListener('load', () => window.print());</script>
        @endpush
    @endif
</x-app-layout>
