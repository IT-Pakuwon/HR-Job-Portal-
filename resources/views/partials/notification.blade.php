<div x-data="waitingApprovalNotif()" x-init="load()" class="h-[50vh] rounded-xl bg-white p-4 shadow dark:bg-gray-800">

    <!-- HEADER -->
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-700 dark:text-gray-200">
            🔔 Notifications
        </h2>

        <span x-show="actionCount > 0" class="rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white"
            x-text="actionCount">
        </span>
    </div>

    <!-- EMPTY -->
    <div x-show="items.length === 0" class="text-sm text-gray-500">
        No notifications 🎉
    </div>

    <!-- LIST -->
    <div x-show="items.length > 0" class="max-h-80 space-y-3 overflow-y-auto">

        <!-- ACTION REQUIRED -->
        <template x-if="actionItems.length">
            <div>
                <div class="mb-1 text-xs font-semibold text-red-500">
                    Action Required
                </div>

                <template x-for="item in actionItems" :key="item.id">
                    <a :href="item.url + '/' + item.id"
                        class="block rounded-lg border border-red-200 bg-red-50 p-3 transition hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20">

                        <!-- ITEM -->
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-100" x-text="item.docid"></div>

                                <div class="text-xs text-gray-500" x-text="item.cpnyid + ' • ' + item.departementid">
                                </div>
                            </div>

                            <span class="rounded px-2 py-0.5 text-xs font-semibold" :class="statusClass(item.status)"
                                x-text="statusText(item.status)">
                            </span>
                        </div>

                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="item.infohd"></div>

                        <div class="mt-1 text-xs text-gray-400" x-text="item.docdate"></div>
                    </a>
                </template>
            </div>
        </template>

        <!-- INFORMATION -->
        <template x-if="infoItems.length">
            <div>
                <div class="mb-1 text-xs font-semibold text-gray-400">
                    Information
                </div>

                <template x-for="item in infoItems" :key="item.id">
                    <a :href="item.url + '/' + item.id"
                        class="block rounded-lg border border-gray-200 p-3 opacity-80 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">

                        <!-- ITEM -->
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-semibold text-gray-800 dark:text-gray-100" x-text="item.docid"></div>

                                <div class="text-xs text-gray-500" x-text="item.cpnyid + ' • ' + item.departementid">
                                </div>
                            </div>

                            <span class="rounded px-2 py-0.5 text-xs font-semibold" :class="statusClass(item.status)"
                                x-text="statusText(item.status)">
                            </span>
                        </div>

                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="item.infohd"></div>

                        <div class="mt-1 text-xs text-gray-400" x-text="item.docdate"></div>
                    </a>
                </template>
            </div>
        </template>

    </div>
</div>

<script>
    function waitingApprovalNotif() {
        return {
            items: [],
            actionItems: [],
            infoItems: [],
            actionCount: 0,

            load() {
                fetch("{{ route('waitingapproval.json') }}")
                    .then(r => r.json())
                    .then(res => {
                        const data = res.data ?? [];
                        this.items = data;

                        this.actionItems = data.filter(i =>
                            i.status === 'P' || i.status === 'D'
                        );

                        this.infoItems = data.filter(i =>
                            i.status === 'C' || i.status === 'R'
                        );

                        this.actionCount = this.actionItems.length;
                    });
            },

            statusText(code) {
                return {
                    D: 'Need Revision',
                    P: 'Waiting Approval',
                    C: 'Approved',
                    R: 'Rejected',
                    X: 'Canceled',
                } [code] || 'Unknown';
            },

            statusClass(code) {
                return {
                    D: 'bg-orange-200 text-orange-700',
                    P: 'bg-blue-200 text-blue-700',
                    C: 'bg-green-200 text-green-700',
                    R: 'bg-red-200 text-red-700',
                    X: 'bg-gray-200 text-gray-700',
                } [code] || 'bg-gray-200 text-gray-700';
            }
        }
    }
</script>
