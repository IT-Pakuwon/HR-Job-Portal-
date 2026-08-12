<x-app-layout>
    <x-slot name="header">
        <h2 class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Profile') }}
        </h2>
    </x-slot>

        <div class="mx-auto max-w-9xl space-y-6 px-2 py-2 sm:px-6 lg:px-8 flex flex-col">

            <!-- Profile -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div
                    class="flex flex-col gap-4 border-b border-blue-100 bg-blue-50 p-6 dark:border-blue-500/20 dark:bg-blue-500/10 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <img id="profilePhotoPreview"
                                class="h-16 w-16 rounded-full border border-gray-200 object-cover dark:border-gray-600"
                                src="{{ Auth::user()->profile_photo_url }}" alt="User avatar">
                            <button type="button" id="btnChangePhoto"
                                class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-gray-900 text-white transition hover:bg-gray-700 dark:border-gray-800 dark:bg-gray-100 dark:text-gray-900"
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
                                    class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
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
                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            <i class="fa-solid fa-barcode"></i>
                            Barcode
                        </button>
                        <button type="button" id="btnShowQr"
                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            <i class="fa-solid fa-qrcode"></i>
                            QR Code
                        </button>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 divide-y divide-gray-100 dark:divide-gray-700 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Password</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Change your account password</p>
                        </div>
                        <button type="button" id="btnChangePassword"
                            class="inline-flex items-center gap-2 rounded-md border border-blue-500 px-3 py-1.5 text-xs font-medium text-blue-600 transition hover:bg-blue-500 hover:text-white dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white">
                            <i class="fa-solid fa-key text-[10px]"></i>
                            Change Password
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Dark Mode</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Use dark mode by default whenever you
                                sign in</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="darkmodeDefaultSwitch" class="peer sr-only"
                                {{ Auth::user()->is_darkmode ? 'checked' : '' }}>
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:border-gray-600 dark:bg-gray-700">
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Company Access -->
            <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Company Access</h4>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="grid grid-cols-1 gap-2 px-6 py-4 sm:grid-cols-4 sm:items-center sm:gap-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Company</p>
                        <div class="flex flex-wrap gap-1.5 sm:col-span-3">
                            @foreach (explode(',', $talenta->cpny_id) as $c)
                                <span
                                    class="rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-700/40 dark:text-gray-200">{{ trim($c) }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-2 px-6 py-4 sm:grid-cols-4 sm:items-center sm:gap-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Department</p>
                        <p class="text-sm text-gray-800 dark:text-gray-100 sm:col-span-3">
                            {{ $talenta->department_id }}</p>
                    </div>
                    <div class="grid grid-cols-1 gap-2 px-6 py-4 sm:grid-cols-4 sm:items-start sm:gap-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Business Unit</p>
                        <div class="flex flex-wrap gap-1.5 sm:col-span-3">
                            @foreach (explode(',', $talenta->business_unit_id) as $bu)
                                <span
                                    class="rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">{{ trim($bu) }}</span>
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
