@php
    $agendaCount = $agenda->count();
    $stateStyles = [
        'Upcoming'  => ['bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300', 'bg-indigo-500'],
        'Ongoing'   => ['bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300', 'bg-emerald-500'],
        'Done'      => ['bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300', 'bg-gray-400'],
        'Cancelled' => ['bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300', 'bg-red-500'],
    ];
@endphp

{{-- ── Header ─────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between gap-3 px-5 py-4">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
        </span>
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Schedule</h3>
            <p class="text-xs text-gray-400">{{ $agendaCount }} {{ \Illuminate\Support\Str::plural('event', $agendaCount) }} for this applicant</p>
        </div>
    </div>
    <button type="button" onclick="openAgendaModal()"
        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:bg-indigo-700 focus:outline-none active:scale-95">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Create Schedule
    </button>
</div>

{{-- ── Schedule cards ─────────────────────────────────────────────── --}}
<div class="space-y-3 px-5 pb-5"
    x-data="{
        page: 1, per: 10, total: {{ $agendaCount }},
        get pages() { return Math.max(1, Math.ceil(this.total / this.per)); },
        get from() { return this.total ? (this.page - 1) * this.per + 1 : 0; },
        get to() { return Math.min(this.page * this.per, this.total); },
        show(i) { return i >= (this.page - 1) * this.per && i < this.page * this.per; },
        go(p) { this.page = Math.min(this.pages, Math.max(1, p)); },
        get nums() {
            const n = [], p = this.page, last = this.pages;
            for (let i = 1; i <= last; i++) {
                if (i === 1 || i === last || Math.abs(i - p) <= 1) n.push(i);
                else if (n[n.length - 1] !== '…') n.push('…');
            }
            return n;
        }
    }">
    @forelse ($agenda as $p)
        @php
            $isPrevPosition = !empty($remapCutoff) && $p->created_at && $p->created_at < $remapCutoff;
            $start = $p->startdate ? \Carbon\Carbon::parse($p->startdate) : null;
            $end   = $p->enddate ? \Carbon\Carbon::parse($p->enddate) : null;
            $now   = now();

            if ($p->agenda_note) { $state = 'Cancelled'; }
            elseif ($start && $end && $now->between($start, $end)) { $state = 'Ongoing'; }
            elseif ($start && $now->lt($start)) { $state = 'Upcoming'; }
            else { $state = 'Done'; }

            [$stateBadge, $stateDot] = $stateStyles[$state];

            $sameDay = $start && $end && $start->isSameDay($end);
            $timeLabel = $start
                ? ($sameDay ? $start->format('H:i').' – '.$end->format('H:i') : $start->format('d M H:i').' → '.($end ? $end->format('d M H:i') : '—'))
                : '—';
            $mins = ($start && $end) ? $start->diffInMinutes($end) : null;
            $durLabel = $mins === null ? null : ($mins >= 60 ? floor($mins / 60).'h'.($mins % 60 ? ' '.($mins % 60).'m' : '') : $mins.'m');

            $people = collect(preg_split('/[,;]+/', (string) $p->participant))->map(fn ($x) => trim($x))->filter()->values();
        @endphp

        <div x-show="show({{ $loop->index }})" @if(!$loop->first) x-cloak @endif class="group relative flex gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700/60 dark:bg-gray-800 @if($isPrevPosition || $state === 'Cancelled') opacity-70 @endif">
            <span class="absolute inset-y-4 left-0 w-1 rounded-r-full {{ $stateDot }}"></span>

            {{-- Date tile --}}
            <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-gray-50 text-center dark:bg-gray-700/60">
                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-500">{{ $start ? $start->format('M') : '—' }}</span>
                <span class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $start ? $start->format('d') : '' }}</span>
                <span class="mt-0.5 text-[10px] text-gray-400">{{ $start ? $start->format('D') : '' }}</span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $p->title }}</h4>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $stateBadge }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $stateDot }}"></span>{{ $state }}
                    </span>
                    @php $isOnlineType = ($p->scheduletype ?? 'Offline') === 'Online'; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isOnlineType ? 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300' : 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300' }}">
                        @if ($isOnlineType)
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        @else
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        @endif
                        {{ $isOnlineType ? 'Online' : 'Offline' }}
                    </span>
                    @if ($isPrevPosition)
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600 dark:bg-amber-500/15 dark:text-amber-300"
                            title="Created before this applicant was remapped. Create a new schedule for the new position.">Previous position</span>
                    @endif
                </div>

                @if ($p->description)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $p->description }}</p>
                @endif

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ $timeLabel }}@if($durLabel) <span class="text-gray-400">· {{ $durLabel }}</span>@endif
                    </span>
                    @if ($people->isNotEmpty())
                        <span class="inline-flex flex-wrap items-center gap-1.5">
                            @foreach ($people as $person)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 py-0.5 pl-0.5 pr-2.5 font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold uppercase text-white">{{ mb_substr($person, 0, 1) }}</span>{{ $person }}
                                </span>
                            @endforeach
                        </span>
                    @endif
                </div>

                @if ($p->agenda_note)
                    <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-500/10 dark:text-red-300">
                        <span class="font-semibold">Cancel reason:</span> {{ $p->agenda_note }}
                    </p>
                @endif
            </div>

            @if ($p->status == 'C')
                <div class="shrink-0 self-start">
                    <button onclick="openCancelModal({{ $p->id }})"
                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-red-300 hover:bg-red-50 hover:text-red-500 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-red-500/10">
                        Cancel
                    </button>
                </div>
            @endif
        </div>
    @empty
        <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-200 py-14 text-center dark:border-gray-700">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-2xl dark:bg-indigo-500/15">🗓️</span>
            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No schedules yet</p>
            <p class="text-xs text-gray-400">Create one to plan an interview for this applicant.</p>
        </div>
    @endforelse

    {{-- Pagination (10 per page) --}}
    <div x-show="total > per" x-cloak class="flex flex-wrap items-center justify-between gap-3 pt-2">
        <p class="text-xs text-gray-400">Showing <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="from + '–' + to"></span> of <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="total"></span></p>
        <nav class="flex items-center gap-1" aria-label="Schedule pages">
            <button type="button" @click="go(page - 1)" :disabled="page === 1"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Previous page">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <template x-for="(n, idx) in nums" :key="idx">
                <button type="button" @click="typeof n === 'number' && go(n)" :disabled="n === '…'"
                    :class="n === page ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="flex h-8 min-w-8 items-center justify-center rounded-lg border px-2 text-xs font-semibold transition disabled:cursor-default disabled:border-transparent disabled:hover:bg-transparent"
                    x-text="n"></button>
            </template>
            <button type="button" @click="go(page + 1)" :disabled="page === pages"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Next page">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </nav>
    </div>
