<x-authentication-layout>
    <h1 class="mb-6 text-lg font-bold text-gray-800 dark:text-gray-100">{{ __('Confirm access') }}</h1>
    <div x-data="{ recovery: false }">
        <div class="mb-4" x-show="! recovery">
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        </div>

        <div class="mb-4" x-show="recovery">
            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('two-factor.login') }}">
            @csrf
            <div class="space-y-4">
                <div x-show="! recovery">
                    <x-label for="code" value="{{ __('Code') }}" />
                    <x-input id="code" type="text" inputmode="numeric" name="code" autofocus x-ref="code"
                        autocomplete="one-time-code" />
                </div>
                <div x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Recovery Code') }}" />
                    <x-input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code"
                        autocomplete="one-time-code" />
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end">
                <button type="button" class="text-xs underline hover:no-underline" x-show="! recovery"
                    x-on:click="
                        recovery = true;
                        $nextTick(() => { $refs.recovery_code.focus() })
                    ">
                    {{ __('Use a recovery code') }}
                </button>

                <button type="button" class="cursor-pointer text-xs text-gray-600 underline hover:text-gray-900"
                    x-show="recovery"
                    x-on:click="
                        recovery = false;
                        $nextTick(() => { $refs.code.focus() })
                    ">
                    {{ __('Use an authentication code') }}
                </button>

                <x-button class="ml-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
    </div>
</x-authentication-layout>
