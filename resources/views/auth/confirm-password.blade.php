<x-authentication-layout>
    <h1 class="mb-6 text-lg font-bold text-gray-800 dark:text-gray-100">{{ __('Confirm your Password') }}</h1>
    <!-- Form -->
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div>
            <x-label for="password" value="{{ __('Password') }}" />
            <x-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
        </div>
        <div class="mt-6 flex justify-end">
            <x-button>
                {{ __('Confirm') }}
            </x-button>
        </div>
    </form>
    <x-validation-errors class="mt-4" />
</x-authentication-layout>
