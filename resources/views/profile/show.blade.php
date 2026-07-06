<x-app-layout>
    <x-slot name="header">
        <h2 class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-9xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm sm:rounded-2xl dark:bg-gray-800">
                <!-- Cover banner -->
                <div class="h-28 bg-linear-to-r from-blue-600 via-indigo-500 to-blue-400 sm:h-32"></div>

                <div class="grid grid-cols-1 gap-6 px-4 pb-8 sm:px-6 md:grid-cols-3 md:px-8">
                    <!-- Left Column (Profile Sidebar) -->
                    <div class="-mt-16 md:col-span-1">
                        <div
                            class="rounded-xl border border-gray-100 bg-white p-6 text-center shadow-md dark:border-gray-700 dark:bg-gray-900">
                            <img class="mx-auto h-28 w-28 rounded-full border-4 border-white object-cover shadow-lg dark:border-gray-900"
                                src="{{ asset('avatar/' . Auth::user()->npk . '.jpg') }}"
                                onerror="this.onerror=null; this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';"
                                alt="User avatar">

                            <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white">
                                {{ Auth::user()->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ Auth::user()->departmentid }}
                            </p>

                            <div class="mt-5 flex justify-center gap-2">
                                <button id="btnChangePassword"
                                    class="inline-flex items-center gap-2 rounded-lg border border-blue-500 px-4 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-500 hover:text-white dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white">
                                    <i class="fa-solid fa-key text-xs"></i>
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="changePasswordModal"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                        <i class="fa-solid fa-key"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Change Password</h2>
                                        <p class="text-xs text-gray-400">Keep your account secure</p>
                                    </div>
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
                                        <i class="fa-solid fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                        <input type="password" name="current_password"
                                            class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-9 text-sm transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
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
                                        <i class="fa-solid fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                        <input type="password" name="password"
                                            class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-9 text-sm transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
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
                                        <i class="fa-solid fa-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                        <input type="password" name="password_confirmation"
                                            class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-9 text-sm transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                            required>
                                        <button type="button"
                                            class="togglePassword absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                    <button type="button" id="btnCancel"
                                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                                        <i class="fa-solid fa-check text-xs"></i>
                                        Update</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    <!-- Right Column (User Info) -->
                    <div class="mt-6 md:col-span-2 md:mt-6">
                        <div
                            class="h-full rounded-xl border border-gray-100 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-700/40">
                            <h4
                                class="mb-5 flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                <i class="fa-solid fa-id-card text-blue-500"></i>
                                User Information
                            </h4>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-user mt-0.5 w-4 text-gray-400"></i>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-gray-400">Full Name</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                            {{ Auth::user()->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-envelope mt-0.5 w-4 text-gray-400"></i>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-gray-400">Email</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                            {{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-building mt-0.5 w-4 text-gray-400"></i>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-gray-400">Company</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                            {{ $talenta->cpny_id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-sitemap mt-0.5 w-4 text-gray-400"></i>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-gray-400">Department</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                            {{ $talenta->department_id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 sm:col-span-2">
                                    <i class="fa-solid fa-layer-group mt-0.5 w-4 text-gray-400"></i>
                                    <div>
                                        <p class="mb-2 text-xs uppercase tracking-wide text-gray-400">Business Unit
                                        </p>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach (explode(',', $talenta->business_unit_id) as $bu)
                                                <span
                                                    class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">{{ trim($bu) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

        $(document).on('click', '.togglePassword', function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            const isPassword = input.attr('type') === 'password';
            input.attr('type', isPassword ? 'text' : 'password');
            icon.toggleClass('fa-eye', !isPassword).toggleClass('fa-eye-slash', isPassword);
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
