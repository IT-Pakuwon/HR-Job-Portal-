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

        <button class="group inline-flex items-center justify-center" aria-haspopup="true"
                @click.prevent="open = !open" :aria-expanded="open">
            <img class="h-8 w-8 rounded-full"
                 src="{{ $user->profile_photo_url ?? asset('images/avatar-default.png') }}"
                 width="32" height="32"
                 alt="{{ $user->name ?? 'User' }}" />
            <div class="flex items-center truncate">
                <span
                    class="ml-2 truncate text-xs font-medium text-gray-600 group-hover:text-gray-800 dark:text-gray-100 dark:group-hover:text-white">
                    {{ $user->name }}
                </span>
                <svg class="ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" viewBox="0 0 12 12">
                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                </svg>
            </div>
        </button>

        <div class="{{ $align === 'right' ? 'right-0' : 'left-0' }} absolute top-full z-10 mt-1 min-w-44 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1.5 dark:border-gray-700/60 dark:bg-gray-800"
             @click.outside="open = false" @keydown.escape.window="open = false" x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0" x-cloak>

            <div class="mb-1 border-b border-gray-200 px-3 pb-2 pt-0.5 dark:border-gray-700/60">
                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $user->name }}</div>
                <div class="text-xs italic text-gray-500 dark:text-gray-400">Administrator</div>
            </div>

            <ul>
                <li>
                    <a class="flex items-center px-3 py-1 text-xs font-medium text-violet-500 hover:text-violet-600 dark:hover:text-violet-400"
                       href="{{ route('profile.showx') }}" @click="open = false" @focus="open = true"
                       @focusout="open = false">
                        Settings
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <a class="flex items-center px-3 py-1 text-xs font-medium text-violet-500 hover:text-violet-600 dark:hover:text-violet-400"
                           href="{{ route('logout') }}" @click.prevent="$root.submit();" @focus="open = true"
                           @focusout="open = false">
                            {{ __('Sign Out') }}
                        </a>
                    </form>
                </li>
            </ul>
        </div>

    @endif
</div>