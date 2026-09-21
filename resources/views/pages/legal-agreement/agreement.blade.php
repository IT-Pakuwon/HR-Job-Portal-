<x-app-layout>
    @include('pages.legal-agreement.partial.style')

    <div class="max-w-9xl mx-auto w-full overflow-x-hidden p-2">

        {{-- Status Filter --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-7">

            {{-- Jobs — contracts from IFCA with no Agreement FU created yet. Default view. --}}
            <button type="button" class="text-left">
                <a href="#" class="legal-nav-card group block h-full" data-view="jobs">
                    <div class="agreement-status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            🗂️
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="whitespace-normal break-words text-sm font-medium">Job</p>
                        </div>
                        <p class="shrink-0 text-base font-bold" data-count="jobs_pending">
                            {{ $counts['jobs_pending'] ?? 0 }}
                        </p>
                    </div>
                </a>
            </button>

            @php
                $statusCards = [
                    ['key' => '', 'label' => 'All', 'icon' => '📄', 'count' => 'all', 'color' => 'slate'],
                    ['key' => 'MY_AGREEMENT', 'label' => 'My Agreement', 'icon' => '👤', 'count' => 'my_agreement', 'color' => 'indigo'],
                    ['key' => 'ACTIVE', 'label' => 'Active', 'icon' => '✅', 'count' => 'active', 'color' => 'green'],
                    ['key' => 'HOLD', 'label' => 'Hold', 'icon' => '⏸️', 'count' => 'hold', 'color' => 'yellow'],
                    ['key' => 'ESCALATED', 'label' => 'Escalated', 'icon' => '🚨', 'count' => 'escalated', 'color' => 'red'],
                    ['key' => 'COMPLETED', 'label' => 'Completed', 'icon' => '🏁', 'count' => 'completed', 'color' => 'slate'],
                ];
            @endphp

            @foreach ($statusCards as $card)
                <button type="button" class="text-left">
                    <a href="#" class="legal-nav-card group block h-full" data-view="agreements" data-status="{{ $card['key'] }}">
                        <div class="agreement-status-card flex h-full items-center gap-3 rounded-lg border border-{{ $card['color'] }}-700 bg-{{ $card['color'] }}-200/20 p-3 text-{{ $card['color'] }}-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-{{ $card['color'] }}-100 hover:shadow-md active:scale-95">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                                {{ $card['icon'] }}
                            </div>
                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="whitespace-normal break-words text-sm font-medium">{{ $card['label'] }}</p>
                            </div>
                            <p class="shrink-0 text-base font-bold" data-count="{{ $card['count'] }}">
                                {{ $counts[$card['count']] ?? 0 }}
                            </p>
                        </div>
                    </a>
                </button>
            @endforeach

        </div>

        @include('pages.legal-agreement.partial.section-jobs')

        {{-- Agreement list section — hidden by default; Job is the landing view. --}}
        <div id="agreementSection" class="mt-4 hidden">

            {{-- Toolbar --}}
            <div class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 dark:border-white/[0.06] dark:bg-white/[0.02] sm:flex-row sm:items-center sm:justify-between">

                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <input type="text" id="agr_search" placeholder="Search agreement, business, tenant..."
                        class="agr-input sm:max-w-xs" />

                    <select id="agr_cpny_filter" class="agr-input agr-select2 sm:max-w-[200px]">
                        <option value="">All Companies</option>
                        @foreach ($allCompanies as $c)
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('legal-agreement.export') }}" class="inline-flex h-11 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                        <i class="fa-solid fa-file-excel"></i> Export
                    </a>
                    <button type="button" id="btnOpenCreateAgreement" class="inline-flex h-11 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i> New Agreement
                    </button>
                </div>

            </div>

            {{-- Table --}}
            <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-white/[0.06] dark:bg-white/[0.02]">
                <table id="agreementTable" class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-white/[0.03]">
                        <tr>
                            <th class="px-4 py-3 text-left">Agreement No</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Business / Tenant</th>
                            <th class="px-4 py-3 text-left">PIC Legal</th>
                            <th class="px-4 py-3 text-left">PIC Leasing</th>
                            <th class="px-4 py-3 text-left">Step</th>
                            <th class="px-4 py-3 text-left">Days</th>
                            <th class="px-4 py-3 text-left">Cycle</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

    </div>

    @include('pages.legal-agreement.partial.modal-create')
    @include('pages.legal-agreement.partial.modal-detail')
    @include('pages.legal-agreement.partial.modal-action')

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            window.currentUser = @json(auth()->user()->username);

            window.agrRoutes = {
                index: "{{ route('legal-agreement') }}",
                show: "{{ url('/show-legal-agreement') }}/:eid",
                counts: "{{ route('legal-agreement.counts') }}",
                json: "{{ route('legal-agreement.json') }}",
                jobsJson: "{{ route('legal-agreement.jobs.json') }}",
                jobsExport: "{{ route('legal-agreement.jobs.export') }}",
                createDropdown: "{{ route('legal-agreement.create-dropdown') }}",
                picSearch: "{{ route('legal-agreement.picSearch') }}",
                companiesSearch: "{{ route('legal-agreement.companiesSearch') }}",
                store: "{{ route('legal-agreement.store') }}",
                update: "{{ url('/legal-agreement/update') }}/:eid",
                hold: "{{ url('/legal-agreement/hold') }}/:eid",
                activate: "{{ url('/legal-agreement/activate') }}/:eid",
                complete: "{{ url('/legal-agreement/complete') }}/:eid",
                detail: "{{ url('/legal-agreement/detail') }}/:eid",
                tracking: "{{ url('/legal-agreement/tracking') }}/:eid",
                comments: "{{ url('/legal-agreement/comments') }}/:eid",
                comment: "{{ url('/legal-agreement/comment') }}/:eid",
                mentionableUsers: "{{ url('/legal-agreement/mentionable-users') }}/:eid",
                print: "{{ url('/legal-agreement/print') }}/:eid",
            };
        </script>

        <script src="{{ asset('assets/js/legal-agreement/agreement.js') }}?v={{ filemtime(public_path('assets/js/legal-agreement/agreement.js')) }}"></script>
        <script src="{{ asset('assets/js/legal-agreement/jobs.js') }}?v={{ filemtime(public_path('assets/js/legal-agreement/jobs.js')) }}"></script>

        @if ($eid)
            <script>
                window.addEventListener('DOMContentLoaded', function () {
                    openAgreementDetailModal(@json($eid));
                });
            </script>
        @endif
    @endpush

</x-app-layout>