</div>

{{-- ── Create Schedule Modal ──────────────────────────────────────── --}}
@php
    $fieldCls = 'w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-500/20';
    $labelCls = 'mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300';
    $roCls = 'w-full cursor-not-allowed rounded-xl border border-dashed border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700/60 dark:text-gray-400';
@endphp
<div id="agendaModal" style="display:none;"
    class="fixed inset-0 z-9999 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
    <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800">

        {{-- Header --}}
        <div class="relative overflow-hidden bg-indigo-600 px-6 py-5 text-white">
            <div class="pointer-events-none absolute -right-8 -top-10 h-36 w-36 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-12 right-24 h-28 w-28 rounded-full bg-white/10"></div>
            <button type="button" onclick="closeAgendaModal()" aria-label="Close"
                class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/30 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="relative flex items-center gap-3.5">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008Z"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold tracking-tight">Create Schedule</h3>
                    <p class="text-sm text-white/80">Set up an interview or event for this applicant</p>
                </div>
            </div>
        </div>

        <form id="createAgendaForm" class="flex-1 space-y-4 overflow-y-auto bg-gray-50/60 px-6 py-5 dark:bg-gray-900/30">
            @csrf
            <input type="hidden" name="refid" value="{{ $career->docid }}" />
            <input type="hidden" name="cpnyid" value="{{ $jobposting->cpnyid }}" />
            <input type="hidden" name="departementid" value="{{ $jobposting->departementid }}" />

            {{-- Schedule type --}}
            <fieldset id="agendaScheduleType" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <legend class="sr-only">Schedule Type</legend>
                <p class="mb-2.5 text-[11px] font-bold uppercase tracking-widest text-gray-400">How will it take place?</p>
                <div class="grid grid-cols-2 gap-2">
                    <label class="agenda-type-option relative cursor-pointer">
                        <input type="radio" name="scheduletype" value="Offline" checked required class="sr-only">
                        <span class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 transition dark:border-gray-600 dark:text-gray-300">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            Offline
                        </span>
                    </label>
                    <label class="agenda-type-option relative cursor-pointer">
                        <input type="radio" name="scheduletype" value="Online" required class="sr-only">
                        <span class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 transition dark:border-gray-600 dark:text-gray-300">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9A2.25 2.25 0 0 0 13.5 5.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            Online
                        </span>
                    </label>
                </div>
            </fieldset>

            {{-- Details --}}
            <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Z"/></svg>
                    </span>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Details</p>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Title</label>
                    <input type="text" name="title" required placeholder="e.g. Interview with HR" class="{{ $fieldCls }}">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Description</label>
                    <textarea name="description" required rows="2" placeholder="Additional notes for this schedule" class="{{ $fieldCls }}"></textarea>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Interview Type</label>
                    <select name="reftype" required class="{{ $fieldCls }}">
                        <option value="">Select type</option>
                        <option value="IU">Interview User</option>
                        @if($hcTrackActive ?? true)
                            <option value="IH">Interview HC</option>
                        @endif
                        <option value="IHU">Interview HC & User</option>
                    </select>
                </div>
            </section>

            {{-- When --}}
            <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">When</p>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelCls }}">Start</label>
                        <input type="datetime-local" name="startdate" required class="{{ $fieldCls }}">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">End</label>
                        <input type="datetime-local" name="enddate" required class="{{ $fieldCls }}">
                    </div>
                </div>
            </section>

            {{-- Online: meeting link --}}
            <section id="onlineMeetingFields" hidden class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <div class="mb-3 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                    </span>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Online Meeting</p>
                </div>
                <label for="onlineMeetingLink" class="{{ $labelCls }}">Meeting link</label>
                <textarea id="onlineMeetingLink" name="link_onlinemeeting" rows="4" maxlength="2048" disabled placeholder="Paste meeting link, Meeting ID, and Passcode here"
                    class="{{ $fieldCls }}" style="min-height: 110px; resize: vertical;"></textarea>
            </section>

            {{-- Offline: site + auto-filled location --}}
            <section id="offlineScheduleFields" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </span>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Where</p>
                    </div>
                    <span class="text-[11px] italic text-gray-400">Auto-filled from selected site</span>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="{{ $labelCls }}">Site</label>
                        <select name="site" id="siteDropdown" required class="select2-site w-full">
                            <option value="">Select site</option>
                            @foreach ($companyaddress as $site)
                                <option value="{{ $site->site }}">
                                    {{ $site->site }}{{ $site->sitelocation ? ' (' . $site->sitelocation . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Location</label>
                        <input type="text" name="location" id="locationField" readonly class="{{ $roCls }}">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Address</label>
                        <textarea name="location_address" id="addressField" readonly rows="2" class="{{ $roCls }}"></textarea>
                    </div>
                </div>
            </section>

            {{-- Who --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <div class="mb-3 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    </span>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Who</p>
                </div>
                <label class="{{ $labelCls }}">Participants</label>
                <select class="select2 w-full" name="participant[]" multiple required>
                    @foreach ($userlist as $p)
                        <option value="{{ $p->username }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </section>
        </form>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <button type="button" onclick="closeAgendaModal()"
                class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button id="createAgendaSubmit" type="submit" form="createAgendaForm"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:bg-indigo-700 focus:outline-none active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Save Schedule
            </button>
        </div>
    </div>
</div>

{{-- ── Cancel Schedule Modal ──────────────────────────────────────── --}}
<div id="cancelModal" style="display:none;"
    class="fixed inset-0 z-99999 flex items-center justify-center bg-black/40">
    <div class="w-full max-w-sm rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Cancel Schedule</h3>
        </div>
        <form id="cancelForm" class="p-5">
            @csrf
            <input type="hidden" name="agenda_id" id="cancel_agenda_id">
            <label class="mb-1 block text-sm font-semibold text-gray-600 dark:text-gray-300">Reason for Cancellation</label>
            <textarea name="reason" required rows="3"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeCancelModal()"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                    Close
                </button>
                <button type="submit"
                    class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none active:scale-95">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    #agendaScheduleType .agenda-type-option span:hover {
        border-color: #a0d7ff;
    }
    #agendaScheduleType input:checked + span {
        background: #3193da;
        border-color: #3193da;
        color: #ffffff;
        box-shadow: 0 4px 12px -2px rgba(49, 147, 218, .35);
    }
    #agendaScheduleType input:focus-visible + span {
        outline: 2px solid #56b1f3;
        outline-offset: 2px;
    }
    .dark #agendaScheduleType input:checked + span {
        background: #56b1f3;
        border-color: #56b1f3;
        color: #ffffff;
    }

    /* ===== Select2 — match the modal's rounded fields ===== */
    #agendaModal .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background-color: #f9fafb;
        display: flex;
        align-items: center;
    }
    #agendaModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
        padding-left: 0.875rem;
        font-size: 0.875rem;
        color: #1f2937;
    }
    #agendaModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    #agendaModal .select2-container--default .select2-selection--multiple {
        min-height: 42px;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background-color: #f9fafb;
        padding: 3px 6px;
    }
    #agendaModal .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #e3f3ff;
        border: 1px solid #b6e1ff;
        color: #1c71ae;
        border-radius: 9999px;
        padding: 1px 10px;
        font-size: 0.8125rem;
        font-weight: 600;
        margin-top: 4px;
    }
    #agendaModal .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #56b1f3;
        margin-right: 6px;
    }
    #agendaModal .select2-container--default.select2-container--focus .select2-selection--multiple,
    #agendaModal .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #56b1f3;
        box-shadow: 0 0 0 4px rgba(86, 177, 243, .12);
    }
    .select2-dropdown {
        border-radius: 0.75rem;
        overflow: hidden;
        border-color: #e5e7eb;
        box-shadow: 0 12px 28px -8px rgba(0, 0, 0, .18);
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3193da;
    }
    .dark #agendaModal .select2-container--default .select2-selection--single,
    .dark #agendaModal .select2-container--default .select2-selection--multiple {
        background-color: #374151;
        border-color: #4b5563;
    }
    .dark #agendaModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f9fafb;
    }
    .dark #agendaModal .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: rgba(86, 177, 243, .2);
        border-color: rgba(86, 177, 243, .4);
        color: #d1ecff;
    }
    /* ===== Select2 Dark Mode (Participant multi-select) ===== */
    .dark .select2-container--default .select2-selection--multiple {
        background-color: #374151; /* gray-700 */
        border-color: #4b5563; /* gray-600 */
        color: #f9fafb; /* gray-50 */
    }

    .dark .select2-selection--multiple .select2-selection__rendered {
        color: #f9fafb;
    }

    /* Selected item (chip) */
    .dark .select2-selection--multiple .select2-selection__choice {
        background-color: #4b5563; /* gray-600 */
        border: 1px solid #6b7280; /* gray-500 */
        color: #f9fafb;
    }

    /* Remove (x) button */
    .dark .select2-selection__choice__remove {
        color: #d1d5db; /* gray-300 */
    }

    .dark .select2-selection__choice__remove:hover {
        color: #f87171; /* red-400 */
    }

    /* Search input inside the multi-select box */
    .dark .select2-search__field {
        color: #f9fafb;
    }

    .dark .select2-search__field::placeholder {
        color: #9ca3af; /* gray-400 */
    }

    /* Dropdown */
    .dark .select2-dropdown {
        background-color: #1f2933; /* gray-800 */
        border-color: #4b5563;
    }

    /* Dropdown options */
    .dark .select2-results__option {
        color: #e5e7eb;
    }

    /* Hovered option */
    .dark .select2-results__option--highlighted {
        background-color: #4b5563;
        color: #ffffff;
    }

    /* Selected option */
    .dark .select2-results__option[aria-selected="true"] {
        background-color: #374151;
    }
