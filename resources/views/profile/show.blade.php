<x-app-layout>
    <x-slot name="header">
        <h2 class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Profile') }}
        </h2>
    </x-slot>

        <div class="mx-auto max-w-9xl space-y-6 p-4 sm:p-6 lg:p-8">

            <!-- Profile -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div
                    class="flex flex-col gap-4 border-b border-blue-100 bg-blue-50 p-6 dark:border-blue-500/20 dark:bg-blue-500/10 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <div class="bg-linear-to-br rounded-full from-blue-500 via-indigo-500 to-purple-500 p-0.75 shadow-sm">
                                <img id="profilePhotoPreview"
                                    class="h-16 w-16 rounded-full border-2 border-white object-cover dark:border-gray-800"
                                    src="{{ Auth::user()->profile_photo_url }}" alt="User avatar">
                            </div>
                            <button type="button" id="btnChangePhoto"
                                class="bg-linear-to-br absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white from-blue-600 to-indigo-600 text-white shadow transition hover:scale-110 dark:border-gray-800"
                                title="Change photo">
                                <i class="fa-solid fa-camera text-[10px]"></i>
                            </button>
                            <input type="file" id="profilePhotoInput" name="photo"
                                accept="image/png,image/jpeg,image/webp" class="hidden">
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ Auth::user()->name }}</h3>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                    Employee</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                NPK {{ $talenta->npk ?? '-' }} &middot; {{ Auth::user()->email }}</p>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {{ $originCpnyName ?? '-' }} &middot; {{ $originDepartmentName ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" id="btnShowBarcode"
                            class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300">
                                <i class="fa-solid fa-barcode text-[9px]"></i>
                            </span>
                            Barcode
                        </button>
                        <button type="button" id="btnShowQr"
                            class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300">
                                <i class="fa-solid fa-qrcode text-[9px]"></i>
                            </span>
                            QR Code
                        </button>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-gray-700 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                <i class="fa-solid fa-key text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Password</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Change your account password</p>
                            </div>
                        </div>
                        <button type="button" id="btnChangePassword"
                            class="bg-linear-to-r inline-flex items-center gap-2 rounded-full from-blue-600 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-500/30 transition hover:-translate-y-0.5 hover:shadow-md hover:shadow-blue-500/40">
                            <i class="fa-solid fa-key text-[10px]"></i>
                            Change Password
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <i class="fa-solid fa-moon text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Dark Mode</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Use dark mode by default whenever
                                    you sign in</p>
                            </div>
                        </div>
                        <span
                            class="bg-linear-to-r has-checked:from-indigo-700 has-checked:to-blue-700 relative inline-flex h-7 w-14 shrink-0 items-center rounded-full from-amber-200 to-amber-100 shadow-inner transition-colors duration-300 dark:from-gray-600 dark:to-gray-600">
                            <input type="checkbox" id="darkmodeDefaultSwitch"
                                class="peer absolute inset-0 z-20 cursor-pointer opacity-0"
                                {{ Auth::user()->is_darkmode ? 'checked' : '' }}>
                            <i
                                class="fa-solid fa-sun absolute left-1.75 text-[10px] text-amber-500 transition-opacity duration-300 peer-checked:opacity-0"></i>
                            <i
                                class="fa-solid fa-moon absolute right-1.75 text-[10px] text-white opacity-0 transition-opacity duration-300 peer-checked:opacity-100"></i>
                            <span
                                class="pointer-events-none relative z-10 inline-block h-5 w-5 translate-x-1 rounded-full bg-white shadow-md transition-transform duration-300 peer-checked:translate-x-8"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Company Access -->
            @php
                $tagPalette = [
                    'bg-blue-50 text-blue-700 ring-blue-600/15 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-400/20',
                    'bg-purple-50 text-purple-700 ring-purple-600/15 dark:bg-purple-500/10 dark:text-purple-300 dark:ring-purple-400/20',
                    'bg-pink-50 text-pink-700 ring-pink-600/15 dark:bg-pink-500/10 dark:text-pink-300 dark:ring-pink-400/20',
                    'bg-amber-50 text-amber-700 ring-amber-600/15 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20',
                    'bg-teal-50 text-teal-700 ring-teal-600/15 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-400/20',
                    'bg-emerald-50 text-emerald-700 ring-emerald-600/15 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20',
                    'bg-indigo-50 text-indigo-700 ring-indigo-600/15 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/20',
                    'bg-rose-50 text-rose-700 ring-rose-600/15 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/20',
                ];
                $tagClass = fn (string $label) => $tagPalette[crc32(trim($label)) % count($tagPalette)];
            @endphp
            <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <span
                        class="bg-linear-to-br flex h-8 w-8 items-center justify-center rounded-lg from-blue-600 to-indigo-500 text-white shadow-sm shadow-blue-500/30">
                        <i class="fa-solid fa-building text-xs"></i>
                    </span>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Company Access</h4>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="grid grid-cols-1 gap-2 px-6 py-4 sm:grid-cols-4 sm:items-center sm:gap-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Company</p>
                        <div class="flex flex-wrap gap-1.5 sm:col-span-3">
                            @foreach (explode(',', $talenta->cpny_id) as $c)
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset transition hover:scale-105 {{ $tagClass($c) }}">{{ trim($c) }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-2 px-6 py-4 sm:grid-cols-4 sm:items-center sm:gap-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Department</p>
                        <p
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100 sm:col-span-3">
                            <i class="fa-solid fa-sitemap text-[11px] text-indigo-500"></i>
                            {{ $talenta->department_id }}
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-2 px-6 py-4 sm:grid-cols-4 sm:items-start sm:gap-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Business Unit</p>
                        <div class="flex flex-wrap gap-1.5 sm:col-span-3">
                            @foreach (explode(',', $talenta->business_unit_id) as $bu)
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset transition hover:scale-105 {{ $tagClass($bu) }}">{{ trim($bu) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>

    <div id="changePasswordModal"
        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div
            class="w-full max-w-md overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Change Password</h2>
                    <p class="text-xs text-gray-400">Keep your account secure</p>
                </div>
                <button type="button" id="btnCloseModal"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="changePasswordForm" class="px-6 py-5">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Current
                        Password</label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        <input type="password" name="current_password"
                            class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-9 text-sm transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            required>
                        <button type="button"
                            class="togglePassword absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">New
                        Password</label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        <input type="password" name="password"
                            class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-9 text-sm transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            required>
                        <button type="button"
                            class="togglePassword absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-5">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm
                        New Password</label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        <input type="password" name="password_confirmation"
                            class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-9 text-sm transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            required>
                        <button type="button"
                            class="togglePassword absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button" id="btnCancel"
                        class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        <i class="fa-solid fa-check text-xs"></i>
                        Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fullscreen barcode/QR viewer -->
    <div id="codeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
        <button type="button" id="closeCodeModal"
            class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-8 sm:top-8">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="flex w-full max-w-sm flex-col items-center rounded-lg border border-gray-200 bg-white p-8 text-center shadow-lg dark:border-gray-700 dark:bg-gray-800"
            onclick="event.stopPropagation()">
            <p class="text-base font-semibold text-gray-800 dark:text-white">{{ Auth::user()->name }}</p>
            <p class="mb-6 text-xs text-gray-400">NPK {{ $talenta->npk ?? '-' }}</p>

            <div class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                <img id="codeModalImage" src="" alt="" class="max-w-55 w-full">
            </div>

            <p id="codeModalTitle" class="mt-6 text-sm font-semibold text-gray-800 dark:text-gray-100"></p>
            <p id="codeModalCaption" class="mt-1 text-xs text-gray-400"></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function resetPasswordVisibility() {
            $('#changePasswordForm input[type="text"]').attr('type', 'password');
            $('.togglePassword i').removeClass('fa-eye-slash').addClass('fa-eye');
        }

        function closeChangePasswordModal() {
            $('#changePasswordModal').addClass('hidden');
            $('#changePasswordForm')[0].reset();
            resetPasswordVisibility();
        }

        $('#btnChangePassword').click(function() {
            $('#changePasswordModal').removeClass('hidden');
        });

        $('#btnCancel, #btnCloseModal').click(function() {
            closeChangePasswordModal();
        });

        const barcodeImageUrl = "{{ route('profile.barcode.image') }}";
        const qrImageUrl = "{{ route('profile.qr.image') }}";

        function openCodeModal(kind) {
            const isBarcode = kind === 'barcode';
            $('#codeModalImage').attr('src', (isBarcode ? barcodeImageUrl : qrImageUrl) + '?t=' + Date.now());
            $('#codeModalTitle').text(isBarcode ? 'HR Check-in Scanner' : 'Phone Camera');
            $('#codeModalCaption').text(isBarcode ?
                "Show this to the HR check-in scanner at any training event." :
                'Scan to save as a contact — also checks you in at any training event.');
            $('#codeModal').removeClass('hidden').addClass('flex');
        }

        $('#btnShowBarcode').on('click', () => openCodeModal('barcode'));
        $('#btnShowQr').on('click', () => openCodeModal('qr'));

        $('#closeCodeModal').on('click', function() {
            $('#codeModal').addClass('hidden').removeClass('flex');
        });

        $('#codeModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden').removeClass('flex');
            }
        });

        $(document).on('click', '.togglePassword', function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            const isPassword = input.attr('type') === 'password';
            input.attr('type', isPassword ? 'text' : 'password');
            icon.toggleClass('fa-eye', !isPassword).toggleClass('fa-eye-slash', isPassword);
        });

        $('#btnChangePhoto').on('click', function() {
            $('#profilePhotoInput').trigger('click');
        });

        $('#profilePhotoInput').on('change', function() {
            const file = this.files[0];
            if (!file) return;

            const previewUrl = URL.createObjectURL(file);
            $('#profilePhotoPreview').attr('src', previewUrl);

            const formData = new FormData();
            formData.append('photo', file);

            $.ajax({
                url: '{{ route('profile.photo.update') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toastr.success('Profile photo updated');
                    $('#profilePhotoPreview').attr('src', response.url);
                    $('#headerAvatarImg').attr('src', response.url);
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;
                    toastr.error(res && res.message ? res.message : 'Failed to update profile photo');
                }
            });
        });

        $('#darkmodeDefaultSwitch').on('change', function() {
            const isDark = $(this).is(':checked');

            // apply immediately to this session so the toggle previews live
            document.documentElement.classList.add('**:transition-none!');
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            localStorage.setItem('dark-mode', isDark ? 'true' : 'false');
            document.querySelectorAll('.light-switch').forEach(el => el.checked = isDark);
            setTimeout(() => document.documentElement.classList.remove('**:transition-none!'), 1);

            $.ajax({
                url: '{{ route('darkmode.update.custom') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    is_darkmode: isDark ? 1 : 0
                },
                success: function() {
                    toastr.success('Theme preference saved');
                },
                error: function() {
                    toastr.error('Failed to save theme preference');
                }
            });
        });

        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route('password.update.custom') }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    // alert(response.message);
                    toastr.success(response.message);
                    closeChangePasswordModal();
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;
                    // alert(res.message || 'Something went wrong.');
                    toastr.error(xhr.responseJSON.message);
                }
            });
        });
    </script>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
