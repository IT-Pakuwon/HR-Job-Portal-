@php
    // Nicer display label for a raw IMAP folder path: "INBOX" -> "Inbox",
    // "INBOX/APP System" -> "APP System" (leaf name), everything else as-is.
    $folderLabel = function (string $path) {
        $leaf = str_contains($path, '/') ? substr($path, strrpos($path, '/') + 1) : $path;
        return $leaf === 'INBOX' ? 'Inbox' : $leaf;
    };
@endphp
<div class="flex h-full flex-col gap-2 overflow-hidden lg:flex-row">
    <!-- FOLDER SIDEBAR -->
    <div class="flex shrink-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a] lg:w-56">
        <div class="flex items-start justify-between gap-2 border-b border-gray-100 px-4 py-4 dark:border-white/[0.06]">
            <div class="min-w-0">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📧 Mailbox</h2>
                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-300">{{ $account->email }}</p>
            </div>
            <button type="button" @click="syncNow()" :disabled="syncing" title="Sync now"
                class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:opacity-50 dark:hover:bg-white/[0.06] dark:hover:text-gray-200">
                <svg class="h-4 w-4" :class="syncing ? 'animate-spin' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 0 1-15.5 6.5M3 12a9 9 0 0 1 15.5-6.5M21 3v6h-6M3 21v-6h6" />
                </svg>
            </button>
        </div>

        <div class="p-2">
            <button type="button" @click="openComposeNew()"
                class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 text-sm font-medium text-white transition hover:bg-blue-500">
                ✏️ New Email
            </button>
        </div>

        <nav class="flex flex-row gap-1 overflow-x-auto p-2 pt-0 lg:min-h-0 lg:flex-1 lg:flex-col lg:overflow-x-visible lg:overflow-y-auto">
            @foreach ($folders as $f)
                @php
                    // Folders come back parent-then-children (see MailboxService::flattenFolders),
                    // so a simple "/" depth count is enough to indent subfolders under their parent.
                    $depth = substr_count($f, '/');
                    $fc = $folderCounts->get($f);
                    $fUnread = $fc->unread ?? 0;
                @endphp
                <a href="{{ route('mailbox.index', ['folder' => $f]) }}"
                    @if ($depth > 0) style="margin-left: {{ $depth * 16 }}px" @endif
                    class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-2 transition
                        {{ $depth > 0 ? 'text-[13px] text-gray-500 dark:text-gray-400' : 'text-sm font-medium text-gray-600 dark:text-gray-300' }}
                        {{ $f === $folder ? '!text-white bg-blue-600' : 'hover:bg-gray-100 dark:hover:bg-white/[0.06]' }}">
                    @if ($depth > 0)
                        <svg class="h-3.5 w-3.5 shrink-0 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3v10a2 2 0 0 0 2 2h9m-4-4 4 4-4 4" />
                        </svg>
                    @endif
                    <span class="min-w-0 flex-1 truncate">{{ $folderLabel($f) }}</span>
                    @if ($fUnread > 0)
                        <span class="inline-flex shrink-0 items-center rounded-full {{ $f === $folder ? 'bg-white/20' : 'bg-red-500 text-white' }} px-1.5 py-0.5 text-[11px] font-semibold">
                            {{ $fUnread }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="border-t border-gray-100 p-2 dark:border-white/[0.06]">
            <button type="button" @click="openSettings()"
                class="flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.22.66.22 1H21a2 2 0 0 1 0 4h-.09c-.34 0-.68.08-1 .22z" />
                </svg>
                Settings
            </button>
        </div>
    </div>

    <!-- EMAIL LIST -->
    <div class="flex h-full min-w-0 flex-1 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
        <div class="flex shrink-0 flex-col gap-4 border-b border-gray-100 bg-gray-50/60 px-5 py-4 dark:border-white/[0.06] dark:bg-white/[0.02] sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                @php
                    $folderIcons = [
                        'INBOX'  => 'M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z',
                        'Sent'   => 'M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z',
                        'Drafts' => 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z',
                        'Junk'   => 'M10 15l2-2m0 0 2-2m-2 2-2-2m2 2 2 2M4.93 4.93a10 10 0 1 0 14.14 0 10 10 0 0 0-14.14 0Z',
                        'Trash'  => 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z',
                        'Archive' => 'M21 8v13H3V8M1 3h22v5H1zM10 12h4',
                    ];
                    $folderIcon = $folderIcons[$folder] ?? 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z';
                @endphp
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $folderIcon }}" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ $folderLabel($folder) }}</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $emails->total() }} {{ $emails->total() === 1 ? 'message' : 'messages' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="text" value="{{ $search }}" placeholder="Search subject or sender..."
                        x-on:input.debounce.500ms="loadPanel({ folder: currentFolder, q: $event.target.value, per_page: {{ $perPage }} })"
                        class="h-10 w-56 rounded-full border border-gray-300 bg-white pl-9 pr-3 text-sm shadow-sm dark:border-white/[0.08] dark:bg-white/[0.03] dark:text-gray-100" />
                </div>
                <div class="relative">
                    <select x-on:change="loadPanel({ folder: currentFolder, q: @js($search), per_page: $event.target.value })"
                        class="h-10 appearance-none rounded-full border border-gray-300 bg-white py-0 pl-3 pr-8 text-sm shadow-sm dark:border-white/[0.08] dark:bg-white/[0.03] dark:text-gray-100">
                        @foreach ([10, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }} / page</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto">
            <table class="w-full min-w-full border-separate border-spacing-0 text-sm">
                <thead class="sticky top-0 z-10">
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 backdrop-blur dark:border-white/[0.06] dark:bg-[#0f172a]/95 dark:text-gray-400">
                        <th class="w-8 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left font-medium">From</th>
                        <th class="px-4 py-3 text-left font-medium">Subject</th>
                        <th class="w-44 px-4 py-3 text-left font-medium">Date</th>
                        <th class="w-24 px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emails as $email)
                        <tr @click="openEmail({{ $email->id }})"
                            class="cursor-pointer border-b border-gray-100 last:border-b-0 hover:bg-gray-50/70 dark:border-white/[0.04] dark:hover:bg-white/[0.03] {{ $email->is_read ? '' : 'bg-blue-50/40 dark:bg-blue-500/[0.06]' }}">
                            <td class="px-4 py-3 text-center">
                                @unless ($email->is_read)
                                    <span class="inline-block h-2 w-2 rounded-full bg-blue-600" title="Unread"></span>
                                @endunless
                            </td>
                            <td class="px-4 py-3 {{ $email->is_read ? 'text-gray-600 dark:text-gray-300' : 'font-semibold text-gray-900 dark:text-gray-100' }}">
                                {{ $email->from_name ?: $email->from_address ?: '(unknown sender)' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="{{ $email->is_read ? 'text-gray-600 dark:text-gray-300' : 'font-semibold text-gray-900 dark:text-gray-100' }}">
                                    {{ $email->subject ?: '(no subject)' }}
                                </div>
                                <div class="truncate text-xs text-gray-400 dark:text-gray-500">{{ $email->body_preview }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ optional($email->email_date)->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3" @click.stop>
                                <div class="flex items-center gap-1">
                                    @if ($folder !== 'Archive')
                                        <button type="button" @click="archiveEmail({{ $email->id }})" :disabled="busyId === {{ $email->id }}"
                                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 disabled:opacity-40 dark:hover:bg-white/[0.06] dark:hover:text-gray-200"
                                            title="Archive">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button type="button" @click="deleteEmail({{ $email->id }}, {{ $folder === 'Trash' ? 'true' : 'false' }})" :disabled="busyId === {{ $email->id }}"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-40 dark:hover:bg-red-500/10"
                                        title="{{ $folder === 'Trash' ? 'Delete permanently' : 'Move to Trash' }}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                No emails in this folder yet. Click "Sync now" to fetch from the mailbox.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="shrink-0 px-5 py-4">
            {{ $emails->links() }}
        </div>
    </div>
</div>
