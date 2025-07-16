<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Left Column (Profile Sidebar) -->
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg shadow">
                        <div class="text-center">
                            <img class="w-28 h-28 rounded-full mx-auto border border-gray-300 object-cover"
                                src="{{ asset('avatar/' . Auth::user()->npk . '.jpg') }}"
                                onerror="this.onerror=null; this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';"
                                alt="User avatar">

                            <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white">
                                {{ Auth::user()->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ Auth::user()->departmentid }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $talenta->branch }}
                            </p>

                            <div class="mt-4 flex justify-center gap-2">
                                {{-- <button class="px-3 py-1 text-sm bg-blue-500 text-white rounded">Follow</button> --}}
                                <button id="btnChangePassword" class="px-3 py-1 text-sm border text-blue-500 border-blue-500 rounded">
                                    Change Password
                                </button>
                            </div>
                        </div>

                        {{-- <div class="mt-6 border-t pt-4">
                            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li><strong>Websitex:</strong> yourwebsite.com</li>
                                <li><strong>Github:</strong> github.com/username</li>
                                <li><strong>Twitter:</strong> @username</li>
                                <li><strong>Instagram:</strong> @username</li>
                                <li><strong>Facebook:</strong> fb.com/username</li>
                            </ul>
                        </div> --}}
                    </div>

                    <div id="changePasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
                        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl">
                            <h2 class="text-lg font-semibold mb-4">Change Password</h2>
                            <form id="changePasswordForm">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Current Password</label>
                                    <input type="password" name="current_password" class="w-full border rounded px-3 py-2" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium">New Password</label>
                                    <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" id="btnCancel" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    <!-- Right Column (User Info) -->
                    <div class="md:col-span-2">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-6 mb-6">
                            <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">User Information</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div><strong>Full Name:</strong> {{ Auth::user()->name }}</div>
                                <div><strong>Email:</strong> {{ Auth::user()->email }}</div>
                                <div><strong>Position:</strong> {{ $talenta->job_position }}</div>
                                <div><strong>Mobile:</strong> {{ $talenta->mobile_phone }}</div>
                                <div class="sm:col-span-2"><strong>Address:</strong> {{ $talenta->current_address }}</div>
                            </div>
                            {{-- <div class="mt-4">
                                <a href="{{ route('profile.show') }}"
                                    class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Edit</a>
                            </div> --}}
                        </div>

                        <!-- Project Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- @foreach (['Web Design', 'Website Markup', 'One Page', 'Mobile Template'] as $project)
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                                    <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                        <span class="italic text-blue-600">assignment</span> {{ $project }}
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ rand(40, 90) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#btnChangePassword').click(function () {
            $('#changePasswordModal').removeClass('hidden');
        });

        $('#btnCancel').click(function () {
            $('#changePasswordModal').addClass('hidden');
        });

        $('#changePasswordForm').submit(function (e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route('password.update.custom') }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    // alert(response.message);
                    toastr.success(response.message);
                    $('#changePasswordModal').addClass('hidden');
                    $('#changePasswordForm')[0].reset();
                },
                error: function (xhr) {
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
