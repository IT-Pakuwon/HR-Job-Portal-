<x-authentication-layout>
    <div class="mx-auto w-full max-w-md rounded-2xl bg-white p-8 shadow-xl dark:bg-gray-800">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">
                {{ __('Reset Password') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Create a new password for your account') }}
            </p>
        </div>

        <x-validation-errors class="mb-6" />

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email -->
            <div>
                <x-label for="email" class="text-xs font-medium text-gray-700 dark:text-gray-200"
                    value="{{ __('Email') }}" />
                <x-input id="email" name="email" type="email" required autofocus :value="old('email', $request->email)"
                    class="mt-2 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>

            <!-- New Password -->
            <div>
                <x-label for="password" class="text-xs font-medium text-gray-700 dark:text-gray-200"
                    value="{{ __('New Password') }}" />
                <x-input id="password" name="password" type="password" required autocomplete="new-password"
                    class="mt-2 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-label for="password_confirmation" class="text-xs font-medium text-gray-700 dark:text-gray-200"
                    value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" name="password_confirmation" type="password" required
                    autocomplete="new-password"
                    class="mt-2 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>

            <!-- Submit -->
            <button type="submit"
                class="mt-2 w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white shadow-md transition-all hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('Reset Password') }}
            </button>
        </form>
    </div>
</x-authentication-layout>
