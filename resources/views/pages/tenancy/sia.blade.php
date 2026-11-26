<x-app-layout>
    @php
        $cards = [
            ['ALL', 'All', '📦', 'all'],
            ['P', 'On Progress', '⏳', 'yellow'],
            ['C', 'Completed', '✅', 'green'],
            ['H', 'Hold / Revise', '✏️', 'blue'],
            ['R', 'Rejected', '⛔', 'red'],
            ['X', 'Cancelled', '❌', 'cancel'],
        ];
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="siaPage()">
        <div class="mb-3 grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($cards as [$key, $label, $icon, $tone])
                <x-status-card :key="$key" :label="$label" :icon="$icon" :tone="$tone" />
            @endforeach
        </div>

        <x-ts-list-assets />

        <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <x-ts-list-header title="SIA Request" hint="Event permit requests from tenants and their approval" icon="fa-champagne-glasses">
                <x-ts-filter icon="fa-calendar" class="w-40">
                    <select x-model="f.year" @change="reload()" class="ts-s2" title="SIA year">
                        <option value="">All Years</option>
                        @foreach ($years as $y)<option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>@endforeach
                    </select>
                </x-ts-filter>
                <x-ts-filter icon="fa-building" class="w-56">
                    <select x-model="f.siteid" @change="reload()" class="ts-s2" title="Site">
                        <option value="">All Sites</option>
                        @foreach ($sites as $s)<option value="{{ $s->siteid }}">{{ $s->sitename }}</option>@endforeach
                    </select>
                </x-ts-filter>
            </x-ts-list-header>

            <div class="relative overflow-x-auto" x-ignore>
                <table id="siaTable" class="ts-rows w-full min-w-full border-separate border-spacing-0 text-sm" style="width:100%">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">SIA No</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Tenant</th>
                            <th class="px-4 py-3 text-left font-medium">Person in Charge</th>
                            <th class="px-4 py-3 text-left font-medium">Event Type</th>
                            <th class="px-4 py-3 text-left font-medium">Event Period</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>


        <!-- Detail modal (same layout as the VPL/Receive view modal) -->
        <div x-show="detail" x-cloak @keydown.escape.window="closeDetail()"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm dark:bg-black/70" @click="closeDetail()"
                x-show="detail" x-transition.opacity.duration.200ms></div>

            <div x-show="detail" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-4 scale-[0.98] opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                class="relative z-10 flex max-h-[95vh] w-full max-w-[90rem] flex-col overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 dark:bg-[#0f172a] dark:ring-white/10">
                <template x-if="detail">
                    <div class="relative flex flex-col">

                        {{-- Header --}}
                        <div class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-white px-6 py-3.5 dark:border-white/[0.06] dark:bg-[#0f172a]">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-champagne-glasses"></i></span>
                                <div class="min-w-0">
                                <h2 class="text-base! font-bold tracking-tight text-gray-800 dark:text-white" x-text="'SIA — ' + detail.data.sianbr"></h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">SIA information &amp; approval workflow.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- Two PDFs per SIA: the permit (Permohonan Ijin Acara) and the document detail --}}
                                <div class="relative" x-data="{ pdfMenu: false }" @click.outside="pdfMenu = false" @keydown.escape.stop="pdfMenu = false">
                                    <button type="button" @click="pdfMenu = !pdfMenu" title="Print PDF"
                                        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                                        <i class="fa-solid fa-print text-base"></i><i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </button>
                                    <div x-show="pdfMenu" x-cloak x-transition class="absolute right-0 top-12 z-40 w-64 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-[#0f172a]">
                                        <a :href="permitUrl()" target="_blank" @click="pdfMenu = false" class="flex items-start gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-white/5">
                                            <i class="fa-solid fa-file-signature mt-0.5 text-blue-600"></i>
                                            <span><span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">SIA Permit</span><span class="block text-xs text-slate-400">Permohonan Ijin Acara with signatures &amp; QR</span></span>
                                        </a>
                                        <a :href="printUrl()" target="_blank" @click="pdfMenu = false" class="flex items-start gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-white/5">
                                            <i class="fa-solid fa-file-lines mt-0.5 text-slate-500"></i>
                                            <span><span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Document Detail</span><span class="block text-xs text-slate-400">Full record, approvals &amp; attachments</span></span>
                                        </a>
                                    </div>
                                </div>
                                <button type="button" @click="copyLink()" title="Copy link to this SIA"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                                    <i class="fa-solid fa-link text-base"></i>
                                </button>
                                <button type="button" @click="showChat = !showChat" title="Messages"
                                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                                    <i class="fa-regular fa-comments text-base"></i>
                                    <span x-show="detail.messages.length" x-text="detail.messages.length" class="absolute -right-1 -top-1 min-w-4 rounded-full bg-blue-500 px-1 text-center text-[10px] font-semibold leading-4 text-white"></span>
                                </button>
                                <button type="button" @click="closeDetail()"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05]">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="grid grid-cols-1 gap-4 bg-slate-50 p-4 dark:bg-[#0b1220] lg:grid-cols-[1.6fr_0.6fr]">

                            {{-- LEFT --}}
                            <div class="space-y-4">
                                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                                    {{-- Hero --}}
                                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-3 dark:border-white/10">
                                        <div class="flex min-w-0 items-center gap-4">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-base font-bold uppercase text-white shadow-sm" x-text="(detail.data.created_user || '?').charAt(0)"></div>
                                            <div class="min-w-0">
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Created By</div>
                                                <div class="truncate text-base font-semibold text-slate-900 dark:text-white" x-text="detail.data.created_user || '—'"></div>
                                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><i class="fa-regular fa-clock mr-1"></i><span x-text="String(detail.data.siadate || '').substring(0, 16)"></span></div>
                                            </div>
                                        </div>
                                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold ring-1 ring-inset" :class="modalBadge(detail.data.status)">
                                            <span class="h-2 w-2 rounded-full bg-current"></span><span x-text="detail.data.status_label"></span>
                                        </span>
                                    </div>

                                    {{-- Grouped facts --}}
                                    <div class="divide-y divide-slate-100 dark:divide-white/10"><div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-6">
                                        <template x-for="g in infoGroups(detail.data)" :key="g.title">
                                            <div class="rounded-xl border border-gray-100 bg-white p-4 dark:border-white/10 dark:bg-white/[0.02]"
                                                :class="g.span">
                                                <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3 dark:border-white/10">
                                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm"><i class="fa-solid text-xs" :class="g.icon"></i></span>
                                                    <span class="text-sm font-bold tracking-tight text-slate-800 dark:text-white" x-text="g.title"></span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-x-6 gap-y-3" :class="g.cols">
                                                    <template x-for="[label, val, icon] in g.items" :key="label">
                                                        <div class="flex min-w-0 items-start gap-3">
                                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-white/[0.06] dark:text-slate-400"><i class="fa-solid text-[11px]" :class="icon"></i></span>
                                                            <div class="min-w-0">
                                                                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="label"></div>
                                                                <div class="break-words text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="val || '—'"></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        </div>{{-- Long text blocks --}}
                                        <div class="grid grid-cols-1 gap-3 px-4 pb-4 pt-1">
                                            <template x-for="[label, val, icon] in textFields(detail.data)" :key="label">
                                                <div class="rounded-lg bg-slate-50 px-3.5 py-3 dark:bg-white/[0.03]">
                                                    <div class="mb-2 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"><i class="fa-solid text-blue-400" :class="icon"></i><span x-text="label"></span></div>
                                                    <template x-if="label === 'Workers' && val">
                                                        <ol class="flex flex-wrap gap-x-4 gap-y-1">
                                                            <template x-for="(w, wi) in splitList(val)" :key="wi">
                                                                <li class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-600 dark:bg-blue-500/20" x-text="wi + 1"></span><span x-text="w"></span></li>
                                                            </template>
                                                        </ol>
                                                    </template>
                                                    <div x-show="label !== 'Workers' || !val" class="whitespace-pre-line text-sm text-slate-700 dark:text-slate-200" x-text="val || '—'"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/10">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span>
                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Attachments</h3>
                                        </div>
                                        <template x-if="detail.can.upload">
                                            <div>
                                                <input type="file" x-ref="attachInput" class="hidden" accept=".jpg,.jpeg,.png,video/*" title="JPG, JPEG, PNG or video, max 5 MB per file" @change="upload($event)">
                                                <button type="button" @click="$refs.attachInput.click()" :disabled="busy"
                                                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-dashed border-blue-300 px-3.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 disabled:opacity-50 dark:border-blue-500/40 dark:text-blue-300 dark:hover:bg-blue-500/10">
                                                    <i class="fa-solid fa-plus"></i> Add Attachment
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="!detail.attachments.length" class="p-4 text-sm text-slate-400">No attachments.</div>
                                    <div class="divide-y divide-slate-100 dark:divide-white/10">
                                        <template x-for="(f, i) in detail.attachments" :key="i">
                                            <div class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                                                <a :href="f.url" target="_blank" rel="noopener" class="flex min-w-0 items-center gap-2 text-blue-600 hover:underline dark:text-blue-400">
                                                    <i class="fa-solid" :class="fileIcon(f.fileextension)"></i>
                                                    <span class="truncate" x-text="f.name + '.' + f.fileextension"></span>
                                                </a>
                                                <span class="shrink-0 text-xs text-slate-400" x-text="(f.created_user ? f.created_user + ' · ' : '') + String(f.created_datetime || '').substring(0, 16)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT --}}
                            <div class="space-y-4">
                                {{-- Reason (Reject / Revise) --}}
                                <div x-show="detail.reason" class="overflow-hidden rounded-2xl border border-yellow-200 bg-yellow-50 dark:border-yellow-500/20 dark:bg-yellow-500/10">
                                    <div class="border-b border-yellow-100 px-5 py-2 dark:border-yellow-500/20">
                                        <h3 class="text-sm font-semibold uppercase tracking-wider text-yellow-700 dark:text-yellow-300" x-text="detail.data.status === 'R' ? 'Rejection Reason' : 'Revision Reason'"></h3>
                                    </div>
                                    <div class="p-5 text-sm leading-relaxed text-yellow-900 dark:text-yellow-100" x-text="detail.reason"></div>
                                </div>

                                <template x-if="banner(detail.data.status)">
                                    <div class="rounded-xl border px-4 py-3 text-sm font-medium" :class="banner(detail.data.status).cls">
                                        <i class="fa-solid mr-2 shrink-0" :class="banner(detail.data.status).icon"></i><span x-text="banner(detail.data.status).text"></span>
                                    </div>
                                </template>

                                {{-- Extend approval: the approver whose turn it is adds a sub-level approver (SIA On Progress) --}}
                                <div x-show="detail.can.extend">
                                    <button type="button" @click="startAddApprover()" class="h-10 w-full rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"><i class="fa-solid fa-user-plus mr-1"></i> Extend Approval</button>
                                </div>

                                {{-- Approve / Revise / Reject --}}
                                <div x-show="detail.can.approve" class="space-y-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <button type="button" @click="startAction('A')" class="h-10 flex-1 rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"><i class="fa-solid fa-check mr-1"></i> Approve</button>
                                        <button type="button" @click="startAction('D')" class="h-10 flex-1 rounded-lg bg-amber-400 px-3 text-sm font-semibold text-amber-950 shadow-sm transition hover:bg-amber-500"><i class="fa-solid fa-rotate-left mr-1"></i> Revise</button>
                                        <button type="button" @click="startAction('R')" class="h-10 flex-1 rounded-lg bg-red-600 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700"><i class="fa-solid fa-xmark mr-1"></i> Reject</button>
                                    </div>
                                </div>

                                <div x-show="!detail.approvals.length" class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-500 dark:border-white/10 dark:bg-white/[0.02]">No approval records.</div>

                                <div x-show="detail.approvals.length" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                                    <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-3 dark:border-white/10">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-route"></i></span>
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Approval Workflow</h3>
                                    </div>
                                    <div class="space-y-4 p-4">
                                        <template x-for="(a, i) in detail.approvals" :key="i">
                                            <div class="relative flex gap-4">
                                                <div class="flex flex-col items-center">
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="step(a).box">
                                                        <i class="fa-solid text-xs" :class="step(a).icon"></i>
                                                    </div>
                                                    <div x-show="i < detail.approvals.length - 1" class="mt-1 min-h-10 w-px flex-1 bg-slate-200 dark:bg-white/10"></div>
                                                </div>
                                                <div class="min-w-0 flex-1 pb-6" :class="i === detail.approvals.length - 1 ? 'pb-0!' : ''">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="levelLabel(i)"></p>
                                                            <p class="mt-1 break-words text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="a.username"></p>
                                                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500" x-show="a.aprvdateafter" x-text="a.aprvdateafter"></p>
                                                        </div>
                                                        <span class="inline-flex shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold" :class="step(a).pill" x-text="step(a).label"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex justify-end border-t border-gray-100 bg-gray-50/70 px-6 py-3.5 dark:border-white/[0.06] dark:bg-white/[0.02]">
                            <button type="button" @click="closeDetail()" class="inline-flex h-10 items-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">Close</button>
                        </div>

                        {{-- Discussion / messages panel --}}
                        <div x-show="showChat" x-transition
                            class="absolute bottom-16 right-0 z-30 w-[380px] max-w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-white/10">
                                <h3 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100"><i class="fa-regular fa-comments text-blue-600"></i>Messages</h3>
                                <button type="button" @click="showChat = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div x-ref="chatBody" class="h-[360px] space-y-4 overflow-y-auto bg-slate-50 p-4 dark:bg-[#0b1220]">
                                <div x-show="!detail.messages.length" class="pt-10 text-center text-sm text-slate-400">No messages yet.</div>
                                <template x-for="(m, i) in detail.messages" :key="i">
                                    <div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="m.name || m.created_user"></span>
                                            <span x-show="m.messagetype !== 'Message'" class="rounded px-1.5 py-0.5 text-[10px] font-semibold"
                                                :class="m.messagetype === 'Reject' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700'" x-text="m.messagetype"></span>
                                            <span class="text-slate-400" x-text="String(m.created_datetime || '').substring(0, 16)"></span>
                                        </div>
                                        <div class="mt-1 whitespace-pre-line break-words rounded-lg bg-white p-3 text-sm text-slate-700 shadow-sm dark:bg-white/5 dark:text-slate-200" x-html="msgHtml(m.message)"></div>
                                    </div>
                                </template>
                            </div>
                            <div class="relative flex items-end gap-2 border-t border-slate-200 p-3 dark:border-white/10">
                                {{-- @mention picker: creator + approvers of this SIA --}}
                                <div x-show="mention.open && mentionList().length" x-cloak
                                    class="absolute bottom-full left-3 right-3 mb-1 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-[#0f172a]">
                                    <template x-for="(p, i) in mentionList()" :key="p.username">
                                        <button type="button" @mousedown.prevent="pickMention(p)" @mouseenter="mention.index = i"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm"
                                            :class="i === mention.index ? 'bg-blue-50 dark:bg-blue-500/10' : ''">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold uppercase text-blue-600" x-text="p.username.charAt(0)"></span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate font-medium text-slate-800 dark:text-slate-100" x-text="p.name"></span>
                                                <span class="block truncate text-[11px] text-slate-400" x-text="'@' + p.username"></span>
                                            </span>
                                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold" :class="p.role === 'Creator' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'" x-text="p.role"></span>
                                        </button>
                                    </template>
                                </div>
                                <textarea x-ref="chatInput" x-model="chatText" rows="1" placeholder="Write message... type @ to mention"
                                    @input="checkMention($event)" @click="checkMention($event)" @blur="mention.open = false"
                                    @keydown.down="if (mention.open) { $event.preventDefault(); mention.index = Math.min(mention.index + 1, mentionList().length - 1); }"
                                    @keydown.up="if (mention.open) { $event.preventDefault(); mention.index = Math.max(mention.index - 1, 0); }"
                                    @keydown.escape.stop="mention.open = false"
                                    @keydown.enter.prevent="if (mention.open && mentionList().length) pickMention(mentionList()[mention.index]); else if (!$event.shiftKey) sendMessage()"
                                    class="max-h-24 min-h-9 flex-1 resize-none rounded-lg border border-gray-200 bg-white p-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-slate-200"></textarea>
                                <button type="button" @click="sendMessage()" :disabled="busy || !chatText.trim()"
                                    class="h-9 rounded-lg bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50"><i class="fa-solid fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function siaPage() {
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const fmt = d => d ? String(d).substring(0, 10) : '-';
            const badgeCls = { C: 'bg-emerald-50 text-emerald-700', P: 'bg-amber-50 text-amber-700', R: 'bg-rose-50 text-rose-700', H: 'bg-blue-50 text-blue-700', X: 'bg-gray-100 text-gray-600' };
            // status pill colours follow the status cards
            const tones = { P: 'yellow', C: 'green', H: 'blue', R: 'red', X: 'red', E: 'sky' };
            const cols = ['sianbr', 'siadate', 'sitename', 'storename', 'requestpenanggungjawab', 'eventtype', 'startdate', 'created_user', 'status'];
            let dt = null;

            return {
                counts: {}, detail: null, eid: null,
                showChat: false, chatText: '', actionMode: null, actionNotes: '', actionError: '', busy: false,
                f: { siteid: '', status: 'P', year: '{{ now()->year }}' },

                init() {
                    const self = this;
                    dt = $('#siaTable').DataTable({
                        serverSide: true,
                        processing: true,
                        searchDelay: 400,
                        pageLength: 15,
                        lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
                        order: [],
                        dom: '<"dt-toolbar"l B f>rtip',
                        buttons: [
                            { extend: 'excelHtml5', text: '↓ Excel', action: () => self.exportFile('xlsx') },
                            { extend: 'csvHtml5', text: '↓ CSV', action: () => self.exportFile('csv') },
                        ],
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
                            fetch(`{{ route('tenantsupport.sia.data') }}?${p}`, { headers: { Accept: 'application/json' } })
                                .then(r => r.json())
                                .then(j => {
                                    self.counts = j.counts;
                                    cb({ draw: d.draw, recordsTotal: j.total, recordsFiltered: j.total, data: j.data });
                                })
                                .catch(() => cb({ draw: d.draw, recordsTotal: 0, recordsFiltered: 0, data: [], error: 'Failed to load' }));
                        },
                        createdRow(row, r) { row.dataset.eid = r.eid; },
                        columns: [
                            { data: 'sianbr', render: v => `<span class="${window.listCls.doc}">${window.listDocText(esc(v))}</span>` },
                            { data: 'siadate', render: fmt },
                            { data: 'sitename', render: esc },
                            { data: 'storename', render: (v, t, r) => `${esc(v)} <span class="text-xs text-gray-400">${esc([r.floor, r.unit].filter(Boolean).join(' / '))}</span>` },
                            { data: 'requestpenanggungjawab', render: esc },
                            { data: 'eventtype', render: esc },
                            { data: 'startdate', render: (v, t, r) => `${fmt(v)} – ${fmt(r.enddate)}` + (r.totalextend > 0 ? ` <span class="ml-1 text-xs text-amber-600">(+${esc(r.totalextend)} ext)</span>` : '') },
                            { data: 'created_user', render: esc },
                            { data: 'status', render: (v, t, r) => window.listBadge(tones[v] || 'slate', esc(r.status_label), true) },
                        ],
                    });
                    $('#siaTable tbody').on('click', 'tr', function () {
                        if (this.dataset.eid) self.open(this.dataset.eid);
                    });

                    // Deep link (/tenant-support/sia/{eid}) opens that SIA; the browser back / forward buttons follow the address.
                    const startEid = @json($openEid ?? null);
                    if (startEid) this.open(startEid, false);
                    window.addEventListener('popstate', () => {
                        const m = location.pathname.match(/\/tenant-support\/sia\/([^\/]+)$/);
                        if (m && !['data', 'export'].includes(m[1])) this.open(m[1], false); else this.closeDetail(false);
                    });
                },
                reload() { dt && dt.ajax.reload(null, true); },
                setStatus(s) { this.f.status = s; this.reload(); },
                exportFile(format) {
                    const o = dt.order()[0];
                    const p = new URLSearchParams({ ...this.f, search: dt.search(), sort: o ? cols[o[0]] : '', dir: o ? o[1] : 'desc', format });
                    window.location = `{{ route('tenantsupport.sia.export') }}?${p}`;
                },
                // Every SIA has its own link: /tenant-support/sia/{eid}. Opening a popup moves the address bar there; closing goes back to the list.
                listUrl: `{{ url('/tenant-support/sia') }}`,
                async open(eid, push = true) {
                    const r = await fetch(`${this.listUrl}/${eid}`, { headers: { Accept: 'application/json' } });
                    if (!r.ok) {
                        window.toastr?.error('This SIA could not be opened.');
                        if (location.pathname !== new URL(this.listUrl).pathname) history.replaceState({}, '', this.listUrl);
                        return;
                    }
                    this.eid = eid; this.showChat = false; this.actionMode = null; this.actionNotes = ''; this.actionError = ''; this.chatText = '';
                    this.detail = await r.json();
                    if (push && location.pathname !== `${new URL(this.listUrl).pathname}/${eid}`) history.pushState({}, '', `${this.listUrl}/${eid}`);
                },
                closeDetail(push = true) {
                    this.detail = null; this.showChat = false; this.actionMode = null;
                    if (push === true && location.pathname !== new URL(this.listUrl).pathname) history.pushState({}, '', this.listUrl);
                },
                async copyLink() {
                    try { await navigator.clipboard.writeText(`${this.listUrl}/${this.eid}`); window.toastr?.success('Link copied.'); }
                    catch (e) { window.prompt('Copy this link:', `${this.listUrl}/${this.eid}`); }
                },
                printUrl() { return `{{ url('/tenant-support/sia') }}/${this.eid}/print`; },
                permitUrl() { return `{{ url('/tenant-support/sia') }}/${this.eid}/permit`; },
                async post(path, body) {
                    const isForm = body instanceof FormData;
                    const r = await fetch(`{{ url('/tenant-support/sia') }}/${this.eid}/${path}`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', ...(isForm ? {} : { 'Content-Type': 'application/json' }) },
                        body: isForm ? body : JSON.stringify(body),
                    });
                    const j = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(j.message || (j.errors ? Object.values(j.errors)[0][0] : 'Request failed'));
                    return j;
                },
                // Approve / Revise / Reject use the same SweetAlert popups as the voucher modules.
                async startAction(mode) {
                    const cfg = {
                        A: { title: 'Approve Document?', text: 'Are you sure you want to approve this SIA?', confirm: 'Yes, Approve', color: '#10b981', done: 'Document approved!' },
                        R: { title: 'Reject Document', label: 'Rejection Reason', ph: 'Please explain why you are rejecting this document...', confirm: 'Reject', color: '#ef4444', msg: 'Please provide a reason for rejection.', done: 'Document rejected.' },
                        D: { title: 'Request Revision', label: 'Revision Notes', ph: 'Please provide feedback for the requester to revise...', confirm: 'Request Revision', color: '#f59e0b', msg: 'Please provide revision notes.', done: 'Revision requested.' },
                    }[mode];
                    const opts = {
                        title: cfg.title, showCancelButton: true, confirmButtonText: cfg.confirm, cancelButtonText: 'Cancel',
                        confirmButtonColor: cfg.color, cancelButtonColor: '#94a3b8', reverseButtons: true,
                        showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
                        preConfirm: async (val) => {
                            try { return await this.post('action', { action: mode, notes: typeof val === 'string' ? val.trim() : '' }); }
                            catch (e) { Swal.showValidationMessage(e.message); return false; }
                        },
                    };
                    if (mode === 'A') Object.assign(opts, { text: cfg.text, icon: 'question' });
                    else Object.assign(opts, { input: 'textarea', inputLabel: cfg.label, inputPlaceholder: cfg.ph, inputValidator: v => (!v || !v.trim()) ? cfg.msg : undefined });

                    const r = await Swal.fire(opts);
                    if (r.isConfirmed && r.value) {
                        this.detail = r.value;
                        this.reload();            // refresh list + status cards
                        window.toastr?.success(cfg.done);
                    }
                },
                // Extend Approval: pick one more approver (added at the end of the chain) + a reason.
                async startAddApprover() {
                    let users = [];
                    try {
                        const r = await fetch(`{{ url('/tenant-support/sia') }}/${this.eid}/approver-options`, { headers: { Accept: 'application/json' } });
                        if (!r.ok) throw new Error();
                        users = (await r.json()).data;
                    } catch (e) { window.toastr?.error('Could not load the approver list.'); return; }
                    if (!users.length) { window.toastr?.info('There is no one left to add.'); return; }

                    // The level the new approver will get (e.g. current Level 1 -> "1.1"), for the hint line.
                    const list = this.detail.approvals, real = list.filter(a => a.accesstype !== 'Submit');
                    const cur = real.find(a => Number(a.progressapproval) === 1 && a.aprvresult === 'P');
                    let next = '';
                    if (cur) {
                        const base = Math.floor(Number(cur.urutan));
                        const bases = [...new Set(real.map(a => Math.floor(Number(a.urutan))))].sort((p, q) => p - q);
                        next = `${bases.indexOf(base) + 1}.${real.filter(a => Math.floor(Number(a.urutan)) === base && Number(a.urutan) > base).length + 1}`;
                    }
                    const initials = n => String(n || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
                    let picked = null, lastDept = null;
                    const rows = users.map(u => {
                        const head = (u.department || 'No department') !== lastDept
                            ? `<div class="apv-grp" data-grp>${esc(u.department || 'No department')}${u.same_dept ? '<span>Same department as this SIA</span>' : ''}</div>` : '';
                        lastDept = u.department || 'No department';
                        return head + `<button type="button" class="apv-item" data-user="${esc(u.username)}" data-name="${esc(u.name)}" data-dept="${esc(u.department || '')}"
                                data-s="${esc((u.name + ' ' + u.username + ' ' + (u.department || '')).toLowerCase())}">
                            <span class="apv-av">${esc(initials(u.name))}</span>
                            <span class="apv-tx"><b>${esc(u.name)}</b><small>${esc(u.username)}</small></span>
                            <span class="apv-dp ${u.same_dept ? 'same' : ''}">${esc(u.department || '—')}</span>
                            <i class="fa-solid fa-circle-check apv-ck"></i>
                        </button>`;
                    }).join('');

                    const r = await Swal.fire({
                        title: 'Extend Approval', width: 700, showCancelButton: true, confirmButtonText: 'Add Approver', cancelButtonText: 'Cancel',
                        confirmButtonColor: '#56b1f3', cancelButtonColor: '#94a3b8', reverseButtons: true, focusConfirm: false,
                        showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
                        html: `<style>
                                .apv{text-align:left}.apv-hint{margin:0 0 12px;font-size:13px;line-height:1.5;color:#64748b;text-align:center}
                                .apv-hint b{color:#3193da}
                                .apv-search{position:relative;margin-bottom:8px}.apv-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px}
                                .apv-search input{width:100%;box-sizing:border-box;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px 10px 34px;font-size:14px;outline:none;color:#334155;background:#fff}
                                .apv-search input:focus{border-color:#56b1f3;box-shadow:0 0 0 3px rgba(86, 177, 243,.15)}
                                .apv-list{max-height:320px;overflow-y:auto;overflow-x:hidden;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;padding:4px}
                                .apv-grp{position:sticky;top:-4px;z-index:1;background:#f1f5f9;border-radius:8px;margin:4px 0;padding:6px 10px;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#64748b;display:flex;justify-content:space-between;gap:8px}
                                .apv-grp span{font-weight:600;letter-spacing:0;text-transform:none;color:#3193da}
                                .apv-item{display:flex;align-items:center;gap:10px;width:100%;box-sizing:border-box;text-align:left;border:1px solid transparent;background:transparent;border-radius:10px;padding:8px 10px;cursor:pointer;transition:background .12s}
                                .apv-item:hover{background:#fff;border-color:#e2e8f0}.apv-item.on{background:#e3f3ff;border-color:#a0d7ff}
                                .apv-av{flex:none;width:34px;height:34px;border-radius:50%;background:#d1ecff;color:#1c71ae;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center}
                                .apv-item.on .apv-av{background:#3193da;color:#fff}
                                .apv-tx{flex:1;min-width:0;display:flex;flex-direction:column;line-height:1.25}.apv-tx b{font-size:14px;color:#1e293b;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                .apv-tx small{font-size:12px;color:#94a3b8}
                                .apv-dp{flex:none;max-width:200px;font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px;background:#e2e8f0;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                .apv-dp.same{background:#dcfce7;color:#166534}
                                .apv-ck{flex:none;color:#3193da;font-size:16px;display:none}.apv-item.on .apv-ck{display:inline-block}
                                .apv-none{padding:22px;text-align:center;font-size:13px;color:#94a3b8;display:none}
                                .apv-chosen{margin:10px 0 8px;font-size:13px;color:#64748b;min-height:20px}.apv-chosen b{color:#1c71ae}
                                .apv-notes{width:100%;box-sizing:border-box;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:14px;min-height:72px;resize:vertical;outline:none;font-family:inherit;color:#334155;background:#fff}
                                .apv-notes:focus{border-color:#56b1f3;box-shadow:0 0 0 3px rgba(86, 177, 243,.15)}
                               </style>
                               <div class="apv">
                                 <p class="apv-hint">${next ? `The new approver becomes <b>Level ${esc(next)}</b>` : 'The new approver becomes a sub-level'}, right after your current level, and must approve before the next level.<br>Showing active users of <b>${esc(this.detail.data.sitename)}</b> only.</p>
                                 <div class="apv-search"><i class="fa-solid fa-magnifying-glass"></i><input id="apvSearch" type="text" placeholder="Search name, username or department..." autocomplete="off"></div>
                                 <div class="apv-list" id="apvList">${rows}<div class="apv-none" id="apvNone">No one matches your search.</div></div>
                                 <div class="apv-chosen" id="apvChosen">No approver selected yet.</div>
                                 <textarea id="apvNotes" class="apv-notes" placeholder="Reason for adding this approver..."></textarea>
                               </div>`,
                        didOpen: () => {
                            const listEl = document.getElementById('apvList'), search = document.getElementById('apvSearch'), chosen = document.getElementById('apvChosen');
                            listEl.addEventListener('click', ev => {
                                const b = ev.target.closest('.apv-item'); if (!b) return;
                                listEl.querySelectorAll('.apv-item.on').forEach(x => x.classList.remove('on'));
                                b.classList.add('on'); picked = b.dataset.user;
                                chosen.innerHTML = `Selected: <b>${esc(b.dataset.name)}</b>${b.dataset.dept ? ' · ' + esc(b.dataset.dept) : ''}`;
                            });
                            search.addEventListener('input', () => {
                                const q = search.value.trim().toLowerCase(); let any = false, grp = null, grpShown = false;
                                [...listEl.children].forEach(el => {
                                    if (el.hasAttribute('data-grp')) { grp = el; grpShown = false; el.style.display = 'none'; }
                                    else if (el.classList.contains('apv-item')) {
                                        const show = !q || el.dataset.s.includes(q);
                                        el.style.display = show ? '' : 'none';
                                        if (show) { any = true; if (grp) grp.style.display = ''; }
                                    }
                                });
                                document.getElementById('apvNone').style.display = any ? 'none' : 'block';
                            });
                            search.focus();
                        },
                        preConfirm: async () => {
                            const username = picked, notes = document.getElementById('apvNotes').value.trim();
                            if (!username) { Swal.showValidationMessage('Please select an approver.'); return false; }
                            if (!notes) { Swal.showValidationMessage('Please fill the reason.'); return false; }
                            try { return await this.post('approver', { username, notes }); }
                            catch (e) { Swal.showValidationMessage(e.message); return false; }
                        },
                    });
                    if (r.isConfirmed && r.value) {
                        this.detail = r.value;
                        this.reload();
                        window.toastr?.success('Approver added.');
                    }
                },
                // Approval Level n (n.1, n.2 ... for approvers added through Extend Approval)
                levelLabel(i) {
                    const list = this.detail.approvals, a = list[i];
                    if (a.accesstype === 'Submit') return 'Submit';
                    const base = Math.floor(Number(a.urutan)), frac = Math.round((Number(a.urutan) - base) * 10);
                    const bases = [...new Set(list.filter(x => x.accesstype !== 'Submit').map(x => Math.floor(Number(x.urutan))))].sort((p, q) => p - q);
                    return 'Approval Level ' + (bases.indexOf(base) + 1) + (frac ? '.' + frac : '') + (a.conditiontype === 'Extend' ? ' · added by ' + a.created_user : '');
                },
                // ---- @mention (creator + approvers) ----
                mention: { open: false, query: '', start: 0, index: 0 },
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
                // escape first, then highlight only real @mentions of this SIA's people
                msgHtml(text) {
                    const known = new Set((this.detail?.mentionable || []).map(p => p.username.toLowerCase()));
                    return esc(text).replace(/@([\w.]+)/g, (all, u) => known.has(u.toLowerCase())
                        ? `<span class="rounded bg-blue-100 px-1 font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">@${u}</span>` : all);
                },
                async sendMessage() {
                    if (!this.chatText.trim()) return;
                    this.busy = true;
                    try {
                        const j = await this.post('message', { message: this.chatText });
                        this.detail.messages = j.messages; this.chatText = '';
                        this.$nextTick(() => { const b = this.$refs.chatBody; if (b) b.scrollTop = b.scrollHeight; });
                    } catch (e) { alert(e.message); } finally { this.busy = false; }
                },
                async upload(ev) {
                    const file = ev.target.files[0]; if (!file) return;
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!(['jpg', 'jpeg', 'png'].includes(ext) || file.type.startsWith('video/'))) { alert('Only image (JPG, JPEG, PNG) or video files are allowed.'); ev.target.value = ''; return; }
                    if (file.size > 5 * 1024 * 1024) { alert('File size must not exceed 5 MB.'); ev.target.value = ''; return; }
                    const fd = new FormData(); fd.append('file', file);
                    this.busy = true;
                    try { this.detail.attachments = (await this.post('upload', fd)).attachments; }
                    catch (e) { alert(e.message); } finally { this.busy = false; ev.target.value = ''; }
                },
                fileIcon(ext) {
                    ext = String(ext || '').toLowerCase();
                    return ext === 'pdf' ? 'fa-file-pdf' : (['xls', 'xlsx'].includes(ext) ? 'fa-file-excel' : (['doc', 'docx'].includes(ext) ? 'fa-file-word' : 'fa-file-image'));
                },
                // timeline step look, from the legacy approval result (S submit, A approve, R reject, D revise, P pending)
                step(a) {
                    const done = 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
                    const pillDone = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300';
                    switch (a.aprvresult) {
                        case 'S': return { box: done, icon: 'fa-check', pill: pillDone, label: 'Submitted' };                        case 'A': return { box: done, icon: 'fa-check', pill: pillDone, label: 'Approved' };
                        case 'R': return { box: 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400', icon: 'fa-xmark', pill: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300', label: 'Rejected' };
                        case 'D': return { box: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400', icon: 'fa-rotate-left', pill: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300', label: 'Revise' };
                        default: return { box: 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400', icon: 'fa-clock', pill: 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400', label: 'Waiting' };
                    }
                },
                badge(s) { return badgeCls[s] || 'bg-gray-100 text-gray-600'; },
                // same pill colours as the list
                modalBadge(s) {
                    return {
                        P: 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30',
                        C: 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30',
                        R: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30',
                        X: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30',
                        H: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30',
                        E: 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30',
                    }[s] || 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10';
                },
                // grouped facts, each [label, value, icon]
                infoGroups(d) {
                    return [
                        { title: 'Tenant & Location', icon: 'fa-store', span: 'md:col-span-2', cols: '', items: [
                            ['Site', d.sitename, 'fa-building'], ['Company', d.companyname, 'fa-briefcase'], ['Location', d.locationname, 'fa-location-dot'],
                            ['Tenant', d.storename, 'fa-shop'], ['Floor / Unit', [d.floor, d.unit].filter(Boolean).join(' / '), 'fa-layer-group'],
                        ] },
                        { title: 'Person in Charge', icon: 'fa-user', span: 'md:col-span-2', cols: '', items: [
                            ['Person in Charge', d.requestpenanggungjawab, 'fa-user-tie'],
                            ['Phone', d.requestphone, 'fa-phone'], ['Email', d.requestemail, 'fa-envelope'],
                        ] },
                        { title: 'Event Period', icon: 'fa-calendar-days', span: 'md:col-span-2', cols: '', items: [
                            ['Start - End Date', [d.startdate, d.enddate].filter(Boolean).join(' - '), 'fa-calendar-check'],
                            ['Start - End Time', [(d.starttime || '').slice(0, 5), (d.endtime || '').slice(0, 5)].filter(Boolean).join(' - '), 'fa-clock'],
                            ['Extended', d.totalextend > 0 ? `${d.totalextend}x (${d.extendstartdate || '-'} – ${d.extendenddate || '-'})` : '', 'fa-rotate'],
                        ] },
                        { title: 'Event', icon: 'fa-champagne-glasses', span: 'md:col-span-2', cols: '', items: [
                            ['Event Type', d.eventtype, 'fa-tag'], ['Event Location', d.eventlocation, 'fa-location-dot'],
                            ['Guests / Media', d.eventguests, 'fa-users'],
                        ] },
                        { title: 'Contacts', icon: 'fa-address-book', span: 'md:col-span-4', cols: 'sm:grid-cols-2', items: [
                            ['Event Coordinator', d.coordinatorname, 'fa-user-check'], ['PIC Security', d.picsecurity, 'fa-user-shield'],
                            ['PIC Engineering', d.picengineering, 'fa-screwdriver-wrench'],
                            ['Event Organizer', d.organizername, 'fa-sitemap'], ['Event Organizer PIC', d.organizerpic, 'fa-user-tie'],
                        ] },
                    ];
                },
                splitList(t) {
                    const lines = String(t).split(/\r?\n/).map(x => x.trim()).filter(Boolean);
                    const parts = lines.length > 1 ? lines : String(t).split(/\s+(?=\d+\.\s)/).map(x => x.trim()).filter(Boolean);
                    return parts.map(x => x.replace(/^\d+[.)]\s*/, ''));
                },
                // long text, one bordered block each
                textFields(d) {
                    return [
                        ['Notes', d.catatan, 'fa-note-sticky'],
                    ];
                },
                banner(s) {
                    return {
                        P: { cls: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/30', icon: 'fa-circle-info', text: 'Waiting for approval. This SIA is under review.' },
                        C: { cls: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/30', icon: 'fa-circle-check', text: 'Completed. This SIA has been approved.' },
                        R: { cls: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/30', icon: 'fa-circle-xmark', text: 'Rejected. This SIA has been rejected.' },
                        H: { cls: 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:border-yellow-500/30', icon: 'fa-triangle-exclamation', text: 'On Hold. This SIA requires revision.' },
                        X: { cls: 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-white/5 dark:text-slate-400 dark:border-white/10', icon: 'fa-ban', text: 'Cancelled. This SIA has been cancelled.' },
                    }[s] || null;
                },
            };
        }
    </script>
</x-app-layout>