</style>

<script>
    function openAgendaModal() {
        syncAgendaScheduleType();
        document.getElementById('agendaModal').style.display = 'flex';
    }
    function syncAgendaScheduleType() {
        const online = $('#agendaScheduleType input:checked').val() === 'Online';
        const $offline = $('#offlineScheduleFields');
        $offline.prop('hidden', online);
        $offline.find('input, select, textarea').each(function() {
            if (this.dataset.offlineRequired === undefined) {
                this.dataset.offlineRequired = this.required ? '1' : '0';
            }
            this.disabled = online;
            this.required = !online && this.dataset.offlineRequired === '1';
        });
        $('#onlineMeetingFields').prop('hidden', !online);
        $('#onlineMeetingLink').prop('disabled', !online).prop('required', online);
    }
    $('#agendaScheduleType').on('change', syncAgendaScheduleType);
    function closeAgendaModal() {
        document.getElementById('agendaModal').style.display = 'none';
    }

    $(document).off('submit', '#createAgendaForm');
    let isCreatingAgenda = false;

    $(document).on('submit', '#createAgendaForm', function(e) {
        e.preventDefault();
        if (isCreatingAgenda) return;
        isCreatingAgenda = true;

        const $btn = $('#createAgendaSubmit');
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '{{ route('agendas.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success('Schedule created successfully');
                closeAgendaModal();
                $('#createAgendaForm')[0].reset();
                location.reload();
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                const errors = response.errors ? Object.values(response.errors).flat().join('\n') : null;
                toastr.error(errors || response.message || response.error || 'Failed to create schedule', '', { escapeHtml: true });
                console.error(xhr.responseText);
            },
            complete: function() {
                isCreatingAgenda = false;
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    function openCancelModal(id) {
        $('#cancel_agenda_id').val(id);
        document.getElementById('cancelModal').style.display = 'flex';
    }
    function closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }

    $('#cancelForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('agendas.cancel') }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success("Schedule cancelled successfully");
                location.reload();
            },
            error: function(xhr) {
                alert('Cancel failed: ' + xhr.responseText);
            }
        });
    });

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select participants",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#agendaModal')
        });

        $('.select2-site').select2({
            placeholder: "Select site",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#agendaModal')
        });

        $('#siteDropdown').on('change', function() {
            const site = $(this).val();
            if (site) {
                $.ajax({
                    url: '/company-address/' + site,
                    type: 'GET',
                    success: function(data) {
                        if (data) {
                            $('#locationField').val(data.location);
                            $('#addressField').val(data.address2);
                        } else {
                            $('#locationField').val('');
                            $('#addressField').val('');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to fetch site info');
                    }
                });
            } else {
                $('#locationField').val('');
                $('#addressField').val('');
            }
        });
    });
</script>
