<x-app-layout>
    @php
        $ctl = 'h-10 rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-10 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-slate-200';
        $cards = [
            ['C', 'Jobs', '🆕', 'yellow'],
            ['P', 'On Progress', '🛠️', 'blue'],
            ['D', 'Completed', '✅', 'green'],
            ['X', 'Cancelled', '❌', 'cancel'],
            ['ALL', 'All', '📦', 'all'],
        ];
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="complaintLog()">
        <div class="mb-3 grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
            @foreach ($cards as [$key, $label, $icon, $tone])
                <x-status-card :key="$key" :label="$label" :icon="$icon" :tone="$tone" />
            @endforeach
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Complaint Jobs</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <select x-model="f.year" @change="reload()" class="{{ $ctl }}">
                        <option value="">All Years</option>
                        @foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                    </select>
                    <select x-model="f.siteid" @change="reload()" class="{{ $ctl }} w-44">
                        <option value="">All Sites</option>
                        @foreach ($sites as $s)<option value="{{ $s->siteid }}">{{ $s->sitename }}</option>@endforeach
                    </select>
                    <select x-model="f.category" @change="reload()" class="{{ $ctl }} w-48">
                        <option value="">All Categories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
            </div>

            <div class="p-2" x-ignore>
                <table id="complaintTable" class="w-full min-w-full border-separate border-spacing-0 text-sm" style="width:100%">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-center font-medium" style="text-align:center">Action</th>
                            <th class="px-4 py-3 text-center font-medium" style="text-align:center">Complaint No</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Tenant</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Detail (same look as the Ticket Support detail modal) --}}
        <div x-show="detail" x-cloak @keydown.escape.window="detail = null" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm dark:bg-black/70" @click="detail = null"></div>
            <div class="relative z-10 flex max-h-[92vh] min-h-[600px] w-full max-w-[1500px] flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-white/[0.06] dark:bg-[#0f172a]">
                <template x-if="detail">
                    <div class="flex min-h-0 flex-1 flex-col">
                        {{-- Action prompt: Process (date + description), Complete / Cancel (notes required) --}}
                        <div x-show="ask" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            class="absolute inset-0 z-[70] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @keydown.escape.stop="ask = null; err = ''">
                            <div x-show="ask" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-3 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                class="w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#111827]" @click.outside="ask = null; err = ''">
                                <template x-if="ask">
                                    <div>
                                        {{-- Header --}}
                                        <div class="flex items-start justify-between gap-3 border-b px-6 py-5" :class="askUi().soft">
                                            <div class="flex items-center gap-4">
                                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-lg text-white shadow-md ring-4" :class="askUi().tile"><i class="fa-solid" :class="askUi().icon"></i></span>
                                                <div>
                                                    <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white" x-text="askUi().title"></h3>
                                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="askUi().sub"></p>
                                                </div>
                                            </div>
                                            <button type="button" @click="ask = null; err = ''" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-black/5 hover:text-gray-600 dark:hover:bg-white/10"><i class="fa-solid fa-xmark"></i></button>
                                        </div>

                                        <div class="space-y-5 px-6 py-5">
                                            {{-- Status change preview --}}
                                            <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-3 text-xs dark:bg-white/[0.04]">
                                                <span class="font-semibold uppercase tracking-wide text-gray-400">Status</span>
                                                <span class="rounded-full px-2.5 py-1 font-semibold" :class="badge(detail.status)" x-text="detail.status_label"></span>
                                                <i class="fa-solid fa-arrow-right text-gray-300"></i>
                                                <span class="rounded-full px-2.5 py-1 font-semibold" :class="badge(askUi().to)" x-text="askUi().toLabel"></span>
                                            </div>

                                            {{-- Process date --}}
                                            <div x-show="ask === 'process'">
                                                <div class="mb-1.5 flex items-center justify-between">
                                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300"><i class="fa-regular fa-calendar mr-1.5 text-gray-400"></i>Process Date</label>
                                                    <button type="button" x-show="actDate" @click="actDate = ''" class="text-[11px] font-semibold text-blue-600 hover:underline">Use now</button>
                                                </div>
                                                <input type="datetime-local" x-model="actDate" :max="nowLocal()"
                                                    class="h-11 w-full rounded-xl border border-gray-200 bg-white px-3.5 text-sm text-gray-800 focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:[color-scheme:dark] dark:focus:ring-amber-500/10">
                                                <p class="mt-1.5 flex items-center gap-1.5 text-[11px] text-gray-400"><i class="fa-regular fa-clock"></i>
                                                    <span x-text="actDate ? 'Saved as ' + actDate.replace('T', ' ') : 'Left empty, the current date & time is used.'"></span></p>
                                            </div>

                                            {{-- Description / notes --}}
                                            <div>
                                                <div class="mb-1.5 flex items-center justify-between">
                                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300"><i class="fa-regular fa-comment-dots mr-1.5 text-gray-400"></i><span x-text="ask === 'process' ? 'Description' : 'Notes'"></span>
                                                        <span x-show="ask !== 'process'" class="text-rose-500">*</span><span x-show="ask === 'process'" class="font-normal text-gray-400">(optional)</span></label>
                                                    <span class="text-[11px] text-gray-400" x-text="notes.length + ' / 1000'"></span>
                                                </div>
                                                <textarea x-model="notes" x-ref="askNotes" rows="4" maxlength="1000"
                                                    :placeholder="{ process: 'Describe what will be done…', complete: 'What was done to resolve this complaint?', cancel: 'Why is this complaint cancelled?' }[ask]"
                                                    class="w-full resize-none rounded-xl border border-gray-200 bg-white p-3.5 text-sm leading-relaxed text-gray-800 placeholder:text-gray-400 focus:ring-4 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                                    :class="askUi().focus"></textarea>
                                            </div>

                                            <p x-show="err" x-text="err" class="flex items-center gap-2 rounded-xl bg-red-50 px-3.5 py-2.5 text-xs font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400"></p>
                                        </div>

                                        {{-- Footer --}}
                                        <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50/70 px-6 py-4 dark:border-white/10 dark:bg-white/[0.02]">
                                            <button type="button" @click="ask = null; err = ''" class="h-11 rounded-xl border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-white/10 dark:bg-transparent dark:text-gray-300">Back</button>
                                            <button type="button" :disabled="busy" @click="confirmAsk()" class="inline-flex h-11 items-center gap-2 rounded-xl px-6 text-sm font-semibold text-white shadow-sm transition disabled:opacity-60" :class="askUi().btn">
                                                <i class="fa-solid" :class="busy ? 'fa-circle-notch fa-spin' : askUi().icon"></i><span x-text="busy ? 'Submitting…' : askUi().cta"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Header --}}
                        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 lg:flex-row lg:items-start lg:justify-between dark:border-gray-800">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white" x-text="detail.complaintnbr"></h2>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badge(detail.status)" x-text="detail.status_label"></span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="[detail.category, detail.storename].filter(Boolean).join(' · ') || '-'"></p>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2" x-data="{ menu: false }" @click.outside="menu = false">
                                <div class="relative" x-show="['C','P'].includes(detail.status)">
                                    <button type="button" :disabled="busy" @click="menu = !menu"
                                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 disabled:opacity-50 dark:border-white/[0.06] dark:bg-[#111827] dark:text-gray-200 dark:hover:bg-[#182132]">
                                        <i class="fa-solid fa-bolt text-[14px]"></i><span>Actions</span><i class="fa-solid fa-chevron-down text-[11px] transition" :class="menu && 'rotate-180'"></i>
                                    </button>
                                    <div x-show="menu" x-cloak x-transition.opacity class="absolute right-0 top-[calc(100%+8px)] z-50 w-60 overflow-hidden rounded-lg border border-gray-200 bg-white p-2 shadow-xl dark:border-white/[0.06] dark:bg-[#111827]">
                                        <button type="button" x-show="detail.status === 'C'" @click="menu = false; ask = 'process'; err = ''; actDate = ''; notes = ''; $nextTick(() => $refs.askNotes && $refs.askNotes.focus())" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-blue-50 dark:hover:bg-white/5"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-xs text-blue-600 dark:bg-blue-500/20"><i class="fa-solid fa-play"></i></span>Process Complaint</button>
                                        <button type="button" x-show="detail.status === 'P'" @click="menu = false; ask = 'complete'; err = ''; $nextTick(() => $refs.askNotes && $refs.askNotes.focus())" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-green-50 dark:hover:bg-white/5"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 text-xs text-green-600 dark:bg-green-500/20"><i class="fa-solid fa-check"></i></span>Completed</button>
                                        <a x-show="!hasWo()" :href="'{{ url('/createwos') }}?complaint=' + detail.eid" target="_blank" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-violet-50 dark:hover:bg-white/5"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-xs text-violet-600 dark:bg-violet-500/20"><i class="fa-solid fa-share-from-square"></i></span>Transfer to WO</a>
                                        <div class="my-1 border-t border-gray-100 dark:border-white/10"></div>
                                        <button type="button" @click="menu = false; ask = 'cancel'; err = ''; $nextTick(() => $refs.askNotes && $refs.askNotes.focus())" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-red-50 dark:hover:bg-white/5"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 text-xs text-red-600 dark:bg-red-500/20"><i class="fa-solid fa-xmark"></i></span>Cancel</button>
                                    </div>
                                </div>
                                <button type="button" @click="detail = null" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 dark:border-white/[0.06] dark:bg-[#111827] dark:text-gray-300 dark:hover:bg-rose-500/10"><i class="fa-solid fa-xmark text-[16px]"></i></button>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto xl:grid-cols-12 xl:overflow-hidden">
                            {{-- Left: information --}}
                            <div class="min-h-0 border-b border-gray-200 p-6 xl:col-span-7 xl:overflow-y-auto xl:border-b-0 xl:border-r dark:border-gray-800">
                                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Complaint Information</h3>
                                <div class="space-y-4">
                                    <template x-for="f in [
                                        ['Created By', detail.created_user || '-'],
                                        ['Complaint Date', String(detail.complaintdate || '').substring(0, 16) || '-'],
                                        ...(detail.resolved_date ? [['Resolved Date', String(detail.resolved_date).substring(0, 16)]] : []),
                                        ['Site', detail.sitename || detail.siteid || '-'],
                                        ['Category', detail.category || '-'],
                                        ['Tenant', [detail.storename, detail.tenantcompanyname].filter(Boolean).join(' · ') || '-'],
                                        ['Floor / Unit', [detail.floor, detail.unit].filter(Boolean).join(' / ') || '-'],
                                    ]" :key="f[0]">
                                        <div><label class="text-xs text-gray-400" x-text="f[0]"></label><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white" x-text="f[1]"></p></div>
                                    </template>
                                </div>

                                <h3 class="mb-3 mt-8 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Description</h3>
                                <div class="whitespace-pre-line rounded-lg bg-gray-50 px-4 py-3 text-sm leading-7 text-gray-800 dark:bg-gray-800/70 dark:text-gray-200" x-text="detail.description"></div>

                                <p x-show="err && !ask" x-text="err" class="mt-6 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-500/10 dark:text-red-400"></p>
                            </div>

                            {{-- Right: Tracking / Discussion / Attachments --}}
                            <div class="flex min-h-[480px] min-w-0 flex-col xl:col-span-5 xl:min-h-0">
                                <div class="border-b border-gray-200 bg-gray-50/70 px-6 py-2 dark:border-gray-800 dark:bg-gray-900/60">
                                    <div class="inline-flex max-w-full items-center gap-1 rounded-lg border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                        <template x-for="t in [['activity', 'Tracking', 'fa-clock-rotate-left'], ['messages', 'Discussion', 'fa-comments'], ['files', 'Attachments', 'fa-paperclip']]" :key="t[0]">
                                            <button type="button" @click="tab = t[0]; $nextTick(() => scrollChat())"
                                                class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                                                :class="tab === t[0] ? 'bg-slate-900 text-white shadow-lg dark:bg-white dark:text-slate-900' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white'">
                                                <i class="fa-solid text-[12px]" :class="t[2]"></i><span x-text="t[1]"></span>
                                                <span class="rounded-full px-1.5 text-[10px]" :class="tab === t[0] ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10'"
                                                    x-text="t[0] === 'activity' ? (detail.activities || []).length : (t[0] === 'messages' ? (detail.messages || []).length : (detail.attachments || []).length)"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Tracking timeline --}}
                                <div x-show="tab === 'activity'" class="flex-1 overflow-y-auto p-6">
                                    <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-100">Tracking Timeline</h3>
                                    <div class="space-y-3">
                                        <template x-for="(a, i) in (detail.activities || [])" :key="i">
                                            <div class="relative flex gap-3">
                                                <div class="flex flex-col items-center">
                                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm text-white" :class="tl(a.type).circle"><i class="fa-solid" :class="tl(a.type).icon"></i></span>
                                                    <span x-show="i < (detail.activities || []).length - 1" class="mt-1 w-px flex-1 bg-gray-200 dark:bg-white/10"></span>
                                                </div>
                                                <div class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-white/[0.06] dark:bg-[#111827]">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="tl(a.type).title || a.label"></div>
                                                            <div class="mt-0.5 text-[11px] text-gray-400"><span x-text="a.user"></span> <span class="mx-1">•</span> <span x-text="a.time"></span></div>
                                                        </div>
                                                        <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-bold uppercase" :class="tl(a.type).chip" x-text="tl(a.type).tag"></span>
                                                    </div>
                                                    <div x-show="a.note" class="mt-3 whitespace-pre-line break-words rounded-lg border px-3 py-2 text-sm" :class="tl(a.type).box" x-text="a.note"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Discussion --}}
                                <div x-show="tab === 'messages'" x-cloak class="flex min-h-0 flex-1 flex-col">
                                    <div x-ref="chatBody" class="min-h-[260px] flex-1 space-y-3 overflow-y-auto p-6">
                                        <div x-show="!(detail.messages || []).length" class="pt-12 text-center text-sm text-gray-400"><i class="fa-regular fa-comments mb-2 block text-2xl"></i>No messages yet.</div>
                                        <template x-for="(m, i) in (detail.messages || [])" :key="i">
                                            <div :class="m.created_user === me ? 'items-end' : 'items-start'" class="flex flex-col">
                                                <div class="mb-0.5 flex items-center gap-2 text-[11px] text-gray-400"><span class="font-semibold text-gray-600 dark:text-gray-300" x-text="m.name || m.created_user"></span><span x-text="String(m.created_datetime || '').substring(0, 16)"></span></div>
                                                <div class="max-w-[90%] whitespace-pre-line break-words rounded-2xl px-3.5 py-2 text-sm shadow-sm"
                                                    :class="m.created_user === me ? 'rounded-tr-sm bg-slate-900 text-white dark:bg-blue-600' : 'rounded-tl-sm bg-gray-100 text-gray-800 dark:bg-white/5 dark:text-gray-200'" x-html="msgHtml(m.message, m.created_user === me)"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="relative border-t border-gray-200 p-4 dark:border-gray-800">
                                        <div x-show="mention.open && mentionList().length" x-cloak class="absolute bottom-full left-4 right-4 mb-1 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-[#0f172a]">
                                            <template x-for="(p, i) in mentionList()" :key="p.username">
                                                <button type="button" @mousedown.prevent="pickMention(p)" @mouseenter="mention.index = i" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm" :class="i === mention.index ? 'bg-blue-50 dark:bg-blue-500/10' : ''">
                                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold uppercase text-blue-600" x-text="p.username.charAt(0)"></span>
                                                    <span class="min-w-0 flex-1"><span class="block truncate font-medium" x-text="p.name"></span><span class="block truncate text-[11px] text-gray-400" x-text="'@' + p.username"></span></span>
                                                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold" :class="p.role === 'Tenant' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'" x-text="p.role"></span>
                                                </button>
                                            </template>
                                        </div>
                                        <div class="flex items-end gap-2 overflow-hidden rounded-lg border border-gray-200 bg-white p-2 shadow-sm focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:focus-within:ring-blue-500/10">
                                            <textarea x-ref="chatInput" x-model="chatText" rows="1" maxlength="1000" placeholder="Write a message… type @ to mention"
                                                @input="checkMention($event)" @click="checkMention($event)" @blur="mention.open = false"
                                                @keydown.down="if (mention.open) { $event.preventDefault(); mention.index = Math.min(mention.index + 1, mentionList().length - 1); }"
                                                @keydown.up="if (mention.open) { $event.preventDefault(); mention.index = Math.max(mention.index - 1, 0); }"
                                                @keydown.escape.stop="mention.open = false"
                                                @keydown.enter.prevent="if (mention.open && mentionList().length) pickMention(mentionList()[mention.index]); else if (!$event.shiftKey) sendMessage()"
                                                class="max-h-24 min-h-9 flex-1 resize-none border-0 bg-transparent px-2 py-1.5 text-sm text-gray-700 outline-none ring-0 placeholder:text-gray-400 focus:ring-0 dark:text-gray-200"></textarea>
                                            <button type="button" @click="sendMessage()" :disabled="busy || !chatText.trim()" class="h-9 rounded-lg bg-slate-900 px-3.5 text-sm font-semibold text-white hover:bg-slate-700 disabled:opacity-50 dark:bg-blue-600"><i class="fa-solid fa-paper-plane"></i></button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                <div x-show="tab === 'files'" x-cloak class="flex min-h-0 flex-1 flex-col">
                                    <div class="min-h-[260px] flex-1 space-y-2 overflow-y-auto p-6">
                                        <div x-show="!(detail.attachments || []).length" class="rounded-lg border border-dashed border-gray-300 py-10 text-center text-sm text-gray-400 dark:border-white/15">No attachment available</div>
                                        <template x-for="(a, i) in (detail.attachments || [])" :key="i">
                                            <a :href="a.url" target="_blank" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-300 dark:border-white/[0.06] dark:bg-[#111827]">
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-white/10"><i class="fa-solid" :class="fileIcon(a.fileextension)"></i></span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-medium text-gray-900 dark:text-white" x-text="a.name + '.' + a.fileextension"></span>
                                                    <span class="block truncate text-[11px] text-gray-400"><span x-text="a.created_user"></span> <span class="mx-1">•</span> <span x-text="String(a.created_datetime || '').substring(0, 16)"></span></span>
                                                </span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-xs text-gray-300"></i>
                                            </a>
                                        </template>
                                    </div>
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                                        <p x-show="fileErr" x-text="fileErr" class="mb-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-500/10"></p>
                                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" :class="busy && 'pointer-events-none opacity-50'">
                                            <i class="fa-solid fa-paperclip text-xs"></i><span x-text="busy ? 'Uploading…' : 'Upload attachment'"></span>
                                            <input type="file" class="hidden" accept=".jpg,.jpeg,.png,video/*" @change="upload($event)">
                                        </label>
                                        <p class="mt-2 text-center text-[11px] text-gray-400">JPG, JPEG, PNG or video · max 5 MB per file</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function complaintLog() {
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const cols = ['', 'complaintnbr', 'complaintdate', 'sitename', 'storename', 'category', 'description', 'created_user', 'status'];
            // same badge look as the other lists (tinted background + border, square corners)
            const listBadge = {
                C: 'bg-yellow-200/60 text-yellow-800 dark:bg-yellow-300/40 dark:text-yellow-900 border-yellow-600/40',
                P: 'bg-blue-200/60 text-blue-800 dark:bg-blue-300/40 dark:text-blue-900 border-blue-600/40',
                W: 'bg-violet-200/60 text-violet-800 dark:bg-violet-300/40 dark:text-violet-900 border-violet-600/40',
                D: 'bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 border-green-600/40',
                X: 'bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 border-red-600/40',
            };
            let dt = null;
            return {
                counts: {}, detail: null, notes: '', err: '', busy: false, ask: null, actDate: '',
                /** Look + wording of the Process / Complete / Cancel prompt. */
                askUi() {
                    const t = {
                        process: { title: 'Process Complaint', cta: 'Submit', icon: 'fa-play', to: 'P', toLabel: 'On Progress',
                            soft: 'border-amber-100 bg-gradient-to-r from-amber-50 to-white dark:border-white/10 dark:from-amber-500/10 dark:to-transparent',
                            tile: 'bg-amber-500 ring-amber-100 dark:ring-amber-500/20', btn: 'bg-amber-500 hover:bg-amber-600', focus: 'focus:border-amber-400 focus:ring-amber-100 dark:focus:ring-amber-500/10' },
                        complete: { title: 'Complete Complaint', cta: 'Mark as Completed', icon: 'fa-check', to: 'D', toLabel: 'Completed',
                            soft: 'border-emerald-100 bg-gradient-to-r from-emerald-50 to-white dark:border-white/10 dark:from-emerald-500/10 dark:to-transparent',
                            tile: 'bg-emerald-500 ring-emerald-100 dark:ring-emerald-500/20', btn: 'bg-emerald-600 hover:bg-emerald-700', focus: 'focus:border-emerald-400 focus:ring-emerald-100 dark:focus:ring-emerald-500/10' },
                        cancel: { title: 'Cancel Complaint', cta: 'Cancel Complaint', icon: 'fa-ban', to: 'X', toLabel: 'Cancelled',
                            soft: 'border-rose-100 bg-gradient-to-r from-rose-50 to-white dark:border-white/10 dark:from-rose-500/10 dark:to-transparent',
                            tile: 'bg-rose-500 ring-rose-100 dark:ring-rose-500/20', btn: 'bg-rose-600 hover:bg-rose-700', focus: 'focus:border-rose-400 focus:ring-rose-100 dark:focus:ring-rose-500/10' },
                    }[this.ask] || {};
                    return { ...t, sub: [this.detail?.complaintnbr, this.detail?.category, this.detail?.storename].filter(Boolean).join(' · ') };
                },

                nowLocal() { const d = new Date(Date.now() - new Date().getTimezoneOffset() * 60000); return d.toISOString().slice(0, 16); },
                hasWo() { return (this.detail?.activities || []).some(a => a.type === 'Transfer to WO'); },
                async confirmAsk() {
                    if (this.ask !== 'process' && !this.notes.trim()) { this.err = 'Please fill the notes.'; return; }
                    await this.act(this.ask);
                    if (!this.err) this.ask = null;
                },
                tab: 'activity', chatText: '', fileErr: '', me: @json(auth()->user()->username),
                mention: { open: false, query: '', start: 0, index: 0 },
                /** Timeline card look per activity type (circle icon, chip, note box), like the Ticket tracking timeline. */
                tl(t) {
                    const m = {
                        Created: { circle: 'bg-blue-500', icon: 'fa-plus', title: 'Complaint Created', tag: 'Created', chip: 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-200', box: '' },
                        Process: { circle: 'bg-amber-500', icon: 'fa-play', title: 'Process Complaint', tag: 'Process', chip: 'bg-amber-100 text-amber-700', box: 'border-amber-200 bg-amber-50 text-amber-700' },
                        Complete: { circle: 'bg-emerald-500', icon: 'fa-check', title: 'Complaint Completed', tag: 'Complete', chip: 'bg-emerald-100 text-emerald-700', box: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
                        Cancel: { circle: 'bg-rose-500', icon: 'fa-ban', title: 'Complaint Cancelled', tag: 'Cancel', chip: 'bg-rose-100 text-rose-600', box: 'border-rose-200 bg-rose-50 text-rose-600' },
                        'Transfer to WO': { circle: 'bg-violet-500', icon: 'fa-share-from-square', title: 'Transfer to WO', tag: 'WO', chip: 'bg-violet-100 text-violet-700', box: 'border-violet-200 bg-violet-50 text-violet-700' },
                        Message: { circle: 'bg-indigo-500', icon: 'fa-comment', title: 'Message', tag: 'Message', chip: 'bg-indigo-100 text-indigo-700', box: 'border-indigo-200 bg-indigo-50 text-indigo-700' },
                    };
                    return m[t] || { circle: 'bg-slate-400', icon: 'fa-circle-info', title: '', tag: 'Info', chip: 'bg-slate-100 text-slate-600', box: 'border-slate-200 bg-slate-50 text-slate-600' };
                },
                esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); },
                msgHtml(text, mine) {
                    const known = new Set((this.detail?.mentionable || []).map(p => p.username.toLowerCase()).concat(this.me.toLowerCase()));
                    return this.esc(text).replace(/@([\w.]+)/g, (all, u) => known.has(u.toLowerCase())
                        ? `<span class="rounded px-1 font-semibold ${mine ? 'bg-white/25 text-white' : 'bg-blue-100 text-blue-700'}">@${u}</span>` : all);
                },
                mentionList() {
                    const q = this.mention.query.toLowerCase();
                    return (this.detail?.mentionable || []).filter(p => p.username.toLowerCase().includes(q) || p.name.toLowerCase().includes(q)).slice(0, 8);
                },
                checkMention(ev) {
                    const el = ev.target, upto = el.value.slice(0, el.selectionStart);
                    const m = upto.match(/(?:^|\s)@([\w.]*)$/);
                    if (!m) { this.mention.open = false; return; }
                    this.mention = { open: true, query: m[1], start: upto.length - m[1].length - 1, index: 0 };
                },
                pickMention(p) {
                    if (!p) return;
                    const el = this.$refs.chatInput, end = el.selectionStart;
                    this.chatText = this.chatText.slice(0, this.mention.start) + '@' + p.username + ' ' + this.chatText.slice(end);
                    this.mention.open = false;
                    this.$nextTick(() => { const pos = this.mention.start + p.username.length + 2; el.focus(); el.setSelectionRange(pos, pos); });
                },
                scrollChat() { const b = this.$refs.chatBody; if (b) b.scrollTop = b.scrollHeight; },
                async post(path, body) {
                    const isFd = body instanceof FormData;
                    const res = await fetch('{{ url('/tenant-support/complaint') }}/' + this.detail.eid + '/' + path, {
                        method: 'POST',
                        headers: Object.assign({ Accept: 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, isFd ? {} : { 'Content-Type': 'application/json' }),
                        body: isFd ? body : JSON.stringify(body),
                    });
                    const j = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(j.message || Object.values(j.errors || {})[0]?.[0] || 'Failed.');
                    return j;
                },
                async sendMessage() {
                    if (!this.chatText.trim()) return;
                    this.busy = true;
                    try {
                        const j = await this.post('message', { message: this.chatText }); this.detail.messages = j.messages; this.detail.activities = j.activities; this.chatText = '';
                        this.$nextTick(() => this.scrollChat());
                    } catch (e) { alert(e.message); } finally { this.busy = false; }
                },
                async upload(ev) {
                    const file = ev.target.files[0]; if (!file) return;
                    const ext = file.name.split(".").pop().toLowerCase();
                    if (!(["jpg", "jpeg", "png"].includes(ext) || file.type.startsWith("video/"))) { this.fileErr = "Only image (JPG, JPEG, PNG) or video files are allowed."; ev.target.value = ""; return; }
                    if (file.size > 5 * 1024 * 1024) { this.fileErr = "File size must not exceed 5 MB."; ev.target.value = ""; return; }
                    const fd = new FormData(); fd.append('file', file);
                    this.busy = true; this.fileErr = '';
                    try { this.detail.attachments = (await this.post('upload', fd)).attachments; }
                    catch (e) { this.fileErr = e.message; } finally { this.busy = false; ev.target.value = ''; }
                },
                fileIcon(ext) {
                    ext = String(ext || '').toLowerCase();
                    return ext === 'pdf' ? 'fa-file-pdf' : (['xls', 'xlsx', 'csv'].includes(ext) ? 'fa-file-excel' : (['doc', 'docx'].includes(ext) ? 'fa-file-word' : 'fa-file-image'));
                },
                async open(r) {
                    this.detail = r; this.notes = ''; this.err = ''; this.ask = null; this.tab = 'activity'; this.chatText = ''; this.fileErr = '';
                    try {
                        const res = await fetch('{{ url('/tenant-support/complaint') }}/' + r.eid, { headers: { Accept: 'application/json' } });
                        if (res.ok && this.detail && this.detail.eid === r.eid) this.detail = (await res.json()).data;
                    } catch (e) { /* list row stays as fallback */ }
                },
                async act(action) {
                    this.err = ''; this.busy = true;
                    try {
                        const res = await fetch('{{ url('/tenant-support/complaint') }}/' + this.detail.eid + '/action', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ action, description: this.notes, activity_date: action === 'process' ? (this.actDate || null) : null }),
                        });
                        const j = await res.json();
                        if (!res.ok) { this.err = j.message || 'Failed.'; return; }
                        this.detail = j.data; this.notes = ''; this.actDate = '';
                        this.reload();
                    } catch (e) { this.err = 'Network error.'; } finally { this.busy = false; }
                },
                f: { status: 'C', year: '{{ now()->year }}', siteid: '', category: '' },
                init() {
                    const self = this;
                    dt = $('#complaintTable').DataTable({
                        serverSide: true,
                        processing: true,
                        searchDelay: 400,
                        pageLength: 15,
                        lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
                        order: [],
                        dom: '<"dt-toolbar"l f>rtip',
                        ajax(d, cb) {
                            const o = d.order[0];
                            const p = new URLSearchParams({
                                ...self.f,
                                search: d.search.value || '',
                                sort: o ? cols[o.column] : '',
                                dir: o ? o.dir : 'desc',
                                page: Math.floor(d.start / d.length) + 1,
                                per_page: d.length,
                            });
                            fetch('{{ route('tenantsupport.complaint.data') }}?' + p, { headers: { Accept: 'application/json' } })
                                .then(r => r.json())
                                .then(j => {
                                    self.counts = j.counts;
                                    cb({ draw: d.draw, recordsTotal: j.total, recordsFiltered: j.total, data: j.data });
                                })
                                .catch(() => cb({ draw: d.draw, recordsTotal: 0, recordsFiltered: 0, data: [], error: 'Failed to load' }));
                        },
                        columns: [
                            { data: null, orderable: false, className: 'text-center dt-body-center', render: (v, t, r) => r.status === 'C'
                                ? '<button type="button" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"><i class="fa-solid fa-play mr-1.5 text-[10px]"></i>Process</button>' : '' },
                            { data: 'complaintnbr', className: 'text-center dt-body-center', render: v => `<span class="inline-flex min-w-[120px] items-center justify-center whitespace-nowrap rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-gray-700">${esc(v)}</span>` },
                            { data: 'complaintdate', render: v => esc(String(v || '').substring(0, 16)) },
                            { data: 'sitename', render: (v, t, r) => esc(v || r.siteid || '—') },
                            { data: 'storename', render: v => esc(v || '—') },
                            { data: 'category', render: v => esc(v || '—') },
                            { data: 'description', render: v => `<span class="block max-w-xs truncate" title="${esc(v)}">${esc(v)}</span>` },
                            { data: 'created_user', render: v => esc(v || '—') },
                            { data: 'status', render: (v, t, r) => `<span class="${listBadge[v] || listBadge.D} pointer-events-none inline-block w-32 rounded border px-4 py-2 text-center font-semibold">${esc(r.status_label)}</span>` },
                        ],
                    });
                    $('#complaintTable tbody').on('click', 'tr', function () {
                        const r = dt.row(this).data();
                        if (r) self.open(r);
                    });
                },
                reload() { dt && dt.ajax.reload(null, true); },
                // The Action (Process) column only matters for new jobs (status C).
                setStatus(s) { this.f.status = s; dt && dt.column(0).visible(s === 'C'); this.reload(); },
                badge(s) { return listBadge[s] || 'bg-slate-100 text-slate-600'; },
            };
        }
    </script>
</x-app-layout>
