<x-app-layout>
    <div id="sendApprovalEmail" class="mx-auto w-full space-y-4 p-3"
        data-users="{{ route('send-email.users') }}" data-pending="{{ route('send-email.pending') }}" data-send="{{ route('send-email.send') }}">
        <h1 class="text-base font-semibold text-gray-800 dark:text-gray-100">Send Email</h1>
        <form id="approvalEmailSearch" class="flex flex-wrap items-end gap-3 border-b border-gray-200 pb-4 dark:border-gray-700">
            <label class="min-w-0 flex-1 text-sm text-gray-700 dark:text-gray-200" style="min-width:min(100%,16rem);">Nama
                <select id="approvalEmailUser" class="mt-1 w-full"><option value=""></option></select>
            </label>
            <button id="approvalEmailFind" type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700"><i class="fas fa-search mr-1" aria-hidden="true"></i>Cari</button>
            <button id="approvalEmailSend" type="button" disabled class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"><i class="fas fa-paper-plane mr-1" aria-hidden="true"></i><span>Send Email</span></button>
        </form>
        <div id="approvalEmailNotice" role="status" class="hidden border-l-4 border-indigo-400 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200"></div>
        <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-300">
            <span>Notification Email: <strong id="approvalEmailRecipient">-</strong></span>
            <span>Pending Approval: <strong id="approvalEmailTotal">0</strong></span>
        </div>
        <div class="overflow-x-auto bg-white p-3 dark:bg-gray-800">
            <table id="approvalEmailTable" class="w-full text-sm text-gray-700 dark:text-gray-200">
                <thead><tr>
                    @foreach (['Document', 'Document ID', 'Company', 'Department', 'Previous Approval Date', 'Created By', 'Description'] as $heading)
                        <th class="px-3 py-3 text-left">{{ $heading }}</th>
                    @endforeach
                </tr></thead>
            </table>
        </div>
    </div>
    <script src="{{ asset('assets/js/workflow/send-email.js') }}?v={{ filemtime(public_path('assets/js/workflow/send-email.js')) }}"></script>
</x-app-layout>
