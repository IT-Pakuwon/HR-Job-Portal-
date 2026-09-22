@props([
    'align' => 'right',
])

@php
    $user = Auth::user();
@endphp

<div class="relative inline-flex" x-data="{ open: false }">

    {{-- ✅ kalau user null (session expired), jangan render dropdown --}}
    @if(!$user)
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:text-gray-900 dark:border-gray-700/60 dark:bg-gray-800 dark:text-gray-200">
            Login
        </a>
    @else

        <button class="group flex h-11 min-w-11 items-center justify-center gap-1" aria-haspopup="true"
                @click.prevent="open = !open" :aria-expanded="open">
            <img id="headerAvatarImg" class="h-8 w-8 shrink-0 rounded-full"
                 src="{{ $user->profile_photo_url ?? asset('images/avatar-default.png') }}"
                 width="32" height="32"
                 alt="{{ $user->name ?? 'User' }}" />
            <span
                class="ml-1 hidden max-w-28 truncate text-xs font-medium text-gray-600 group-hover:text-gray-800 sm:inline-block dark:text-gray-100 dark:group-hover:text-white">
                {{ $user->name }}
            </span>
            <svg class="h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" viewBox="0 0 12 12">
                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
            </svg>
        </button>

        <div class="{{ $align === 'right' ? 'right-0' : 'left-0' }} absolute top-full z-10 mt-1.5 w-64 origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg shadow-gray-200/50 dark:border-gray-700/60 dark:bg-gray-800 dark:shadow-none"
             @click.outside="open = false" @keydown.escape.window="open = false" x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0" x-cloak>

            <a href="{{ route('profile.showx') }}" @click="open = false"
               class="group flex items-start gap-2.5 border-b border-gray-100 bg-gray-50/60 px-3.5 py-3 transition-colors hover:bg-violet-50/60 dark:border-gray-700/60 dark:bg-gray-800/60 dark:hover:bg-violet-500/5">
                <img class="h-9 w-9 shrink-0 rounded-full ring-2 ring-white dark:ring-gray-800"
                     src="{{ $user->profile_photo_url ?? asset('images/avatar-default.png') }}"
                     width="36" height="36" alt="{{ $user->name ?? 'User' }}" />
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-gray-800 group-hover:text-violet-600 dark:text-gray-100 dark:group-hover:text-violet-400">
                        {{ $user->name }}
                    </div>
                    @if($user->originCompany || $user->originDepartment)
                        <div class="mt-1 flex flex-wrap items-center gap-1">
                            @if($user->originCompany)
                                <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-[10.5px] font-medium text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                    {{ $user->originCompany->cpny_name }}
                                </span>
                            @endif
                            @if($user->originDepartment)
                                <span class="inline-flex items-center rounded-full bg-gray-200/70 px-2 py-0.5 text-[10.5px] font-medium text-gray-600 dark:bg-gray-700/60 dark:text-gray-400">
                                    {{ $user->originDepartment->department_name }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </a>

            <ul class="py-1.5">
                <li>
                    <button type="button" id="btnShowNameCard"
                       class="flex w-full items-center gap-2.5 px-3.5 py-2 text-left text-xs font-medium text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600 dark:text-gray-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400"
                       @click="open = false" @focus="open = true" @focusout="open = false">
                        <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M3 4a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2H3zm1.5 3a1 1 0 000 2h1a1 1 0 100-2h-1zM4 12a1 1 0 011-1h2a1 1 0 110 2H5a1 1 0 01-1-1zm7.5-5a1 1 0 100 2h4a1 1 0 100-2h-4zm-1 4a1 1 0 011-1h4a1 1 0 110 2h-4a1 1 0 01-1-1z" />
                        </svg>
                        Name Card
                    </button>
                </li>
                <li>
                    <button type="button" id="btnShowCheckinTraining"
                       class="flex w-full items-center gap-2.5 px-3.5 py-2 text-left text-xs font-medium text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600 dark:text-gray-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400"
                       @click="open = false" @focus="open = true" @focusout="open = false">
                        <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                        </svg>
                        Checkin Training
                    </button>
                </li>
                <li>
                    <a class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600 dark:text-gray-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400"
                       href="{{ route('profile.showx') }}" @click="open = false" @focus="open = true"
                       @focusout="open = false">
                        <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M8.34 1.804A1 1 0 019.32 1h1.36a1 1 0 01.98.804l.295 1.473c.077.383.417.655.806.655.244 0 .484-.068.71-.203l1.301-.768a1 1 0 011.28.15l.96.96a1 1 0 01.15 1.28l-.768 1.301a1.14 1.14 0 00.452 1.516l1.473.295a1 1 0 01.804.98v1.36a1 1 0 01-.804.98l-1.473.295a1.14 1.14 0 00-.452 1.516l.768 1.301a1 1 0 01-.15 1.28l-.96.96a1 1 0 01-1.28.15l-1.301-.768a1.14 1.14 0 00-1.516.452l-.295 1.473a1 1 0 01-.98.804h-1.36a1 1 0 01-.98-.804l-.295-1.473a1.14 1.14 0 00-1.516-.452l-1.301.768a1 1 0 01-1.28-.15l-.96-.96a1 1 0 01-.15-1.28l.768-1.301a1.14 1.14 0 00-.452-1.516l-1.473-.295a1 1 0 01-.804-.98v-1.36a1 1 0 01.804-.98l1.473-.295a1.14 1.14 0 00.452-1.516l-.768-1.301a1 1 0 01.15-1.28l.96-.96a1 1 0 011.28-.15l1.301.768c.226.135.466.203.71.203.39 0 .729-.272.806-.655l.295-1.473zM10 13a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                        Settings
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <a class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-gray-300 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                           href="{{ route('logout') }}" @click.prevent="$root.submit();" @focus="open = true"
                           @focusout="open = false">
                            <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" />
                            </svg>
                            {{ __('Sign Out') }}
                        </a>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Name Card (QR vCard) modal -->
        <div id="nameCardModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
            <button type="button" id="closeNameCardModal"
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-8 sm:top-8">
                <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                </svg>
            </button>

            <div class="flex w-full max-w-sm flex-col items-center rounded-lg border border-gray-200 bg-white p-8 text-center shadow-lg dark:border-gray-700 dark:bg-gray-800"
                 onclick="event.stopPropagation()">
                <p class="text-base font-semibold text-gray-800 dark:text-white">{{ $user->name }}</p>
                <p class="mb-6 text-xs text-gray-400">NPK {{ $user->npk ?? '-' }}</p>

                <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                    <img id="nameCardModalImage" src="" alt="Name card QR" class="w-full max-w-55">
                </div>

                <p class="mt-6 text-sm font-semibold text-gray-800 dark:text-gray-100">Phone Camera</p>
                <p class="mt-1 text-xs text-gray-400">Scan to save as a contact — also checks you in at any training
                    event.</p>
            </div>
        </div>

        <script>
            (function () {
                const nameCardQrUrl = "{{ route('profile.qr.image') }}";

                $('#btnShowNameCard').on('click', function () {
                    $('#nameCardModalImage').attr('src', nameCardQrUrl + '?t=' + Date.now());
                    $('#nameCardModal').removeClass('hidden').addClass('flex');
                });

                $('#closeNameCardModal').on('click', function () {
                    $('#nameCardModal').addClass('hidden').removeClass('flex');
                });

                $('#nameCardModal').on('click', function (e) {
                    if (e.target === this) {
                        $(this).addClass('hidden').removeClass('flex');
                    }
                });
            })();
        </script>

        <!-- Checkin Training (Barcode / QR Code) modal -->
        <div id="checkinTrainingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
            <button type="button" id="closeCheckinTrainingModal"
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-8 sm:top-8">
                <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                </svg>
            </button>

            <div class="flex w-full max-w-sm flex-col items-center rounded-lg border border-gray-200 bg-white p-8 text-center shadow-lg dark:border-gray-700 dark:bg-gray-800"
                 onclick="event.stopPropagation()">
                <p class="text-base font-semibold text-gray-800 dark:text-white">{{ $user->name }}</p>
                <p class="mb-4 text-xs text-gray-400">NPK {{ $user->npk ?? '-' }}</p>

                <div class="mb-5 inline-flex rounded-full bg-gray-100 p-1 dark:bg-gray-700">
                    <button type="button" id="checkinTabBarcode"
                            class="checkinTabBtn rounded-full px-4 py-1.5 text-xs font-semibold transition"
                            data-kind="barcode">
                        Barcode
                    </button>
                    <button type="button" id="checkinTabQr"
                            class="checkinTabBtn rounded-full px-4 py-1.5 text-xs font-semibold transition"
                            data-kind="qr">
                        QR Code
                    </button>
                </div>

                <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                    <img id="checkinTrainingModalImage" src="" alt="" class="w-full max-w-55">
                </div>

                <p id="checkinTrainingModalTitle" class="mt-6 text-sm font-semibold text-gray-800 dark:text-gray-100"></p>
                <p id="checkinTrainingModalCaption" class="mt-1 text-xs text-gray-400"></p>
            </div>
        </div>

        <script>
            (function () {
                const checkinBarcodeUrl = "{{ route('profile.barcode.image') }}";
                const checkinQrUrl = "{{ route('profile.qr.checkin.image') }}";

                function setCheckinTab(kind) {
                    const isBarcode = kind === 'barcode';

                    $('.checkinTabBtn').removeClass('bg-white text-violet-600 shadow-sm dark:bg-gray-800 dark:text-violet-400')
                        .addClass('text-gray-500 dark:text-gray-400');
                    $(isBarcode ? '#checkinTabBarcode' : '#checkinTabQr')
                        .removeClass('text-gray-500 dark:text-gray-400')
                        .addClass('bg-white text-violet-600 shadow-sm dark:bg-gray-800 dark:text-violet-400');

                    $('#checkinTrainingModalImage').attr('src', (isBarcode ? checkinBarcodeUrl : checkinQrUrl) + '?t=' + Date.now());
                    $('#checkinTrainingModalTitle').text(isBarcode ? 'HR Check-in Scanner' : 'Phone Camera');
                    $('#checkinTrainingModalCaption').text(isBarcode ?
                        'Show this to the HR check-in scanner at any training event.' :
                        'Scan with the check-in scanner or a phone camera to check in at any training event.');
                }

                $('#btnShowCheckinTraining').on('click', function () {
                    setCheckinTab('barcode');
                    $('#checkinTrainingModal').removeClass('hidden').addClass('flex');
                });

                $('.checkinTabBtn').on('click', function () {
                    setCheckinTab($(this).data('kind'));
                });

                $('#closeCheckinTrainingModal').on('click', function () {
                    $('#checkinTrainingModal').addClass('hidden').removeClass('flex');
                });

                $('#checkinTrainingModal').on('click', function (e) {
                    if (e.target === this) {
                        $(this).addClass('hidden').removeClass('flex');
                    }
                });
            })();
        </script>
    @endif
</div>
