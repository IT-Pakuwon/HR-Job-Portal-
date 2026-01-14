@if ($errors->any())
    <div {{ $attributes }}>
        <div class="rounded-lg bg-red-500 px-4 py-2 text-xs text-white">
            <div class="font-medium">{{ __('Whoops! Something went wrong.') }}</div>
            <ul class="mt-1 list-inside list-disc text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
