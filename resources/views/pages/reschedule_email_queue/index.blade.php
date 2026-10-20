<x-app-layout>
    @php
        $statusLabel = ['P' => 'Pending', 'S' => 'Sent', 'F' => 'Failed'];
        $statusClass = [
            'P' => 'bg-amber-50 text-amber-700',
            'S' => 'bg-green-50 text-green-700',
            'F' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div
            class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        📧 Training Reschedule Email Queue</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Sent in batches by <span class="font-mono">training:send-reschedule-emails</span> (every 5 minutes).
                    </p>
                </div>
            </div>

            @if (session('success') || session('error'))
                <div class="border-b border-gray-100 px-5 py-2 text-sm {{ session('success') ? 'text-green-700' : 'text-red-600' }} dark:border-white/[0.06]">
                    {{ session('success') ?? session('error') }}
                </div>
            @endif

            {{-- Status summary / filter --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <a href="{{ route('reschedule_email_queue', array_filter(['q' => $search])) }}"
                    class="rounded-lg border px-3 py-1.5 text-sm {{ $status === null ? 'border-blue-600 text-blue-600' : 'border-gray-200 text-gray-600 dark:border-white/[0.06] dark:text-gray-300' }}">
                    All ({{ $counts->sum() }})
                </a>
                @foreach ($statusLabel as $code => $label)
                    <a href="{{ route('reschedule_email_queue', array_filter(['status' => $code, 'q' => $search])) }}"
                        class="rounded-lg border px-3 py-1.5 text-sm {{ $status === $code ? 'border-blue-600 text-blue-600' : 'border-gray-200 text-gray-600 dark:border-white/[0.06] dark:text-gray-300' }}">
                        {{ $label }} ({{ $counts->get($code, 0) }})
                    </a>
                @endforeach

                <form method="GET" action="{{ route('reschedule_email_queue') }}" class="ml-auto flex items-center gap-2">
                    @if ($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <input type="text" name="q" value="{{ $search }}" placeholder="Registration / Schedule ID"
                        class="h-10 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 dark:border-white/[0.06] dark:bg-transparent dark:text-gray-200">
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        Search
                    </button>
                </form>
            </div>

            <div class="relative overflow-x-auto">
                <table class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">Registration</th>
                            <th class="px-4 py-3 text-left font-medium">Participant</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Schedule</th>
                            <th class="px-4 py-3 text-left font-medium">New Date</th>
                            <th class="px-4 py-3 text-left font-medium">Time</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Attempts</th>
                            <th class="px-4 py-3 text-left font-medium">Sent At</th>
                            <th class="px-4 py-3 text-left font-medium">Last Error</th>
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-200">
                        @forelse ($rows as $row)
                            <tr class="border-b border-gray-100 dark:border-white/[0.06]">
                                <td class="px-4 py-2">{{ $row->id }}</td>
                                <td class="px-4 py-2 font-mono">{{ $row->training_regist_id }}</td>
                                <td class="px-4 py-2">{{ $row->participant_name ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $row->participant_email ?? '-' }}</td>
                                <td class="px-4 py-2 font-mono">{{ $row->schedule_id }}</td>
                                <td class="px-4 py-2">{{ $row->new_date?->format('d M Y') }}</td>
                                <td class="px-4 py-2">{{ $row->time_label ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass[$row->status] ?? '' }}">
                                        {{ $statusLabel[$row->status] ?? $row->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ $row->attempts }}</td>
                                <td class="px-4 py-2">{{ $row->sent_at?->format('d M Y H:i:s') ?? '-' }}</td>
                                <td class="max-w-xs truncate px-4 py-2 text-xs text-red-600" title="{{ $row->last_error }}">
                                    {{ $row->last_error ?? '-' }}
                                </td>
                                <td class="px-4 py-2">
                                    @if ($row->status !== 'P')
                                        <form method="POST" action="{{ route('reschedule_email_queue.resend', $row->id) }}"
                                            onsubmit="return confirm('Resend this email to {{ $row->participant_email ?? 'the participant' }}?')">
                                            @csrf
                                            <button type="submit"
                                                class="rounded-lg border border-blue-600 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50">
                                                Resend
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-8 text-center text-gray-500">The queue is empty.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows->hasPages())
                <div class="border-t border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
