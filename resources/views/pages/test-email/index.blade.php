<x-app-layout>
    <style>
.input {
    @apply w-full rounded-md border px-3 py-2 text-sm;
}
</style>
    <div class="max-w-4xl mx-auto p-4">
        <div class="rounded-xl bg-white shadow-md dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h1 class="text-lg font-bold text-gray-800 dark:text-white">
                    Test Email SMTP
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Form testing kirim email SMTP.
                </p>
            </div>

            <div class="mb-6 rounded-lg border p-4 bg-gray-50 dark:bg-gray-700">
    <h2 class="mb-3 text-sm font-bold text-gray-700 dark:text-gray-200">
        🔧 SMTP Configuration (Testing Only)
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

    <input name="mail_mailer"
        value="{{ old('mail_mailer', $mailConfig['mailer']) }}"
        placeholder="Mailer" class="input">

    <input name="mail_host"
        value="{{ old('mail_host', $mailConfig['host']) }}"
        placeholder="Host" class="input">

    <input name="mail_port"
        value="{{ old('mail_port', $mailConfig['port']) }}"
        placeholder="Port" class="input">

    <input name="mail_username"
        value="{{ old('mail_username', $mailConfig['username']) }}"
        placeholder="Username" class="input">

    <input name="mail_password"
        value="{{ old('mail_password', $mailConfig['password']) }}"
        placeholder="Password" type="password" class="input">

    <input name="mail_encryption"
        value="{{ old('mail_encryption', $mailConfig['encryption']) }}"
        placeholder="Encryption" class="input">

    <input name="mail_from_address"
        value="{{ old('mail_from_address', $mailConfig['from_address']) }}"
        placeholder="From Address" class="input">

    <input name="mail_from_name"
        value="{{ old('mail_from_name', $mailConfig['from_name']) }}"
        placeholder="From Name" class="input">

</div>

            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-100 px-4 py-3 text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-100 px-4 py-3 text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('test-email.send') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="from" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            From
                        </label>
                        <input
                            type="email"
                            id="from"
                            name="from"
                            value="{{ old('from', $mailConfig['from_address'] ?? '') }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="from@example.com">
                    </div>

                    <div>
                        <label for="to" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            To
                        </label>
                        <input
                            type="email"
                            id="to"
                            name="to"
                            value="{{ old('to') }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="to@example.com">
                    </div>

                    <div>
                        <label for="subject" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Subject
                        </label>
                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Input subject">
                    </div>

                    <div>
                        <label for="body" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Body
                        </label>
                        <textarea
                            id="body"
                            name="body"
                            rows="10"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            placeholder="Isi email...">{{ old('body') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Send Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- <script>
function fillSes() {
    document.querySelector('[name=mail_host]').value = 'mx5.pakuwon.com';
    document.querySelector('[name=mail_port]').value = '587';
    document.querySelector('[name=mail_encryption]').value = '';
}
</script> --}}
</x-app-layout>