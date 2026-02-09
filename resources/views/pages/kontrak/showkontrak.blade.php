<x-app-layout>
    <div class="max-w-9xl mx-auto px-8 py-4 sm:px-8 lg:px-8">
        {{-- Top bar --}}
        <div class="mb-4 flex items-center justify-between">
            @php
                // mapping status Kontrak (samakan style PO)
                $st = strtoupper((string) ($kontrak->status ?? ''));
                $statusText = match ($st) {
                    'H' => 'Hold',
                    'P' => 'On Progress',
                    'C' => 'Completed',
                    default => 'Unknown',
                };

                $statusClasses = match ($st) {
                    'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                    'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                    'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                };

                $sppbDisplay = e($kontrak->sppbjktid);
                if (!empty($sppbUrl)) {
                    $sppbDisplay =
                        '<a href="' .
                        e($sppbUrl) .
                        '" target="_blank"
                            class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                        e($kontrak->sppbjktid) .
                        '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                        </svg></a>';
                }

                $csDisplay = e($kontrak->csid);
                if (!empty($csUrl)) {
                    $csDisplay =
                        '<a href="' .
                        e($csUrl) .
                        '" target="_blank"
                            class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                        e($kontrak->csid) .
                        '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M18 18.75H6.75A1.5 1.5 0 0 1 5.25 17.25V6.75A1.5 1.5 0 0 1 6.75 5.25H12" />
                        </svg></a>';
                }

                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                $value = 'break-words font-medium text-gray-900 dark:text-gray-100 sm:flex-1';
            @endphp
            @php
                $loginUser = auth()->user();

                $createdBy = $kontrak->created_by ?? null;

                $isOwner = false;
                if ($loginUser) {
                    $isOwner =
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->username ?? '')) ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->name ?? '')) ||
                        (is_string($createdBy) && strtolower($createdBy) === strtolower($loginUser->email ?? ''));
                }

                // encode id untuk URL createkontrak
                $eid = \Vinkla\Hashids\Facades\Hashids::encode($kontrak->id);
            @endphp

            <div class="flex items-center gap-3">
                @if ($isOwner)
                    <a href="{{ route('kontrak.edit', $eid) }}"
                        class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.875 4.5" />
                        </svg>
                        Edit
                    </a>
                @endif
            </div>

        </div>

        <div class="flex w-full flex-col gap-6">
            <div class="flex w-full items-stretch gap-6 xl:flex-row">
                {{-- Left card (Kontrak Info) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $kontrak->kontrakid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold">
                                {{ $statusText }}
                            </span>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        @php
                            $userMap = collect($users ?? [])->mapWithKeys(function ($u) {
                                $username = is_array($u) ? $u['username'] ?? '' : $u->username ?? '';
                                $name = is_array($u) ? $u['name'] ?? '' : $u->name ?? '';
                                return [$username => $name ?: $username];
                            });
                        @endphp

                        @php
                            $fields = [
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'Kontrak Date',
                                    'value' =>
                                        optional($kontrak->kontrakdate)->format('d M Y') ??
                                        ($kontrak->kontrakdate
                                            ? \Carbon\Carbon::parse($kontrak->kontrakdate)->format('d M Y')
                                            : '-'),
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $kontrak->cpny_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $kontrak->department_id,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Requester',
                                    'value' => ucwords(strtolower($kontrak->user_peminta ?? '-')),
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T ID',
                                    'value' => $sppbDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' => $csDisplay,
                                    'is_raw' => true,
                                ],
                                [
                                    'icon' => 'identification',
                                    'label' => 'Vendor ID',
                                    'value' => $kontrak->vendorid,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => $kontrak->vendorname,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'tag',
                                    'label' => 'Kontrak Type',
                                    'value' => $kontrak->kontraktype,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'tag',
                                    'label' => 'Kontrak Category',
                                    'value' => $kontrak->kontrakcategory,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'document',
                                    'label' => 'No SK',
                                    'value' => $kontrak->nosk,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'document-check',
                                    'label' => 'No PK Legal',
                                    'value' => $kontrak->nopklegal,
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'calendar',
                                    'label' => 'Start Date',
                                    'value' => $kontrak->startdate
                                        ? \Carbon\Carbon::parse($kontrak->startdate)->format('d M Y')
                                        : '-',
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'calendar',
                                    'label' => 'End Date',
                                    'value' => $kontrak->enddate
                                        ? \Carbon\Carbon::parse($kontrak->enddate)->format('d M Y')
                                        : '-',
                                    'is_raw' => false,
                                ],
                                [
                                    'icon' => 'user',
                                    'label' => 'User Approval',
                                    'value' => $userMap[$kontrak->user_approval] ?? ($kontrak->user_approval ?? '-'),
                                    'is_raw' => false,
                                ],
                            ];
                        @endphp


                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

                                    @if ($f['is_raw'])
                                        <span class="{{ $value }}">{!! $f['value'] !!}</span>
                                    @else
                                        <span class="{{ $value }}">{{ $f['value'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if (!empty($kontrak->keperluan))
                            <div class="mt-4 flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Purpose</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $kontrak->keperluan }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if (!empty($kontrak->kontaknote))
                            <div class="mt-3 flex items-start gap-3 rounded-md border bg-gray-50 p-3 dark:bg-gray-700">
                                <x-heroicon-o-document-text class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Note</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $kontrak->kontaknote }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">

                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Attachment
                                </button>

                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        {{-- Tab: Attachment (read-only list; kalau mau upload mirip PO tinggal tambah form) --}}
                        <div x-show="activeTab === 'attachment'" class="flex-1 p-4">
                            <div
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/60">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($attachment ?? []) as $a)
                                            <tr
                                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">
                                                    @if (!empty($a->url))
                                                        <a href="{{ $a->url }}" target="_blank"
                                                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                            📎 {{ $a->display_name ?? $a->filename }}
                                                        </a>
                                                    @else
                                                        <span class="text-gray-700 dark:text-gray-200">📎
                                                            {{ $a->display_name ?? $a->filename }}</span>
                                                        <span class="ml-2 text-sm text-red-500">(link
                                                            unavailable)</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">{{ $a->created_by ?? '-' }}</td>
                                                <td class="px-3 py-2">
                                                    {{ optional($a->created_at)->format('d M Y H:i') ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3"
                                                    class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab: Comments (pakai endpoint yang sama: /comments/{doctype}/{refnbr}) --}}
                        <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
                            <div class="flex h-full flex-col">
                                <div id="commentList"
                                    class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                    <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                </div>
                                <div class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                    <input id="commentInput" type="text" placeholder="Write a comment..."
                                        class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                    <button id="postCommentBtn" type="button"
                                        class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                        Post 🚀
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- JS comments (copy dari showpo, ganti doctype & ref) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);
        $(document).ready(function() {
            const refnbr = @json($kontrak->kontrakid);
            const doctype = "KO"; // atau "KONTRAK" / "TRKONTRAK" sesuai sistem comments kamu

            loadComments(refnbr, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 text-sm italic">No comments yet.</p>');
                            return;
                        }

                        response.comments.forEach(comment => {
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';
                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class="text-sm font-semibold">
                                        ${comment.username}
                                        <span class="text-sm text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            function addComment() {
                let input = $('#commentInput').val().trim();
                if (input === "") {
                    toastr?.warning?.("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: @json(csrf_token())
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(refnbr, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        toastr?.error?.(xhr.responseJSON?.message || 'Error adding comment.');
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
